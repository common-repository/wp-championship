<?php
/** This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2010-2021  Hans Matzen  (email : webmaster at tuxlog.de)
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

/* verwendet cs-stats.js für die ajaxeffekte  */

// prüfen, ob wir direkt aufgerufen werden.
if ( ! defined( 'WPINC' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// set character set in case of wrong collation in cs tables.
global $wpdb;
$r0 = $wpdb->query( $wpdb->prepare( 'SET CHARACTER SET %s;', $wpdb->charset ) );


/**
 * Funktion zur Ausgabe der Statistik 1 Punkte jedes Spielers pro Spieltag
 *
 * @param array $atts Parameter für die Statistik.
 */
function show_stats1( $atts ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	$out .= '<h2>' . __( 'Day of event statistic', 'wp-championship' ) . '</h2>';

	$out .= "<div class='wpc-stats1-sel'><form action='#'>" . __( 'Match-Day', 'wp-championship' ) . ':';
	$out .= '<input name="wpc_nonce_stats" id="wpc_nonce_stats" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_stats' ) . '" />';
	$out .= "<input id='wpc_stats1_tippgroup' type='hidden' value='$tippgroup' />";
	$out .= "<select id='wpc_stats1_selector' size='1' onchange='wpc_stats1_update();' >";
	if ( get_option( 'cs_modus' ) == 1 ) {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r1 = $wpdb->get_results( $wpdb->prepare( 'SELECT date( matchtime ) as sday FROM %i GROUP BY date( matchtime );', $cs_match ) );
	} else {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r1 = $wpdb->get_results( $wpdb->prepare( 'SELECT spieltag as sday FROM %i where spieltag > 0 GROUP BY spieltag;', $cs_match ) );
	}

	foreach ( $r1 as $r ) {
		$out .= "<option value='" . $r->sday . "'>" . $r->sday . '</option>';
	}

	$out .= '</select>';
	$out .= "<input id='wpc_selector_site' type='hidden' value='" . plugins_url( '', __FILE__ ) . "' />";
	$out .= '</form>';
	$out .= "<script type='text/javascript'>window.onDomReady(wpc_stats1_update);</script>";
	$out .= '</div>';
	$out .= "<div id='wpc-stats1-res'></div>";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 2 Verteilung der Tipps über alle User
 *
 * @param array $atts Parameter für die Statistik.
 */
function show_stats2( $atts = array() ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	if ( '' != $tippgroup ) {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				IF(result1>result2,concat(cast(result1 as char), cast(result2 as char)), 
				concat(cast(result2 as char), cast(result1 as char))) as tip, count(*) as anzahl
				FROM %i a inner join %i b on a.userid=b.userid 
				WHERE result1>=0 and result2>=0 and tippgroup=%s  
				group by IF(result1>result2,concat(cast(result1 as char), 
				cast(result2 as char)), concat(cast(result2 as char), cast(result1 as char)))",
				$cs_tipp,
				$cs_users,
				$tippgroup
				)
			);
			// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT 
				IF(result1>result2,concat(cast(result1 as char), cast(result2 as char)), 
				concat(cast(result2 as char), cast(result1 as char))) as tip, count(*) as anzahl
				FROM %i WHERE result1>=0 and result2>=0
				group by IF(result1>result2,concat(cast(result1 as char), 
				cast(result2 as char)), concat(cast(result2 as char), cast(result1 as char)))',
				$cs_tipp
			)
		);
		// @codingStandardsIgnoreEnd
	}

	// Ueberschrift ausgeben.
	$out .= '<h2>' . __( 'Tip - frequency of occurrence', 'wp-championship' ) . '</h2>';

	if ( empty( $r1 ) ) {
		$out .= __( 'No tips entered yet.', 'wp-championship' );
	} else {
		$urlparm = '?';
		$tanz    = 0;
		// anzahl aller tipps ermitteln.
		foreach ( $r1 as $r ) {
			$tanz = $tanz + $r->anzahl;
		}

		foreach ( $r1 as $r ) {
			$urlparm .= $r->tip . '=' . round( $r->anzahl / $tanz, 2 ) * 100 . '&';
		}

		$out .= '<p>&nbsp;</p>';
		$out .= "<img src='" . plugin_dir_url( __FILE__ ) . 'func-pie.php' . $urlparm . "' alt='Piechart'/>";
	}

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 3 Verteilung der Tipps pro Spieler.
 *
 * @param array $atts Parameter für die Statistik.
 */
function show_stats3( $atts = array() ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	if ( '' != $tippgroup ) {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login else b.user_login end as vdisplay_name,
				IF(result1>result2,concat(cast(result1 as char), cast(result2 as char)), 
				concat(cast(result2 as char), cast(result1 as char))) as tip, count(*) as anzahl
				FROM %i a inner join %i b on b.ID=a.userid
				inner join %i c on a.userid = c.userid
				WHERE result1>=0 and result2>=0 and c.tippgroup=%s
				group by a.userid, IF(result1>result2,concat(cast(result1 as char), 
				cast(result2 as char)), concat(cast(result2 as char), cast(result1 as char)))",
				$cs_tipp,
				$wp_users,
				$cs_users,
				$tippgroup
			)
		);
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name,
				IF(result1>result2,concat(cast(result1 as char), cast(result2 as char)), 
				concat(cast(result2 as char), cast(result1 as char))) as tip, count(*) as anzahl
				FROM %i inner join %i on ID=userid
				WHERE result1>=0 and result2>=0
				group by userid, IF(result1>result2,concat(cast(result1 as char), 
				cast(result2 as char)), concat(cast(result2 as char), cast(result1 as char)))",
				$cs_tipp,
				$wp_users
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$out .= '<h2>' . __( 'Tip - frequency in detail', 'wp-championship' ) . '</h2>';

	if ( empty( $r1 ) ) {
		$out .= __( 'No tips entered yet.', 'wp-championship' );
	} else {
		$out .= '<p>&nbsp;</p>';
		$out .= "<table id='stats3' border='1' ><tr id='stats3'><th id='stats3'>" . __( 'Player', 'wp-championship' ) . "</th>\n";

		// matrix aufbauen.
		$sm = array();
		foreach ( $r1 as $r ) {
			$erg                             = $r->tip[0] . ':' . $r->tip[1];
			$sm[ $r->vdisplay_name ][ $erg ] = $r->anzahl;
		}

		// erzeuge liste der vorkommenden ergebnisse.
		$sm1 = array();
		foreach ( array_keys( $sm ) as $uk ) {
			foreach ( array_keys( $sm[ $uk ] ) as $ek ) {
				array_push( $sm1, $ek );
			}
		}
		$sm1 = array_unique( $sm1 );
		asort( $sm1 );

		foreach ( $sm1 as $ek ) {
			$out .= "<th id='stats3' style='padding:5px;text-align:center;'>" . $ek . '</th>';
		}
		$out .= '</tr>';

		$olduser = '';
		foreach ( $sm as $uname => $r ) {
			if ( $olduser != $uname ) {
				if ( '' != $olduser ) {
					$out .= "</tr>\n";
				}
					$out .= '<tr><td>' . $uname . '</td>';
			}
			foreach ( $sm1 as $erg ) {
				if ( isset( $r[ $erg ] ) && $r[ $erg ] > 0 ) {
					$out .= "<td style='text-align:right'>" . $r[ $erg ] . "</td>\n";
				} else {
					$out .= "<td style='text-align:right'>-</td>\n";
				}
			}
			$olduser = $uname;
		}
		$out .= "</tr></table>\n";
	}

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 4 Tipps eines ausgewählten Spielers.
 *
 * @param array $atts Parameter für die Statistik.
 */
function show_stats4( $atts ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	if ( '' != $tippgroup ) {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name 
				FROM %i inner join %i on ID=userid where tippgroup = %s order by vdisplay_name;",
				$cs_users,
				$wp_users,
				$tippgroup
			)
		);
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name 
				FROM %i inner join %i on ID=userid order by vdisplay_name;",
				$cs_users,
				$wp_users
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$blog_now = current_time( 'mysql', 0 );
	if ( get_option( 'cs_modus' ) == 1 ) {
		// show all tipps or just tips form finished matches.
		if ( get_option( 'cs_stats4_showall' ) == 1 ) {
			// @codingStandardsIgnoreStart
			$r2 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
					a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where ( ( a.round in ('V','F') and b.name not like '#%') or ( a.round in ('V') ) ) 
					order by origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$r2 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
					a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where a.round in ('V','F') and matchtime < %s 
					order by origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team,
					$blog_now
				)
			);
			// @codingStandardsIgnoreEnd
		}
	} else {
		// @codingStandardsIgnoreStart
		$r2 = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
				date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
				a.winner as winner,a.round as round, a.spieltag as spieltag 
				from %i a inner join %i b on a.tid1=b.tid 
				inner join %i c on a.tid2=c.tid 
				where a.round = 'V' and matchtime < %s 
				order by spieltag,origtime;",
				'%d.%m<br />%H:%i',
				$cs_match,
				$cs_team,
				$cs_team,
				$blog_now
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$out .= '<h2>' . __( 'Player-Tips', 'wp-championship' ) . '</h2>';

	$out .= "<div class='wpc-stats4-sel'><form action='#'>" . __( 'Player', 'wp-championship' ) . ':';
	$out .= '<input name="wpc_nonce_stats" id="wpc_nonce_stats" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_stats' ) . '" />';
	$out .= "<input id='wpc_stats4_tippgroup' type='hidden' value='$tippgroup' />";
	$out .= "<select id='wpc_stats4_selector' size='1' onchange='wpc_stats4_update();' >";

	$out .= "<option value='?'>" . __( 'All', 'wp-championship' ) . '</option>';
	foreach ( $r1 as $r ) {
		$out .= "<option value='" . $r->vdisplay_name . "'>" . $r->vdisplay_name . '</option>';
	}
	$out .= '</select>';

	$out .= ' ' . __( 'Match', 'wp-championship' ) . ':';
	$out .= "<select id='wpc_stats4_selector2' size='1' onchange='wpc_stats4_update();' >";
	$out .= "<option value='?'>" . __( 'All', 'wp-championship' ) . '</option>';
	foreach ( $r2 as $r ) {
		$out .= "<option value='" . $r->mid . "'>" . $r->team1 . ' - ' . $r->team2 . '</option>';
	}
	$out .= '</select>';

	$out .= "<input id='wpc_selector_site4' type='hidden' value='" . plugins_url( '', __FILE__ ) . "' />";
	$out .= '</form>';
	$out .= "<script type='text/javascript'>window.onDomReady(wpc_stats4_update);</script>";
	$out .= '</div>';
	$out .= "<div id='wpc-stats4-res'></div>";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 5 Spieltagsübersicht.
 *
 *  @param array $atts Parameter für die Statistik.
 */
function show_stats5( $atts ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	$out .= '<h2>' . __( 'Day of event statistic', 'wp-championship' ) . '</h2>';

	$out .= "<div class='wpc-stats5-sel'><form action='#'>" . __( 'Match-Day', 'wp-championship' ) . ':';
	$out .= '<input name="wpc_nonce_stats" id="wpc_nonce_stats" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_stats' ) . '" />';
	$out .= "<input id='wpc_stats5_tippgroup' type='hidden' value='$tippgroup' />";
	$out .= "<select id='wpc_stats5_selector' size='1' onchange='wpc_stats5_update();' >";
	if ( get_option( 'cs_modus' ) == 1 ) {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r1 = $wpdb->get_results( $wpdb->prepare( 'SELECT date( matchtime ) as sday FROM %i GROUP BY date( matchtime );', $cs_match ) );
	} else {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r1 = $wpdb->get_results( $wpdb->prepare( 'SELECT spieltag as sday FROM %i where spieltag > 0 GROUP BY spieltag;', $cs_match ) );
	}

	foreach ( $r1 as $r ) {
		$out .= "<option value='" . $r->sday . "'>" . $r->sday . '</option>';
	}

	$out .= '</select>';
	$out .= "<input id='wpc_selector_site' type='hidden' value='" . plugins_url( '', __FILE__ ) . "' />";
	$out .= '</form>';
	$out .= "<script type='text/javascript'>window.onDomReady(wpc_stats5_update);</script>";
	$out .= '<p>* Player used Joker</p>';
	$out .= '</div>';
	$out .= "<div id='wpc-stats5-res'></div>";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 6: Spiele einer Mannschaft.
 *
 *  @param array $atts Parameter für die Statistik.
 */
function show_stats6( $atts ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	$out .= '<h2>' . __( 'Team-Stats', 'wp-championship' ) . '</h2>';

	$out .= "<div class='wpc-stats6-sel'><form action='#'>" . __( 'Team', 'wp-championship' ) . ':';
	$out .= '<input name="wpc_nonce_stats" id="wpc_nonce_stats" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_stats' ) . '" />';
	$out .= "<input id='wpc_stats6_tippgroup' type='hidden' value='$tippgroup' />";
	$out .= "<select id='wpc_stats6_selector' size='1' onchange='wpc_stats6_update();' >";

	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r1 = $wpdb->get_results( $wpdb->prepare( "SELECT tid, name, shortname FROM %i where substring(name,1,1) <> '#' order by name;", $cs_team ) );

	foreach ( $r1 as $r ) {
		$out .= "<option value='" . $r->tid . "'>" . $r->name . " ($r->shortname)</option>";
	}

	$out .= '</select>';
	$out .= "<input id='wpc_selector_site' type='hidden' value='" . plugins_url( '', __FILE__ ) . "' />";
	$out .= '</form>';
	$out .= "<script type='text/javascript'>window.onDomReady(wpc_stats6_update);</script>";
	$out .= '</div>';
	$out .= "<div id='wpc-stats6-res'></div>";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 7 Tipper des Monats.
 *
 * @param array $atts Parameter für die Statistik.
 */
function show_stats7( $atts ) {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// parameter holen dabei übersteuert tippgruppe, tippgroup.
	$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' ) );

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d', $cs_users, $uid ) );

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	$out .= '<h2>' . __( 'Tiper of the month', 'wp-championship' ) . '</h2>';

	$out .= "<div class='wpc-stats7-sel'><form action='#'>" . __( 'Month', 'wp-championship' ) . ':';
	$out .= '<input name="wpc_nonce_stats" id="wpc_nonce_stats" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_stats' ) . '" />';
	$out .= "<input id='wpc_stats7_tippgroup' type='hidden' value='$tippgroup' />";
	$out .= "<select id='wpc_stats7_selector' size='1' onchange='wpc_stats7_update();' >";

	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r1 = $wpdb->get_results( $wpdb->prepare( "SELECT year(matchtime) as year, lpad(month(matchtime),2,'0') as month FROM %i group by year(matchtime), month(matchtime) order by year(matchtime), month(matchtime);", $cs_match ) );

	foreach ( $r1 as $r ) {
		$sel = '';
		if ( current_time( 'Y' ) == $r->year && current_time( 'm' ) == $r->month ) {
			$sel = "selected='selected'";
		}
		$out .= "<option value='" . $r->year . $r->month . "' $sel>" . $r->year . '-' . $r->month . "</option>\n";
	}
	$out .= '</select>';
	$out .= "<input id='wpc_selector_site' type='hidden' value='" . plugins_url( '', __FILE__ ) . "' />";
	$out .= '</form>';
	$out .= "<script type='text/javascript'>window.onDomReady(wpc_stats7_update);</script>";
	$out .= '</div>';
	$out .= "<div id='wpc-stats7-res'></div>";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 8 Tippgruppenranking.
 */
function show_stats8() {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	// lese anwenderdaten ein.
	$userdata = wp_get_current_user();
	// merke die userid.
	$uid = $userdata->ID;

	// userdaten lesen.
	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i a inner join %i b on a.tippgroup = b.tgid where userid=%d', $cs_users, $cs_tippgroup, $uid ) );

	$user_tgid = $r0[0]->tgid;

	// admin flag setzen.
	$is_admin = false;
	if ( 1 == $r0[0]->admin ) {
		$is_admin = true;
	}

	// ermittle aktuelle uhrzeit.
	$currtime = current_time( 'Y-m-d H:i:s' );

	//
	// lese alternative bezeichnungen.
	//
	$cs_label_place     = get_option( 'cs_label_place' );
	$cs_label_tippgroup = get_option( 'cs_label_tippgroup' );
	$cs_label_upoints   = get_option( 'cs_label_upoints' );
	$cs_label_trend     = get_option( 'cs_label_trend' );
	$cs_label_average   = get_option( 'cs_label_average' );
	$cs_label_numusers  = get_option( 'cs_label_numusers' );
	$cs_col_place       = get_option( 'cs_col_place' );
	$cs_col_tippgroup   = get_option( 'cs_col_tippgroup' );
	$cs_col_average     = get_option( 'cs_col_average' );
	$cs_col_numusers    = get_option( 'cs_col_numusers' );
	$cs_col_upoints     = get_option( 'cs_col_upoints' );

	if ( '' == $cs_label_place ) {
		$cs_label_place = __( 'Rank', 'wp-championship' );
	}
	if ( '' == $cs_label_tippgroup ) {
		$cs_label_tippgroup = __( 'Tip Group', 'wp-championship' );
	}
	if ( '' == $cs_label_upoints ) {
		$cs_label_upoints = __( 'Score', 'wp-championship' );
	}
	if ( '' == $cs_label_average ) {
		$cs_label_average = __( 'Average', 'wp-championship' );
	}
	if ( '' == $cs_label_numusers ) {
		$cs_label_numusers = __( 'Number of tips', 'wp-championship' );
	}

	$out .= '<h2>' . __( 'Current scoring of the tip groups', 'wp-championship' ) . "</h2>\n";

	// pruefe ob Tippgruppen aktiviert sind, wenn nicht schreibe Mitteilung und beende die Funktion.
	if ( get_option( 'cs_use_tippgroup' ) == 0 ) {
		$out .= __( 'Please activate tippgroups to use this statistic.', 'wp-championship' ) . '<br />';
		return $out;
	}

	// ausgabe des aktuellen punktestandes und des ranges.
	$rank = cs_get_tippgroup_ranking();

	$i            = 0;
	$j            = 1;
	$pointsbefore = -1;
	foreach ( $rank as $row ) {
		// platzierung erhoehen, wenn punkte sich veraendern.
		if ( $row->points != $pointsbefore ) {
			$i += $j;
			$j  = 1;
		} else {
			++$j;
		}

		if ( $row->tgid == $user_tgid ) {
			$out .= '<div><b>' . __( 'You are member of tippgroup', 'wp-championship' ) . ' ' . $r0[0]->name . '. Deine Tippgruppe hat ' . $row->points . ' ' . __( 'points and your current rank is', 'wp-championship' ) . " $i.</b></div>";
		}
		// gruppenwechsel versorgen.
		$pointsbefore = $row->points;
	}

	// ausgabe des aktuellen punktestandes und des ranges.
	$out .= "<table class='tablesorter'><tr>\n";
	if ( ! $cs_col_place ) {
		$out .= '<th scope="col" style="text-align: center">' . $cs_label_place . '</th>' . "\n";
	}
	if ( ! $cs_col_tippgroup ) {
		 $out .= '<th scope="col" style="text-align: center">' . $cs_label_tippgroup . '</th>' . "\n";
	}
	if ( ! $cs_col_upoints ) {
		 $out .= '<th style="width:20;text-align: center">' . $cs_label_upoints . '</th>';
	}
	if ( ! $cs_col_numusers ) {
		 $out .= '<th style="width:20;text-align: center">' . $cs_label_numusers . '</th>';
	}
	if ( ! $cs_col_average ) {
		 $out .= '<th style="width:20;text-align: center">' . $cs_label_average . '</th>';
	}

	$out .= "</tr>\n";

	$rank = cs_get_tippgroup_ranking();

	$pointsbefore = -1;
	$i            = 0;
	$j            = 1;
	foreach ( $rank as $row ) {
		// platzierung erhoehen, wenn punkte sich veraendern.
		if ( $row->points != $pointsbefore ) {
			$i = $i + $j;
			$j = 1;
		} else {
			++$j;
		}

		$out .= '<tr>';

		if ( ! $cs_col_place ) {
			$out .= "<td style='text-align:center'>$i</td>";
		}
		if ( ! $cs_col_tippgroup ) {
			$out .= "<td style='text-align:center'>" . $row->name . ' (' . $row->members . ')</td>';
		}
		if ( ! $cs_col_upoints ) {
			$out .= "<td style='text-align:center'>" . $row->points . '</td>';
		}
		if ( ! $cs_col_numusers ) {
			$out .= "<td style='text-align:center'>" . $row->numusers . '</td>';
		}
		if ( ! $cs_col_average ) {
			$out .= "<td style='text-align:center'>" . round( $row->average, 2 ) . '</td>';
		}
		$out .= '</tr>';

		// gruppenwechsel versorgen.
		$pointsbefore = $row->points;
	}
	$out .= '</table>' . "<p>&nbsp;</p>\n";

	return $out;
}

/**
 * Funktion zur Ausgabe der Statistik 9 Siegertipps.
 */
function show_stats9() {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	// setze iconpath.
	if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
		$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
	} else {
		$iconpath = plugins_url( 'icons/', __FILE__ );
	}

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		return $out;
	}

	$out .= '<h2>' . __( 'Champion-Tip Score', 'wp-championship' ) . "</h2>\n";

	// pruefen ob Turnier schon begonnen hat, sonst Hinweis ausgeben.
	$blog_now = current_time( 'mysql', 0 );

	// The placeholder ignores can be removed when %i is supported by WPCS.
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i', $cs_match ) );

	if ( $blog_now <= $mr->mintime ) {
		$out .= __( 'The tournament has not started yet.', 'wp-championship' ) . "<br />\n";
		return $out;
	}

	// anzeigen der gewinnermannschaft falls tunier schon beendet.
	$cswinner = cs_get_cswinner();
	if ( $cswinner ) {
		$out .= '<hr>' . __( 'The championship winner is:', 'wp-championship' ) . " <b>$cswinner</b><hr>";
	} else {
		$out .= '<hr>' . __( 'The winner of the championship has not been determined yet.', 'wp-championship' ) . '</b><hr>';
	}

	$out .= "<table class='tablesorter'><tr>\n";

	$out .= '<th scope="col" style="text-align: center">' . __( 'Player', 'wp-championship' ) . '</th>' . "\n";
	$out .= '<th scope="col" style="text-align: center">' . __( 'Champion-Tip', 'wp-championship' ) . '</th>' . "\n";
	$out .= '<th style="width:20;text-align: center">' . __( 'Points', 'wp-championship' ) . '</th>';
	$out .= "</tr>\n";

	// @codingStandardsIgnoreStart
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"select b.user_nicename, case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login 
			else b.user_login end as vdisplay_name, a.userid, a.champion, c.name, c.icon 
			from %i a inner join %i b on a.userid=b.ID left outer join %i as c on a.champion = c.tid 
			order by a.userid",
			$cs_users,
			$wp_users,
			$cs_team
		)
	);
	// @codingStandardsIgnoreEnd

	foreach ( $res as $r ) {
		// zeile ausgeben.
		$out .= '<tr>';

		$out .= "<td style='text-align:center'>" . $r->vdisplay_name . '</td>';
		if ( substr( $r->icon, 0, 4 ) == 'http' ) {
			$iconpath = '';
		}
		$out .= "<td style='text-align:center'>";
		if ( ! is_null( $r->icon ) ) {
			$out .= "<img class='csicon' alt='icon1' width='20' src='" . $iconpath . $r->icon . "' />&nbsp;&nbsp;";
		}
		if ( ! is_null( $r->name ) ) {
			$out .= $r->name . '</td>';
		} else {
			$out .= '-</td>';
		}

		$points = 0;
		if ( '' != $cswinner && $r->name == $cswinner ) {
			$points = get_option( 'cs_pts_champ' );
		}
		$out .= "<td style='text-align:center'>" . $points . ' </td>';
		$out .= "</tr>\n";
	}
	$out .= '</table>' . "<p>&nbsp;</p>\n";

	return $out;
}

/**
 * Funktion zur Aktualisierung via Ajax der Statistik 1 Punkte jedes Spielers pro Spieltag.
 */
function update_stats1() {
	include 'globals.php';

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_stats' ) ) {
		die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
	}

	$tippgroup = ( isset( $_POST['tippgroup'] ) ? sanitize_text_field( wp_unslash( $_POST['tippgroup'] ) ) : '%' );
	$newday    = ( isset( $_POST['newday'] ) ? sanitize_text_field( wp_unslash( $_POST['newday'] ) ) : '' );

	if ( '' == $tippgroup ) {
			$tippgroup = '%';
	}

	if ( 1 == get_option( 'cs_modus' ) ) {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login 
				else b.user_login end as vdisplay_name, sum(e.points) as punkte,count(e.points) as ctipps 
				from %i a inner join %i b on a.userid = b.ID 
				left outer join ( 
					select c.* from %i c inner join %i d on c.mid = d.mid 
					where date(d.matchtime) = %s 
					and c.points>0 ) e 
				on e.userid = a.userid 
				where a.tippgroup like %s 
				group by vdisplay_name 
				order by sum(e.points) desc",
				$cs_users,
				$wp_users,
				$cs_tipp,
				$cs_match,
				$newday,
				$tippgroup 
			)
		);
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login 
				else b.user_login end as vdisplay_name, sum(e.points) as punkte, count(e.points) as ctipps 
				from %i a inner join %i b on a.userid = b.ID 
				left outer join ( 
					select c.* from %i c inner join %i d on c.mid = d.mid 
					where spieltag = %s and c.points>0 ) e 
				on e.userid = a.userid 
				%s
				group by vdisplay_name 
				order by sum(e.points) desc",
				$cs_users,
				$wp_users,
				$cs_tipp,
				$cs_match,
				$newday,
				$tippgroup_sql
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$out  = '<p>&nbsp;</p>';
	$out .= "<table border='1' ><tr><th>" . __( 'Player', 'wp-championship' ) . '</th><th>' . __( 'Points', 'wp-championship' ) . '</th><th>' . __( 'Tips', 'wp-championship' ) . "</th></tr>\n";

	foreach ( $r1 as $r ) {
		$out .= '<tr><td>' . $r->vdisplay_name . "</td><td style='text-align:right'>" . ( null == $r->punkte ? 0 : $r->punkte ) . "</td><td style='text-align:right'>" . ( null == $r->ctipps ? 0 : $r->ctipps ) . "</td></tr>\n";
	}

	$out .= "</table>\n";

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_update_stats1', 'update_stats1' );

/**
 * Funktion zur Aktualisierung via Ajax der Statistik 4 Tipps eines ausgewählten Spielers.
 */
function update_stats4() {
	include 'globals.php';

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_stats' ) ) {
		die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
	}

	// Stats 4.
	$tippgroup = ( isset( $_POST['wpc_stats4_tippgroup'] ) ? sanitize_text_field( wp_unslash( $_POST['wpc_stats4_tippgroup'] ) ) : '%' );
	$username  = ( isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '' );
	$match     = ( isset( $_POST['match'] ) ? intval( $_POST['match'] ) : '' );

	$tippgroup_sql = '%';
	if ( '' != $tippgroup ) {
		$tippgroup_sql = $tippgroup;
	}

	$blog_now = current_time( 'mysql', 0 );

	$match_sql = '%';
	if ( 0 != $match ) {
		$match_sql = $match;
	}

	if ( 1 == get_option( 'cs_modus' ) ) {
		// show all tips or just tips from finished matches.
		if ( get_option( 'cs_stats4_showall' ) == 1 ) {
			// @codingStandardsIgnoreStart
            $r1 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime, %s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
					a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid
					where ( ( a.round in ('V','F') ) or ( a.round in ('V') ) ) 
					and mid like %s 
					order by origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team,
					$match_sql
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
					date_format(a.matchtime,%s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
					a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
					where a.round in ('V','F') and matchtime < %s 
					and mid like %s 
					order by origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team,
					$blog_now,
					$match_sql
				)
			);
		}
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid as mid,b.groupid as groupid,b.name as team1,b.icon as icon1, c.name as team2,c.icon as icon2,a.location as location,
				date_format(a.matchtime,%s) as matchtime,a.matchtime as origtime,a.result1 as result1,a.result2 as result2,
				a.winner as winner,a.round as round, a.spieltag as spieltag 
				from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
				where a.round = 'V' and matchtime < %s 
				and mid like %s 
				order by spieltag,origtime;",
				'%d.%m<br />%H:%i',
				$cs_match,
				$cs_team,
				$cs_team,
				$blog_now,
				$match_sql
			)
		);
		// @codingStandardsIgnoreEnd
	}

	// hole tipps des users.
	if ( '?' != $username ) {
		// @codingStandardsIgnoreStart
		$r2 = $wpdb->get_results(
			$wpdb->prepare(
				'select mid,userid, result1, result2, points from %i inner join %i on ID=userid where result1<>-1 
				and display_name=%s or user_login=%s order by mid',
				$cs_tipp,
				$wp_users,
				$username,
				$username
			)
		);
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r2 = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid, a.userid, case when display_name != '' then display_name when display_name is null then user_login else user_login end 
				as vdisplay_name,result1,result2,points 
				from  %i a inner join %i b on b.ID = a.userid 
				inner join %i c on b.ID = c.userid
				where tippgroup like %s
				and result1<>-1 order by mid",
				$cs_tipp,
				$wp_users,
				$cs_users,
				$tippgroup_sql
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$tipps = array();
	foreach ( $r2 as $r ) {
		$tipps[ $r->mid ][ $r->userid ] = $r;
	}

	// hole relevante user.
	if ( '?' != $username ) {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r3 = $wpdb->get_results( $wpdb->prepare( 'select ID from %i where display_name=%s or user_login=%s;', $wp_users, $username, $username ) );
	} else {
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r3 = $wpdb->get_results( $wpdb->prepare( 'select ID from %i order by user_login', $wp_users ) );
	}

	$users = array();
	foreach ( $r3 as $r ) {
		$users[ $r->ID ] = $r->ID;
	}

	$out  = '<p>&nbsp;</p>';
	$out .= "<table border='1' ><tr><th>" . __( 'match', 'wp-championship' ) . '</th>';
	if ( '?' == $username ) {
		$out .= '<th>' . __( 'Player', 'wp-championship' ) . '</th>';
	}
	$out .= '<th>' . __( 'Result', 'wp-championship' ) . '</th><th>' . __( 'Tip', 'wp-championship' ) . '</th><th>' . __( 'Points', 'wp-championship' ) . '</th></tr>';

	if ( empty( $r2 ) ) {
		$out .= "<tr><td colspan='4'>" . __( 'No tips entered yet.', 'wp-championship' ) . '</td></tr>';
	} else {
		$lteam = '';
		if ( empty( $r1 ) ) {
			$out .= "<tr><td colspan='5'>" . __( 'The matches have not started yet.', 'wp-championship' ) . '</td></tr>';
		}
		foreach ( $r1 as $r ) {
			foreach ( $users as $s ) {
				$ctipp = ( isset( $tipps[ $r->mid ][ $s ] ) ? $tipps[ $r->mid ][ $s ] : '' );
				$tr1   = ( isset( $ctipp->result1 ) && -1 != $ctipp->result1 ? $ctipp->result1 : '-' );
				$tr2   = ( isset( $ctipp->result2 ) && -1 != $ctipp->result2 ? $ctipp->result2 : '-' );
				$rr1   = ( isset( $r->result1 ) && -1 != $r->result1 ? $r->result1 : '-' );
				$rr2   = ( isset( $r->result2 ) && -1 != $r->result2 ? $r->result2 : '-' );

				if ( '-' != $tr1 ) {
					if ( $lteam != $r->team1 . ' - ' . $r->team2 ) {
						$out .= '<tr><td>' . $r->team1 . ' - ' . $r->team2 . '</td>';
					} else {
						$out .= '<tr><td>&nbsp;</td>';
					}
					$lteam = $r->team1 . ' - ' . $r->team2;
					if ( '?' == $username ) {
						$out .= "<td style='text-align:center'>" . $ctipp->vdisplay_name . "</td>\n";
					}
					$out .= "<td style='text-align:center'>" . $rr1 . ':' . $rr2 . "</td>\n";
					$out .= "<td style='text-align:center'>" . $tr1 . ':' . $tr2 . "</td>\n";
					$out .= "<td style='text-align:center'>" . ( -1 == $ctipp->points ? 0 : $ctipp->points ) . "</td></tr>\n";
				}
			}
		}
	}
	$out .= "</table>\n";

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_update_stats4', 'update_stats4' );

/**
 * Funktion zur Aktualisierung via Ajax der Statistik 5 Spieltagsübersicht.
 */
function update_stats5() {
	// Stats 5.
	include 'globals.php';

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_stats' ) ) {
		die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
	}

	$newday5 = ( isset( $_POST['newday5'] ) ? sanitize_text_field( wp_unslash( $_POST['newday5'] ) ) : '' );

	// get data for header.
	if ( get_option( 'cs_modus' ) == 1 ) {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid as mid,b.groupid as groupid,b.name as team1,b.shortname as shortname1,b.icon as icon1, c.name as team2,
				c.shortname as shortname2, c.icon as icon2,a.location as location,date_format(a.matchtime, %s) as matchtime,
				a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
				from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
				where a.round in ('V','F') and result1>-2 and result2>-2 and date(a.matchtime)=%s 
				order by origtime,mid;",
				'%d.%m<br />%H:%i',
				$cs_match,
				$cs_team,
				$cs_team,
				$newday5
			)
		);
		// @codingStandardsIgnoreEnd
	} else {
		// @codingStandardsIgnoreStart
		$r1 = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid as mid,b.groupid as groupid,b.name as team1,b.shortname as shortname1, b.icon as icon1, c.name as team2,
				c.shortname as shortname2, c.icon as icon2,a.location as location,date_format(a.matchtime, %s) as matchtime,
				a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
				from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
				where a.round = 'V' and result1>-2 and result2>-2 and spieltag=%d 
				order by spieltag,origtime,mid;",
				'%d.%m<br />%H:%i',
				$cs_match,
				$cs_team,
				$cs_team,
				$newday5
			)
		);
		// @codingStandardsIgnoreEnd
	}

	$out  = '<p>&nbsp;</p>';
	$out .= "<table id='stats5' border='1' ><tr><th id='stats5'>" . __( 'Username', 'wp-championship' ) . '</th>';
	foreach ( $r1 as $r ) {
		$short_team1 = ( strlen( trim( $r->shortname1 ) ) > 0 ? $r->shortname1 : substr( $r->team1, 0, 3 ) );
		$short_team2 = ( strlen( trim( $r->shortname2 ) ) > 0 ? $r->shortname2 : substr( $r->team2, 0, 3 ) );

		$out .= "<th id='stats5'>" . $short_team1 . '<br />' . ( -1 == $r->result1 ? '-' : $r->result1 ) . ':' . ( -1 == $r->result2 ? '-' : $r->result2 ) . '<br/>' . $short_team2 . '</th>';
	}
	$out .= "<th id='stats5'>&empty;</th><th id='stats5'>" . __( 'Points', 'wp-championship' ) . '</th>';
	$out .= '</tr>';

	$tippgroup     = ( isset( $_GET['tippgroup'] ) ? sanitize_text_field( wp_unslash( $_GET['tippgroup'] ) ) : '' );
	$tippgroup_sql = '%';
	if ( '' != $tippgroup ) {
		$tippgroup_sql = $tippgroup;
	}

	// get data for table.
	// @codingStandardsIgnoreStart
	$r2 = $wpdb->get_results(
		$wpdb->prepare(
			"select case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name, 
			userid, jokerlist 
			from %i inner join %i on ID=userid 
			where tippgroup like %s 
			order by vdisplay_name;",
			$wp_users,
			$cs_users,
			$tippgroup_sql
		)
	);
	// @codingStandardsIgnoreEnd

	foreach ( $r2 as $r ) {
		// fetch results per day and user.
		if ( get_option( 'cs_modus' ) == 1 ) {
			// @codingStandardsIgnoreStart
			$r3 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid,a.result1 as res1, a.result2 as res2, a.points as points, b.matchtime as origtime 
					from %i b left outer join %i a on a.mid=b.mid and a.userid=%d 
					where date(matchtime)=%s and b.result1>-1 and b.result2>-1 and b.round in ('V','F') 
					order by origtime;",
					$cs_match,
					$cs_tipp,
					$r->userid,
					$newday5
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$r3 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid,a.result1 as res1, a.result2 as res2, a.points as points, b.matchtime as origtime 
					from %i b left outer join %i a on a.mid=b.mid and a.userid=%d 
					where spieltag=%d  and b.result1>-1 and b.result2>-1 and b.round ='V' 
					order by spieltag,origtime;",
					$cs_match,
					$cs_tipp,
					$newday5
				)
			);
			// @codingStandardsIgnoreEnd
		}

		// sort it into an array with the matchid as index.
		$r4 = array();
		foreach ( $r3 as $t ) {
			$r4[ $t->mid ] = $t;
		}

		if ( $r4 ) {
			$out .= '<tr><td>' . $r->vdisplay_name . '</td>';
			$anz  = 0;
			$sum  = 0;
			foreach ( $r1 as $s ) {
				$mday = $s->mid;

				if ( isset( $r4[ $mday ] ) && ( -1 == $r4[ $mday ]->res1 || null == $r4[ $mday ]->res1 ) ) {
					$out .= '<td>-:-<sub>-</sub></td>';
				} else {
					if ( isset( $r4[ $mday ] ) ) {
						if ( in_array( $mday, explode( ',', $r->jokerlist ) ) ) {
							$out .= '<td>' . $r4[ $mday ]->res1 . ':' . $r4[ $mday ]->res2 . '<sub>' . $r4[ $mday ]->points . '*</sub></td>';
						} else {
							$out .= '<td>' . $r4[ $mday ]->res1 . ':' . $r4[ $mday ]->res2 . '<sub>' . $r4[ $mday ]->points . '</sub></td>';
						}
						$sum += $r4[ $mday ]->points;
					} elseif ( in_array( $mday, explode( ',', $r->jokerlist ) ) ) {
							$out .= '<td>-:-<sub>0*</sub></td>';
					} else {
						$out .= '<td>-:-<sub>0</sub></td>';
					}
					++$anz;
				}
			}
			if ( $anz > 0 ) {
				$out .= '<td>' . round( $sum / $anz, 2 ) . '</td>';
			} else {
				$out .= '<td>-</td>';
			}
			$out .= "<td>$sum</td>";
			$out .= '</tr>';
		}
	}
	$out .= '</table>';

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_update_stats5', 'update_stats5' );

/**
 * Funktion zur Aktualsierung der Statistik 6: Spiele einer Mannschaft.
 */
function update_stats6() {
	include 'globals.php';

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_stats' ) ) {
		die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
	}

	if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
		$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
	} else {
		$iconpath = plugins_url( 'icons/', __FILE__ );
	}

	$team    = ( isset( $_POST['team'] ) ? intval( $_POST['team'] ) : '' );
	$matches = cs_get_team_matches( $team );

	$out  = '<p>&nbsp;</p>';
	$out .= "<table border='1' >\n";
	$out .= '<tr><th>' . __( 'Date', 'wp-championship' ) . '</th><th>&nbsp;</th><th>' . __( 'match', 'wp-championship' ) . '</th><th>&nbsp;</th><th>' . __( 'Result', 'wp-championship' ) . '</th></tr>';

	foreach ( $matches as $m ) {
		$out .= '<tr><td>' . $m['date'] . '</td>';
		if ( substr( $m['icon1'], 0, 4 ) == 'http' ) {
				$out .= "<td><img src='" . $m['icon1'] . "' width='30'></td>";
		} else {
			$out .= "<td><img src='" . $iconpath . $m['icon1'] . "' width='30'></td>";
		}
		$out .= "<td style='text-align:center'>" . $m['name1'] . ' - ' . $m['name2'] . '</td>';
		if ( substr( $m['icon2'], 0, 4 ) == 'http' ) {
			$out .= "<td><img src='" . $m['icon2'] . "' width='30'></td>";
		} else {
			$out .= "<td><img src='" . $iconpath . $m['icon2'] . "' width='30'></td>";
		}

		$out .= "<td style='text-align:center'>" . $m['res1'] . ':' . $m['res2'] . "</td></tr>\n";
	}
	$out .= "</table>\n";

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_update_stats6', 'update_stats6' );

/**
 * Funktion zur Aktualisierung der Statistik 7 Tipper des Monats.
 */
function update_stats7() {
	include 'globals.php';

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpc_nonce_stats' ) ) {
		die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
	}

	$newday        = ( isset( $_POST['newday'] ) ? intval( $_POST['newday'] ) : '' );
	$tippgroup     = ( isset( $_POST['tippgroup'] ) ? sanitize_text_field( wp_unslash( $_POST['tippgroup'] ) ) : '' );
	$tippgroup_sql = '';
	if ( '' != $tippgroup ) {
		$tippgroup_sql = " where a.tippgroup='$tippgroup' ";
	}

	// @codingStandardsIgnoreStart
	$r1 = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login else b.user_login end as vdisplay_name,
		     date_format(matchtime,%s) as ym, sum(c.points) as mp, 
			 min(f.gp) as gp, count(c.points) as nt
			from %i a 
			inner join %i b on a.userid = b.ID
			inner join %i c on a.userid = c.userid
			inner join %i d on d.mid = c.mid
			inner join (select e.userid,sum(e.points) as gp 
				from %i e 
				where e.points > -1 
				group by e.userid) f on f.userid = c.userid
			where c.points > -1 and d.winner > -1 and date_format(matchtime,%s)=%s
			group by  vdisplay_name,date_format(matchtime,%s)
			order by sum(c.points) desc;",
			'%Y%m',
			$cs_users,
			$wp_users,
			$cs_tipp,
			$cs_match,
			$cs_tipp,
			'%Y%m',
			$newday,
			'%Y%m'
		)
	);
	// @codingStandardsIgnoreEnd

	$out = '';
	if ( empty( $r1 ) ) {
		$out .= __( 'No results found.', 'wp-championship' );
	} else {
		$out .= '<p>&nbsp;</p>';
		$out .= "<table border='1' ><tr><th>" . __( 'Player', 'wp-championship' ) . '</th><th>' . __( 'Total points', 'wp-championship' ) . "</th>\n";
		$out .= '<th>' . __( 'Number of tips', 'wp-championship' ) . '</th><th>' . __( 'Points (month)', 'wp-championship' ) . "</th></tr>\n";

		foreach ( $r1 as $r ) {
			$out .= '<tr><td>' . $r->vdisplay_name . "</td><td style='text-align:right'>" . ( null == $r->gp ? 0 : $r->gp ) . "</td>\n";
			$out .= "<td style='text-align:right'>" . $r->nt . "</td><td style='text-align:right'>" . ( null == $r->mp ? 0 : $r->mp ) . "</td></tr>\n";
		}
		$out .= "</table>\n";
	}

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_update_stats7', 'update_stats7' );

