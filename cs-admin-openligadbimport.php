<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2014-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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
 * JSON API interface for use with OpenLigaDB
 *
 * @param string $endpoint endpoint to fetch.
 */
function do_apicall( $endpoint ) {

	$url = sprintf( 'https://api.openligadb.de/%s', $endpoint );

	$ret = wp_remote_get( $url );

	if ( is_array( $ret ) && ! is_wp_error( $ret ) ) {
		$output = $ret['body'];
	}

	// Decoding the API response.
	$response = json_decode( $output, true );

	if ( null === $response ) {
		die( esc_attr( 'Error processing API response: ' . json_last_error_msg() ) );
	}

	return $response;
}

/**
 * Import dialog for getting data from OpenLigaDB.
 */
function wpc_openligadbimport_cb() {
	// get sql object.
	global $wpdb;
	require dirname( __FILE__ ) . '/globals.php';

	if ( ! empty( $_POST ) ) {
		if ( ! isset( $_POST['wpc_nonce_oldbimport'] ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_oldbimport'] ) ), 'wpc_nonce_oldbimport' ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}

		$league = '';

		if ( array_key_exists( 'league', $_POST ) ) {
			$league          = sanitize_text_field( wp_unslash( $_POST['league'] ) );
			$league_shortcut = substr( $league, 0, strpos( $league, ';' ) );
			$league_saison   = substr( $league, strpos( $league, ';' ) + 1 );
		}

		$csv_delall = false;
		if ( array_key_exists( 'csv_delall', $_POST ) ) {
			$csv_delall = ( sanitize_text_field( wp_unslash( $_POST['csv_delall'] ) == 'true' ? true : false ) );
		}

		// check if current data should be deleted.
		if ( $csv_delall ) {
			$wpdb->query( 'truncate table cs_team;' ); // this also resets the auto increment counter.
			$wpdb->query( 'truncate table cs_match;' );
			echo esc_attr__( 'Data deleted.', 'wp-championship' ) . '<br />';
		}

		// get OpenLigaDB Teams.
		$resteam = do_apicall( "getavailableteams/$league_shortcut/$league_saison" );
		// get OpenLigaDB Matches.
		$resmatch = do_apicall( "getmatchdata/$league_shortcut/$league_saison" );

		// insert new data.
		foreach ( $resteam as $t ) {
			$result = $wpdb->query(
				$wpdb->prepare(
					"insert into cs_team (tid,name,shortname,icon,groupid,qualified,penalty) values (%d, %s, '', %s, 'A', 0, 0);",
					intval( $t['teamId'] ),
					esc_attr( $t['teamName'] ),
					array_key_exists( 'teamIconUrl', $t ) ? esc_url_raw( $t['teamIconUrl'] ) : ''
				)
			);

			if ( $result ) {
				echo esc_attr__( 'Teamrecord inserted successfully', 'wp-championship' ) . '.<br />';
			} else {
				echo esc_attr__( 'Database error, record not inserted.', 'wp-championship' ) . '<br />';
			}
		}

		foreach ( $resmatch as $m ) {
			$result = $wpdb->query(
				$wpdb->prepare(
					"insert into cs_match (mid,round,spieltag,tid1,tid2,location,matchtime,result1,result2,winner,ptid1,ptid2) 
					values (%d,'V', %d, %d, %d, '',%s,-1,-1,-1,-1,-1);",
					intval( $m['matchID'] ),
					intval( $m['group']['groupOrderID'] ),
					intval( $m['team1']['teamId'] ),
					intval( $m['team2']['teamId'] ),
					esc_attr( $m['matchDateTime'] )
				)
			);

			if ( $result ) {
				echo esc_attr__( 'Matchrecord inserted successfully', 'wp-championship' ) . '.<br />';
			} else {
				echo esc_attr__( 'Database error, record not inserted.', 'wp-championship' ) . '<br />';
			}
		}

		// you must end here to stop the displaying of the html below.
		exit( 0 );
	}

	// import formular aufbauen ===================================================.

	// get all leagues (since json api does not allow to filter by sport, we fetch all leagues and extract the seasons for use as a filter.
	$leagues = do_apicall( 'getavailableleagues' );
	$seasons = array();
	foreach ( $leagues as $l ) {
		$seasons[] = $l['leagueSeason'];
	}
	$seasons = array_unique( $seasons );
	rsort( $seasons );

	$out = '';
	// add log area style.
	$out .= '<style>#message {margin:20px; padding:20px; background:#cccccc; color:#cc0000;}</style>';
	$out .= '<div id="importform" class="wrap" >';
	$out .= '<h2>wp-championship ' . __( 'OpenLigaDB-Import', 'wp-championship' ) . '</h2>';
	// add nonce.
	$out .= '<input name="wpc_nonce_oldbimport" id="wpc_nonce_oldbimport" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_oldbimport' ) . '" />';

	$out .= '<p><strong>' . __( 'This feature is experimental. Please backup your match and team data before trying OpenLigaImport.', 'wp-championship' ) . '</strong></p>';

	$out .= '<label for="ol_season">' . __( 'Select season', 'wp-championship' ) . '&nbsp;&nbsp;&nbsp;:</label>' . "\n";
	$out .= '<select name="ol_season" id="ol_season" onclick="ol_update_league()">' . "\n";
	$out .= '<option value="-1">' . __( 'Select season', 'wp-championship' ) . '...</option>' . "\n";
	foreach ( $seasons as $spa ) {
		$out .= '<option value="' . $spa . '">' . $spa . '</option>' . "\n";
	}
	$out .= "</select><br/><br/>\n";

	$out .= '<label for="ol_league">' . __( 'Select league', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<select name="ol_league" id="ol_league" >' . "\n";
	$out .= '<option value="-1">' . __( 'Select league', 'wp-championship' ) . '...</option>' . "\n";
	$out .= "</select><br/><br/>\n";

	// import mit oder ohne überschreiben.
	$out .= '<label for="csvdelall">' . __( 'Delete data before import', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<input name="csvdelall" id="csvdelall" type="checkbox" value="1" />' . "\n";
	// add submit button to form.
	$href = site_url( 'wp-admin' ) . '/admin.php?page=cs_admin.php';
	$out .= '<p class="submit">';
	$out .= '<input type="submit" name="startimport" id="startimport" value="' . __( 'Start import', 'wp-championship' ) . ' &raquo;" onclick="submit_this(\'openligadbimport\')" />';
	$out .= '&nbsp;&nbsp;&nbsp;';
	$out .= '<input type="submit" name="cancelimport" id="cancelimport" value="' . __( 'Close', 'wp-championship' ) . '" onclick="tb_remove();" /></p>';
	$out .= '<hr />' . "\n";
	// div container fuer das verarbeitungs log.
	$out .= '<div id="message">' . __( 'Import log', 'wp-championship' ) . '</div>';
	$out .= "</div>\n";
	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}

/**
 * Fetches leagues for a special season and returns them as json (called via Ajax).
 */
function wpc_openligadb_getleagues() {
	$out = array();

	if ( ! empty( $_POST ) ) {

		if ( ! isset( $_POST['nonce'] ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_oldbimport' ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}

		$season = '';
		if ( array_key_exists( 'season', $_POST ) ) {
			$season = sanitize_text_field( wp_unslash( $_POST['season'] ) );

			// get leagues.
			$res = do_apicall( 'getavailableleagues' );
			$out = array();
			foreach ( $res as $r ) {
				if ( $r['leagueSeason'] == $season ) {
					$out[] = array(
						'leagueId'       => $r['leagueId'],
						'leagueName'     => $r['leagueName'],
						'leagueShortcut' => $r['leagueShortcut'],
						'leagueSaison'   => $r['leagueSeason'],
					);
				}
			}
		}
	}

	echo json_encode( $out );
	wp_die();
}
