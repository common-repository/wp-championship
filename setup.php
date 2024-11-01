<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2007-2023  Hans Matzen  (email : webmaster at tuxlog.de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package wp-championship
 */

/**
 * This function installs the wp-championship database tables and
 * sets up default values and options
 */
function wp_championship_install() {
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	include 'globals.php';

	global $wpdb;
	$wpdb->show_errors( false );

	// add charset & collate like wp db class.
	$charset_collate = '';

	if ( version_compare( $wpdb->db_server_info(), '4.1.0', '>=' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}

	// CREATE TABLEs.
	// team table.
	$sql = 'CREATE TABLE ' . $cs_team . " (
        tid INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        shortname VARCHAR(5) NOT NULL,
        icon VARCHAR(255) NOT NULL,
        groupid VARCHAR(2) NOT NULL,
        qualified BOOL NOT NULL,
        penalty INT NOT NULL,
        PRIMARY KEY  (tid)
      ) $charset_collate;";

	dbDelta( $sql );

	// match table.
	$sql = 'CREATE TABLE ' . $cs_match . " (
        mid INT NOT NULL AUTO_INCREMENT,
        round CHAR(1),
        spieltag INT NOT NULL,
        tid1 VARCHAR(8) NOT NULL,
        tid2 VARCHAR(8) NOT NULL,
        location VARCHAR(80) NOT NULL,
        matchtime DATETIME NOT NULL,
        result1 INT NOT NULL, 
        result2 INT NOT NULL,
        winner BOOL NOT NULL,
        ptid1 INT NOT NULL,
        ptid2 INT NOT NULL,
        PRIMARY KEY  (mid)
      ) $charset_collate;";

	dbDelta( $sql );

	// tipp table.
	$sql = 'CREATE TABLE ' . $cs_tipp . " (
        userid INT NOT NULL,
        mid INT NOT NULL,
        result1 INT NOT NULL, 
        result2 INT NOT NULL,
        result3 INT NOT NULL,
        tipptime DATETIMe NOT NULL,
        points INT NOT NULL,
        PRIMARY KEY  (userid,mid)
      ) $charset_collate;";

	dbDelta( $sql );

	// users table.
	$sql = 'CREATE TABLE ' . $cs_users . " (
	userid INT NOT NULL,
    admin BOOL NOT NULL,
    mailservice BOOL NOT NULL,
    mailreceipt BOOL NOT NULL,
    stellvertreter INT NOT NULL,
    champion INT NOT NULL,
    championtime DATETIME NOT NULL,
    rang INT NOT NULL,
    tippgroup VARCHAR(20) NOT NULL,
    penalty INT NOT NULL,
	mailformat INT NOT NULL,
	hidefinmatch BOOL NOT NULL,
	jokerlist VARCHAR(255) NOT NULL,
    PRIMARY KEY  (userid)
    ) $charset_collate;";

	dbDelta( $sql );

	// tippgroup table.
	$sql = 'CREATE TABLE ' . $cs_tippgroup . " (
        tgid INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(40) NOT NULL,
        shortname VARCHAR(5) NOT NULL,
        icon VARCHAR(40) NOT NULL,
        penalty INT NOT NULL,
        PRIMARY KEY  (tgid)
      ) $charset_collate;";

	dbDelta( $sql );

	// add admin as tippspiel admin if necessary.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$resadmin = $wpdb->get_row( $wpdb->prepare( 'select count(*) as c from %i where userid=1;', $cs_users ) );
	if ( 0 == $resadmin->c ) {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->query( $wpdb->prepare( "insert into %i values ( 1, 1, 0, 0, 0, 0, '0000-00-00 00:00:00',0, '',0 ,0,0,'');", $cs_users ) );
	}

	//
	// Optionen / Parameter.
	//

	// Option: Anzahl der Gruppen in der Vorrunde; Werte: 1-12;.
	// Gibt die Anzahl der Gruppen in der Vorrunde an. Default: 8.
	$cs_groups = get_option( 'cs_groups' );
	if ( '' == $cs_groups ) {
		$cs_groups = '8';
		add_option( 'cs_groups', $cs_groups, '', 'yes' );
	};

	// Option: Punkte für Sieger, Wert: ganzzahlig numerisch, Default: 3.
	$cs_pts_winner = get_option( 'cs_pts_winner' );
	if ( '' == $cs_pts_winner ) {
		$cs_pts_winner = '3';
		add_option( 'cs_pts_winner', $cs_pts_winner, '', 'yes' );
	};

	// Option: Punkte für Verlierer, Wert: ganzzahlig numerisch, Default: 0.
	$cs_pts_looser = get_option( 'cs_pts_looser' );
	if ( '' == $cs_pts_looser ) {
		$cs_pts_looser = '0';
		add_option( 'cs_pts_looser', $cs_pts_looser, '', 'yes' );
	};

	// Option: Punkte für Unentschieden , Wert: ganzzahlig numerisch, Default: 1.
	$cs_pts_deuce = get_option( 'cs_pts_deuce' );
	if ( '' == $cs_pts_deuce ) {
		$cs_pts_deuce = '1';
		add_option( 'cs_pts_deuce', $cs_pts_deuce, '', 'yes' );
	};

	// Option: Anzahl der Teams in der Finalrunde; Werte: 16, 8, 4; Default: 16;.
	// Wenn die Option auf den Wert 16 eingestellt wird, startet die Finalrunde.
	// im Achtelfinale, bei 8 im Viertelfinale und bei 4 im Halbfinale.
	$cs_final_teams = get_option( 'cs_final_teams' );
	if ( '' == $cs_final_teams ) {
		$cs_final_teams = '16';
		add_option( 'cs_final_teams', $cs_final_teams, '', 'yes' );
	};

	// Option: Anzahl der Teams pro Gruppe, die maximal in die
	// Finalrunde kommen, Default:2.
	$cs_group_teams = get_option( 'cs_group_teams' );
	if ( '' == $cs_group_teams ) {
		$cs_group_teams = '2';
		add_option( 'cs_group_teams', $cs_group_teams, '', 'yes' );
	};

	// Option: Punkte für richtigen Tipp, Wert: ganzzahlig numerisch, Default:5.
	$cs_pts_tipp = get_option( 'cs_pts_tipp' );
	if ( '' == $cs_pts_tipp ) {
		$cs_pts_tipp = '5';
		add_option( 'cs_pts_tipp', $cs_pts_tipp, '', 'yes' );
	};

	// Option: Punkte für richtige Tendenz, Wert: ganzzahlig numerisch, Default:1.
	$cs_pts_tendency = get_option( 'cs_pts_tendency' );
	if ( '' == $cs_pts_tendency ) {
		$cs_pts_tendency = '1';
		add_option( 'cs_pts_tendency', $cs_pts_tendency, '', 'yes' );
	};

	// Option: Punkte für richtige Tendenz und Tordifferenz, Wert: ganzzahlig
	// numerisch, Default:3.
	$cs_pts_supertipp = get_option( 'cs_pts_supertipp' );
	if ( '' == $cs_pts_supertipp ) {
		$cs_pts_supertipp = '3';
		add_option( 'cs_pts_supertipp', $cs_pts_supertipp, '', 'yes' );
	};

	// Option: Punkte für richtigen Champion, Wert: ganzzahlig numerisch,
	// Default: 20.
	$cs_pts_champ = get_option( 'cs_pts_champ' );
	if ( '' == $cs_pts_champ ) {
		$cs_pts_champ = '1';
		add_option( 'cs_pts_champ', $cs_pts_champ, '', 'yes' );
	};

	// Option: Punkte für einseitig richtigen Tipp, Wert: ganzzahlig numerisch,
	// Default: 0.
	$cs_pts_oneside = get_option( 'cs_pts_oneside' );
	if ( '' == $cs_pts_oneside ) {
		$cs_pts_oneside = '0';
		add_option( 'cs_pts_oneside', $cs_pts_oneside, '', 'yes' );
	};

	// Option: Schwellwert für Summer der Tore Tipp, Wert: ganzzahlig numerisch,
	// Default: 0.
	$cs_goalsum = get_option( 'cs_goalsum' );
	if ( '' == $cs_goalsum ) {
		$cs_goalsum = '-1';
		add_option( 'cs_goalsum', $cs_goalsum, '', 'yes' );
	};

	// Option: Punkte für Summe der Tore, Wert: ganzzahlig numerisch,
	// Default: 0.
	$cs_pts_goalsum = get_option( 'cs_pts_goalsum' );
	if ( '' == $cs_pts_goalsum ) {
		$cs_pts_goalsum = '0';
		add_option( 'cs_pts_goalsum', $cs_pts_goalsum, '', 'yes' );
	};

	// Option: Stellvertreterfunktion abstellen, Wert: bool, Default: 0.
	$cs_stellv_schalter = get_option( 'cs_stellv_schalter' );
	if ( '' == $cs_stellv_schalter ) {
		$cs_stellv_schalter = '1';
		add_option( 'cs_stellv_schalter', $cs_stellv_schalter, '', 'yes' );
	};

	// Option: Turniermodus, Wert: int, Default: 1.
	$cs_modus = get_option( 'cs_modus' );
	if ( '' == $cs_modus ) {
		$cs_modus = '1';
		add_option( 'cs_modus', $cs_modus, '', 'yes' );
	};

	// Option: Floating Link einschalten, Wert: bool, Default: 1.
	$cs_floating_link = get_option( 'cs_floating_link' );
	if ( '' == $cs_floating_link ) {
		$cs_floating_link = '1';
		add_option( 'cs_floating_link', $cs_floating_link, '', 'yes' );
	};

	// Option: Vorrunden-Tipps sperren, Wert: bool, Default: 0.
	$cs_lock_round1 = get_option( 'cs_lock_round1' );
	if ( '' == $cs_lock_round1 ) {
		$cs_lock_round1 = '0';
		add_option( 'cs_lock_round1', $cs_lock_round1, '', 'yes' );
	};

	// Option: Platzierungstrend berechnen, Wert: bool, Default: 1.
	$cs_rank_trend = get_option( 'cs_rank_trend' );
	if ( '' == $cs_rank_trend ) {
		$cs_rank_trend = '1';
		add_option( 'cs_rank_trend', $cs_rank_trend, '', 'yes' );
	};

	wp_schedule_event( time(), 'hourly', 'cs_mailreminder' );

}

/**
 * Function to remove tables and options during deactivation
 */
function wp_championship_deinstall() {
	include 'globals.php';
	$wpdb =& $GLOBALS['wpdb'];

	// entferne reminder hook.
	wp_clear_scheduled_hook( 'cs_mailreminder' );

	// to prevent misuse :-).
	return;

	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$results = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s\%;', $cs_table_prefix ) );

	if ( 0 != $results ) {
		// drop tables.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->query( $wpdb->prepare( 'drop table %i;', $cs_team ) );  // team table.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->query( $wpdb->prepare( 'drop table %i;', $cs_match ) ); // match table.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->query( $wpdb->prepare( 'drop table %i;', $cs_tipp ) );  // tipp table.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->query( $wpdb->prepare( 'drop table %i;', $cs_users ) ); // users table.
	}

	// remove options from wp_options.
	delete_option( 'cs_final_teams' );
	delete_option( 'cs_groups' );
	delete_option( 'cs_pts_champ' );
	delete_option( 'cs_pts_deuce' );
	delete_option( 'cs_pts_looser' );
	delete_option( 'cs_pts_winner' );
	delete_option( 'cs_pts_supertipp' );
	delete_option( 'cs_pts_tipp' );
	delete_option( 'cs_pts_tendency' );
	delete_option( 'cs_stellv_schalter' );
	delete_option( 'cs_modus' );
	delete_option( 'cs_goalsum' );
	delete_option( 'cs_pts_goalsum' );
	delete_option( 'cs_pts_oneside' );

	// options.
	$fieldnames = array(
		'cs_label_group',
		'cs_col_group',
		'cs_label_icon1',
		'cs_col_icon1',
		'cs_label_match',
		'cs_col_match',
		'cs_label_icon2',
		'cs_col_icon2',
		'cs_label_location',
		'cs_col_location',
		'cs_label_time',
		'cs_col_time',
		'cs_label_tip',
		'cs_col_tip',
		'cs_label_points',
		'cs_col_points',
		'cs_label_place',
		'cs_col_place',
		'cs_label_player',
		'cs_col_player',
		'cs_label_upoints',
		'cs_col_upoints',
		'cs_label_trend',
		'cs_label_steam',
		'cs_col_steam',
		'cs_label_smatch',
		'cs_col_smatch',
		'cs_label_swin',
		'cs_col_swin',
		'cs_label_stie',
		'cs_col_stie',
		'cs_label_sloose',
		'cs_col_sloose',
		'cs_label_sgoal',
		'cs_col_sgoal',
		'cs_label_spoint',
		'cs_col_spoint',
		'cs_tipp_sort',
	);
	foreach ( $fieldnames as $fn ) {
		delete_option( $fn );
	}
}

