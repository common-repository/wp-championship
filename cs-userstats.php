<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2008-2020  Hans Matzen  (email : webmaster at tuxlog.de)
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

// Check if the file is called directly.
$phpself = ( isset( $_SERVER['PHP_SELF'] ) ? sanitize_file_name( wp_unslash( $_SERVER['PHP_SELF'] ) ) : '' );
if ( preg_match( '#' . basename( __FILE__ ) . '#', $phpself ) ) {
	die( 'You are not allowed to call this page directly.' );
}


// -----------------------------------------------------------------------------------
// Funktion zur Ausgabe der User Statistikseite.
// -----------------------------------------------------------------------------------
if ( ! function_exists( 'show_user_stats' ) ) { // make it pluggable.
	/**
	 * Function to show user-statistics.
	 *
	 * @param array $atts Parameters for the user-statistics.
	 */
	function show_user_stats( $atts ) {
		include 'globals.php';
		global $wpdb,$wpcs_demo;

		// initialisiere ausgabe variable.
		$out = '';

		// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus
		// und beende die funktion.
		if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
			$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
			$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
			return $out;
		}

		// parameter holen dabei übersteuert tippgruppe, tippgroup.
		$tippgroup = ( isset( $atts['tippgroup'] ) ? $atts['tippgroup'] : '' );
		$tippgroup = ( isset( $atts['tippgruppe'] ) ? $atts['tippgruppe'] : '' );

		// javascript für floating link ausgeben.
		$cs_floating_link = get_option( 'cs_floating_link' );
		if ( $cs_floating_link > 0 ) {
			$out .= cs_get_float_js();
		}

		// lese anwenderdaten ein.
		$userdata = wp_get_current_user();
		// merke die userid.
		$uid = $userdata->ID;

		// userdaten lesen.
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r0 = $wpdb->get_results( $wpdb->prepare( 'select * from %i where userid=%d;', $cs_users, $uid ) );

		// admin flag setzen.
		$is_admin = false;
		if ( 1 == $r0[0]->admin ) {
			$is_admin = true;
		}

		// ermittle aktuelle uhrzeit.
		$currtime = current_time( 'mysql' );

		// begruessung ausgeben.
		$out .= __( 'Welcome ', 'wp-championship' ) . $userdata->display_name . ',<br />';
		$out .= __( 'this page displays the current scores of the tournament and of the guessing game.', 'wp-championship' ) . '<br /></p>';

		//
		// ausgabe des floating nach oben links.
		//
		if ( $cs_floating_link > 0 ) {
			$out .= '<div id="WPCSfloatMenu" ><ul class="menu1"><li><a href="#" onclick="window.scrollTo(0,0); return false;"> Nach oben </a></li></ul></div>';
		}

		//
		// lese alternative bezeichnungen.
		//
		$cs_label_group       = get_option( 'cs_label_group' );
		$cs_col_group         = get_option( 'cs_col_group' );
		$cs_label_icon1       = get_option( 'cs_label_icon1' );
		$cs_col_icon1         = get_option( 'cs_col_icon1' );
		$cs_label_match       = get_option( 'cs_label_match' );
		$cs_col_match         = get_option( 'cs_col_match' );
		$cs_label_icon2       = get_option( 'cs_label_icon2' );
		$cs_col_icon2         = get_option( 'cs_col_icon2' );
		$cs_label_location    = get_option( 'cs_label_location' );
		$cs_col_location      = get_option( 'cs_col_location' );
		$cs_label_time        = get_option( 'cs_label_time' );
		$cs_col_time          = get_option( 'cs_col_time' );
		$cs_label_tip         = get_option( 'cs_label_tip' );
		$cs_col_tip           = get_option( 'cs_col_tip' );
		$cs_label_points      = get_option( 'cs_label_points' );
		$cs_col_points        = get_option( 'cs_col_points' );
		$cs_label_place       = get_option( 'cs_label_place', __( 'Rank', 'wp-championship' ) );
		$cs_col_place         = get_option( 'cs_col_place' );
		$cs_label_player      = get_option( 'cs_label_player', __( 'Player', 'wp-championship' ) );
		$cs_col_player        = get_option( 'cs_col_player' );
		$cs_label_upoints     = get_option( 'cs_label_upoints', __( 'Score', 'wp-championship' ) );
		$cs_col_upoints       = get_option( 'cs_col_upoints' );
		$cs_label_trend       = get_option( 'cs_label_trend', __( 'Tendency', 'wp-championship' ) );
		$cs_col_trend         = get_option( 'cs_col_trend' );
		$cs_label_steam       = get_option( 'cs_label_steam', __( 'Team', 'wp-championship' ) );
		$cs_col_steam         = get_option( 'cs_col_steam' );
		$cs_label_smatch      = get_option( 'cs_label_smatch', __( 'Matches', 'wp-championship' ) );
		$cs_col_smatch        = get_option( 'cs_col_smatch' );
		$cs_label_swin        = get_option( 'cs_label_swin', __( 'Wins', 'wp-championship' ) );
		$cs_col_swin          = get_option( 'cs_col_swin' );
		$cs_label_stie        = get_option( 'cs_label_stie', __( 'Draw', 'wp-championship' ) );
		$cs_col_stie          = get_option( 'cs_col_stie' );
		$cs_label_sloose      = get_option( 'cs_label_sloose', __( 'Defeat', 'wp-championship' ) );
		$cs_col_sloose        = get_option( 'cs_col_sloose' );
		$cs_label_sgoal       = get_option( 'cs_label_sgoal', __( 'goals', 'wp-championship' ) );
		$cs_col_sgoal         = get_option( 'cs_col_sgoal' );
		$cs_label_spoint      = get_option( 'cs_label_spoint', __( 'Points', 'wp-championship' ) );
		$cs_col_spoint        = get_option( 'cs_col_spoint' );
		$cs_tipp_sort         = get_option( 'cs_tipp_sort' );
		$cs_label_championtip = get_option( 'cs_label_championtip', __( 'Champion-Tip', 'wp-championship' ) );
		$cs_col_championtip   = get_option( 'cs_col_championtip' );

		if ( '' == $cs_label_place ) {
			$cs_label_place = __( 'Rank', 'wp-championship' );
		}
		if ( '' == $cs_label_player ) {
			$cs_label_player = __( 'Player', 'wp-championship' );
		}
		if ( '' == $cs_label_upoints ) {
			$cs_label_upoints = __( 'Score', 'wp-championship' );
		}
		if ( '' == $cs_label_trend ) {
			$cs_label_trend = __( 'Tendency', 'wp-championship' );
		}
		if ( '' == $cs_label_steam ) {
			$cs_label_steam = __( 'Team', 'wp-championship' );
		}
		if ( '' == $cs_label_smatch ) {
			$cs_label_smatch = __( 'Matches', 'wp-championship' );
		}
		if ( '' == $cs_label_swin ) {
			$cs_label_swin = __( 'Wins', 'wp-championship' );
		}
		if ( '' == $cs_label_stie ) {
			$cs_label_stie = __( 'Draw', 'wp-championship' );
		}
		if ( '' == $cs_label_sloose ) {
			$cs_label_sloose = __( 'Defeat', 'wp-championship' );
		}
		if ( '' == $cs_label_sgoal ) {
			 $cs_label_sgoal = __( 'goals', 'wp-championship' );
		}
		if ( '' == $cs_label_spoint ) {
			 $cs_label_spoint = __( 'Points', 'wp-championship' );
		}
		if ( '' == $cs_label_championtip ) {
			 $cs_label_championtip = __( 'Champion-Tip', 'wp-championship' );
		}

		// ausgabe der optionen und der tipptabelle.
		// -------------------------------------------------------------------.

		// anzeigen wenn der user admin des tippspiels ist.
		if ( $is_admin ) {
			$out .= '<b>' . __( 'You are a guessing game admin.', 'wp-championship' ) . '</b><br />';
		}

		// anzeigen der gewinnermannschaft falls tunier schon beendet.
		$cswinner = cs_get_cswinner();
		if ( $cswinner ) {
			$out .= '<hr>' . __( 'The championship winner is:', 'wp-championship' ) . "<b>$cswinner</b><hr>";
		}

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking( $tippgroup );

		// hat das tunier schon begonnen.
		$csstarted = true;
		$blog_now  = current_time( 'mysql', 0 );

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$mr = $wpdb->get_row( $wpdb->prepare( 'select min(matchtime) as mintime from %i;', $cs_match ) );

		if ( $blog_now <= $mr->mintime ) {
			$csstarted = false;
		}

		$out .= '<h2>' . __( 'Current score', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class='tablesorter'><tr>\n";
		if ( ! $cs_col_place ) {
			$out .= '<th scope="col" style="padding: 0;text-align: center">' . $cs_label_place . '</th>' . "\n";
		}
		if ( ! $cs_col_player ) {
			$out .= '<th scope="col" style="padding:0;text-align: center">' . $cs_label_player . '</th>' . "\n";
		}
		if ( ! $cs_col_upoints ) {
			$out .= '<th scope="col" style="padding:0;text-align: center">' . $cs_label_upoints . '</th>';
		}
		if ( get_option( 'cs_rank_trend' ) ) {
			$out .= '<th scope="col" style="padding:0;text-align: center">' . $cs_label_trend . '</th>';
		}
		if ( $csstarted && ! $cs_col_championtip ) {
			$out .= '<th scope="col" style="padding:0;text-align: center">' . $cs_label_championtip . '</th>';
		}
		$out .= "</tr>\n";

		$pointsbefore = -1;
		$i            = 0;
		$j            = 1;
		foreach ( $rank as $row ) {
			/*
			 * Show user clear name in ranking table
			 */

			// shall we show fullnames in ranking?get sort criteria.
			if ( get_option( 'cs_stats_show_fullnames' ) ) {
                // @codingStandardsIgnoreStart
				$uq = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT user.id as id, meta.meta_value as lname, meta2.meta_value as fname FROM %i as user
						LEFT JOIN %i as meta
						on meta.user_id = user.id and  meta.meta_key = 'last_name'
						LEFT JOIN %i as meta2
						on meta2.user_id = user.id and meta2.meta_key = 'first_name'
						where user.id = %d",
						$wpdb->prefix . 'users',
						$wpdb->prefix . 'usermeta',
						$wpdb->prefix . 'usermeta',
						$row->userid
					)
				);
                // @codingStandardsIgnoreEnd
			}

			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i = $i + $j;
				$j = 1;
			} else {
				++$j;
			}

			if ( $i < $row->oldrank || -1 == $row->oldrank ) {
				$trend = '&uArr;';
			} elseif ( $i > $row->oldrank ) {
				$trend = '&dArr;';
			} else {
				$trend = '&rArr;';
			}

			$out .= '<tr>';

			if ( ! $cs_col_place ) {
				$out .= "<td style='text-align:center'>$i</td>";
			}
			if ( ! $cs_col_player ) {
				// shall we show fullnames inranking?
				if ( get_option( 'cs_stats_show_fullnames' ) ) {
					$out .= "<td style='text-align:center'>" . $row->vdisplay_name . ' (' . $uq->fname . ' ' . $uq->lname . ')</td>';
				} else {
					$out .= "<td style='text-align:center'>" . $row->vdisplay_name . '</td>';
				}
			}
			if ( ! $cs_col_upoints ) {
				$out .= "<td style='text-align:center'>" . $row->points . '</td>';
			}

			if ( get_option( 'cs_rank_trend' ) ) {
				$out .= "<td style='text-align:center'>$trend</td>";
			}

			if ( $csstarted && ! $cs_col_championtip ) {
				$out .= "<td style='text-align:center'>" . ( '' == $row->teamname ? '-' : $row->teamname ) . '</td>';
			}
			$out .= '</tr>';

			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}
		$out .= '</table>' . "<p>&nbsp;</p>\n";

		// Übersicht Strafpunkte ausgeben.
		$penalties = cs_get_team_penalty();
		if ( is_array( $penalties ) && count( $penalties ) > 0 ) {
			$out .= "<div id='cs_stattab_p'>";
			$out .= '<h2>' . __( 'Penalty', 'wp-championship' ) . "</h2>\n";

			foreach ( $penalties as $k => $val ) {
				$out .= $val->name . ':   ' . $val->penalty . " $cs_label_spoint<br/>";
			}
			$out .= '<br/></div>';
		}

		// Spielübersicht Vorrunde.
		if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
			$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
		} else {
			$iconpath = plugins_url( 'icons/', __FILE__ );
		}

		// tabellen loop.
		// hole tabellen daten.
		$results = cs_get_team_clification();

		$groupid_old = '';

		$out .= "<h2 id='cs_sh_v' class='cs_stathead' onclick=\"jQuery(document).ready( function() {jQuery('#cs_stattab_v').toggle('slow') } );\">" . __( 'Preliminary', 'wp-championship' ) . "</h2>\n";
		// $out .= "<script type='text/javascript'>jQuery('#cs_sh_v').toggle( function () { jQuery(this).addClass('divclose'); }, function () { jQuery(this).removeClass('divclose');});</script>";

		$out .= "<div id='cs_stattab_v'>";

		foreach ( $results as $res ) {

			// bei gruppenwechsel footer / header ausgeben.
			if ( $res->groupid != $groupid_old ) {
				if ( '' != $groupid_old ) {
					$out .= '</table><p>&nbsp;</p>';
				}

				$out .= "<h2 id='cs_sh_$res->groupid' class='cs_grouphead' onclick=\"jQuery(document).ready( function() {jQuery('#cs_stattab_$res->groupid').toggle('slow'   , function() { if ( jQuery('#cs_stattab_$res->groupid').css('display') == 'block') jQuery('#cs_stattab_$res->groupid').css('display','table');}    ); } );\">" . __( 'Group', 'wp-championship' ) . ' ' . $res->groupid . "</h2>\n";

				// $out .= "<script type='text/javascript'>jQuery('#cs_sh_$res->groupid').toggle( function () { jQuery(this).addClass('divclose'); }, function () { jQuery(this).removeClass('divclose');});</script>";
				$out .= "<table id='cs_stattab_$res->groupid' class='tablesorter' ><thead><tr>\n";
				if ( ! $cs_col_steam ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_steam . '</th>' . "\n";
				}
				if ( ! $cs_col_smatch ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_smatch . '</th>' . "\n";
				}
				if ( ! $cs_col_swin ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_swin . '</th>' . "\n";
				}
				if ( ! $cs_col_stie ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_stie . '</th>' . "\n";
				}
				if ( ! $cs_col_sloose ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_sloose . '</th>' . "\n";
				}
				if ( ! $cs_col_sgoal ) {
					$out .= '<th style="padding:0; text-align: center">' . $cs_label_sgoal . '</th>' . "\n";
				}
				if ( ! $cs_col_spoint ) {
					$out .= '<th style="padding:0; text-align:center">' . $cs_label_spoint . '</th></tr>';
				}
				$out .= '</thead>' . "\n";
			}

			// hole statistiken des teams.
			$stats = array();
			$stats = cs_get_team_stats( $res->tid );

			// zeile ausgeben.
			$out .= '<tr>';
			if ( ! $cs_col_steam ) {
				if ( substr( $res->icon, 0, 4 ) == 'http' ) {
					$iconpath = '';
				}
				$out .= "<td><img class='csicon' alt='icon1' width='20' src='" . $iconpath . $res->icon . "' />";
				$out .= $res->name . '</td>';
			}
			if ( ! $cs_col_smatch ) {
				$out .= "<td style='text-align:center'>" . $stats['spiele'] . '</td>';
			}
			if ( ! $cs_col_swin ) {
				$out .= "<td style='text-align:center'>" . $stats['siege'] . '</td>';
			}
			if ( ! $cs_col_stie ) {
				$out .= "<td style='text-align:center'>" . $stats['unentschieden'] . '</td>';
			}
			if ( ! $cs_col_sloose ) {
				$out .= "<td style='text-align:center'>" . $stats['niederlagen'] . '</td>';
			}
			if ( ! $cs_col_sgoal ) {
				$out .= "<td style='text-align:center'> " . $res->store . ' : ' . $res->sgegentore . ' </td>';
			}
			if ( ! $cs_col_spoint ) {
				$out .= "<td style='text-align:center'>" . $res->spoints . ' </td>';
			}
			$out .= "</tr>\n";

			// gruppenwechsel versorgen.
			$groupid_old = $res->groupid;
		}
		$out .= "</table><p>&nbsp;</p></div>\n";

		// Finalrunde ausgeben.
		// @codingStandardsIgnoreStart
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select a.mid as mid, b.icon as icon1, b.name as name1, c.icon as icon2, c.name as name2, a.result1 as result1,
				a.result2 as result2, a.location as location, date_format(a.matchtime,%s) as matchtime,
				a.matchtime as origtime, a.matchtime as matchts
				from %i a inner join %i b on a.tid1=b.tid
				inner join %i c on a.tid2=c.tid
				where a.round='F'
				order by origtime;",
				'%d.%m<br />%H:%i',
				$cs_match,
				$cs_team,
				$cs_team
			)
		);
		// @codingStandardsIgnoreEnd

		// tabellen kopf ausgeben.
		if ( ! empty( $results ) ) {
			$out .= "<h2 id='cs_sh_z' class='cs_stathead' onclick=\"jQuery(document).ready( function() {jQuery('#cs_stattab_z').toggle('slow', function() { if ( jQuery('#cs_stattab_z').css('display') == 'block') jQuery('#cs_stattab_z').css('display','table');}    ); } );\">" . __( 'Finals', 'wp-championship' ) . "</h2>\n";
			// $out .= "<script type='text/javascript'>jQuery('#cs_sh_z').toggle( function () { jQuery(this).addClass('divclose'); }, function () { jQuery(this).removeClass('divclose');});</script>";

			$out .= "<table id='cs_stattab_z' class='tablesorter'><thead><tr>\n";
			$out .= '<th style="width:20">' . __( 'Matchno.', 'wp-championship' ) . '</th>' . "\n";
			$out .= '<th>&nbsp;</th>';
			$out .= '<th scope="col" style="text-align: center">' . __( 'match', 'wp-championship' ) . '</th>' . "\n";
			$out .= '<th>&nbsp;</th>';
			$out .= '<th scope="col" style="text-align: center">' . __( 'Location', 'wp-championship' ) . '</th>' . "\n";
			$out .= '<th scope="col" style="text-align: center">' . __( 'Date<br />Time' ) . '</th>' . "\n";
			$out .= '<th style="text-align:center">' . __( 'Result', 'wp-championship' ) . '</th>';
			$out .= '</tr></thead>' . "\n";
		}

		foreach ( $results as $res ) {
			// zeile ausgeben.
			$out .= '<tr>';
			$out .= "<td style='text-align:center'>" . $res->mid . '</td>';
			if ( '' != $res->icon1 ) {
				$out .= "<td><img class='csicon' alt='icon1' width='15' src='" . $iconpath . $res->icon1 . "' /></td>";
			} else {
				$out .= '<td>&nbsp;</td>';
			}
			$out .= "<td style='text-align:center'>" . cs_team2text( $res->name1 ) . ' - ' . cs_team2text( $res->name2 ) . '</td>';
			if ( '' != $res->icon2 ) {
				$out .= "<td><img class='csicon' alt='icon2' width='15' src='" . $iconpath . $res->icon2 . "' /></td>";
			} else {
				$out .= '<td>&nbsp;</td>';
			}
			$out .= "<td style='text-align:center'>" . $res->location . '</td>';
			$out .= "<td style='text-align:center'>" . $res->matchtime . '</td>';
			$out .= "<td style='text-align:center'>";
			$out .= ( -1 == $res->result1 ? '-' : $res->result1 ) . ' : ' . ( -1 == $res->result2 ? '-' : $res->result2 ) . '</td>';
			$out .= "</tr>\n";

		}
		if ( ! empty( $results ) ) {
			$out .= "</table>\n<p>&nbsp;";
		}

		return $out;
	}
}//make it pluggable.


