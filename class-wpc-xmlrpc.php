<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2011-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

// Load includes.
require_once ABSPATH . 'wp-includes/class-IXR.php';
require_once ABSPATH . 'wp-includes/class-wp-xmlrpc-server.php';
if ( file_exists( get_stylesheet_directory() . '/wp-championship/cs-stats.php' ) ) {
	require_once get_stylesheet_directory() . '/wp-championship/cs-stats.php';
} else {
	require_once 'cs-stats.php';
}


/**
 * Class to extend WordPress xmlrpc interface with wp-championship specific methods
 */
class WPC_XMLRPC extends wp_xmlrpc_server {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		// define xml methods.
		$methods = array(
			'wpc.getStats'       => 'this:wpc_get_Stats',
			'wpc.getTop'         => 'this:wpc_get_Top',
			'wpc.getRank'        => 'this:wpc_get_Rank',
			'wpc.getNews'        => 'this:wpc_get_News',
			'wpc.getStatsParams' => 'this:wpc_get_Stats_Params',
		);

			$this->methods = array_merge( $this->methods, $methods );
	}

	/**
	 * Get class name.
	 */
	public static function wpc_getName() {
		return __CLASS__;
	}

	/**
	 * Create html header.
	 */
	protected function html_header() {
		$def        = 'xwp-championship-default.css';
		$user       = 'xwp-championship.css';
		$plugin_url = plugins_url( '/', __FILE__ );

		if ( file_exists( plugin_dir_path( __FILE__ ) . $user ) ) {
			$def = $user;
		}

		// das css wird in den header mit rein geschrieben, denn da wir über xmlrpc
		// aufgerufen werden kann der browser realtive urls nicht auflösen.
		if ( file_exists( get_stylesheet_directory() . '/wp-championship/xwp-championship.css' ) ) {
			$csstext = file_get_contents( get_stylesheet_directory() . '/wp-championship/xwp-championship.css' );
		} else {
			$csstext = file_get_contents( plugin_dir_path( __FILE__ ) . $def );
		}

		// jetzt ersetzen wir noch die url(..) image angaben mit der absoluten url.
		$pattern = '/url\((.*)\)/i';
		$matches = array();
		preg_match( $pattern, $csstext, $matches );

		$csstext = str_replace( $matches[0], 'url(' . $plugin_url . $matches[1] . ')', $csstext );

		$head = <<<EOL
		<html>
		<head>
		  <title>wp-championship</title>
		  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
                  <meta name="viewport" content="width=device-width" />
		  <style type="text/css">
		  $csstext
		  </style>		 
		</head>
		<body class="xwpc_body">
		
EOL;
		// nur fürs testen im loalen netz.
		$head = str_replace( 'host1', '192.168.2.11', $head );

		return $head;
	}

	/**
	 * Create HTML footer.
	 */
	protected function html_footer() {
		$foot = '</body></html>';
		return $foot;
	}

	/**
	 * Login user
	 *
	 * @param array $args credentials for login.
	 */
	public function wpc_login( $args ) {
		$blog_id  = (int) $args[0];
		$username = $args[1];
		$password = $args[2];

		$user = $this->login( $username, $password );
		if ( ! $user ) {
			return $this->error;
		} else {
			return;
		}
	}

	/**
	 * Endpoint for getting statistics.
	 *
	 * @param array $args credentials for login and statistics.
	 */
	public function wpc_get_Stats( $args ) {
		$blog_id    = (int) $args[0];
		$username   = $args[1];
		$password   = $args[2];
		$whichstats = $args[4];
		$param      = $args[5];

		$user = $this->login( $username, $password );
		if ( ! $user ) {
			return $this->error;
		}

		$erg = '';
		switch ( $whichstats ) {
			case 0:
				$erg = __( 'This statistic does not exist.', 'wp-championship' );
				// $erg = show_UserStats();.
				break;
			case 1:
				$erg = $this->show_UserStats1( $param );
				break;
			case 2:
				$erg = show_stats2();
				break;
			case 3:
				$erg = show_stats3();
				break;
			case 4:
				$erg = $this->show_UserStats4( $param );
				break;
			case 5:
				$erg = $this->show_UserStats5( $param );
				break;
			case 6:
				$erg = $this->wpc_get_top( $param );
				break;
			case 7:
				$erg = $this->wpc_get_Rank( $param );
				break;
			default:
				$erg = __( 'This statistic does not exist.', 'wp-championship' );
				break;
		}
		return $this->html_header() . $erg . $this->html_footer();
	}

	/**
	 * Methode zum Abfragen der möglichen Parameter für eine Statistik.
	 *
	 * @param array $args credentials for login and statistics.
	 */
	public function wpc_get_Stats_Params( $args ) {

		include 'globals.php';
		global $wpdb;

		$blog_id    = (int) $args[0];
		$username   = $args[1];
		$password   = $args[2];
		$whichstats = $args[4];

		$user = $this->login( $username, $password );
		if ( ! $user ) {
			return $this->error;
		}

		$erg = '';
		switch ( $whichstats ) {
			case 0:
				$erg = '';
				break;
			case 1:
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
					$erg .= $r->sday . ',';
				}
				break;
			case 2:
				$erg = '';
				break;
			case 3:
				$erg = '';
				break;
			case 4:
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r1 = $wpdb->get_results( $wpdb->prepare( 'SELECT user_nicename FROM %i inner join %i on ID=userid order by user_nicename;', $cs_users, $wp_users ) );

				if ( get_option( 'cs_xmlrpc_alltipps' ) > 0 ) {
					$erg .= __( 'All', 'wp-championship' ) . ',';
				}
				foreach ( $r1 as $r ) {
					if ( get_option( 'cs_xmlrpc_alltipps' ) > 0 || $username == $r->user_nicename ) {
						$erg .= $r->user_nicename . ',';
					}
				}
				break;
			case 5:
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
					$erg .= $r->sday . ',';
				}

				break;
			case 6:
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r1  = $wpdb->get_row( $wpdb->prepare( 'SELECT count(*) as c FROM %i;', $cs_users ) );
				$erg = '';
				for ( $i = 1; $i <= $r1->c; $i++ ) {
					$erg .= $i . ',';
				}
				break;
			case 7:
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r1  = $wpdb->get_row( $wpdb->prepare( 'SELECT count(*) as c FROM %i;', $cs_team ) );
				$erg = '';
				for ( $i = 1; $i <= $r1->c; $i++ ) {
					$erg .= $i . ',';
				}
				break;
			default:
				$erg = 'Diese Statistik gibt es nicht.';
				break;
		}
		return $erg;
	}

	/**
	 * Endpoint for getting news.
	 */
	protected function wpc_get_News() {
		$out  = '';
		$text = get_option( 'cs_xmlrpc_news' );

		return trim( $text );
	}

	/**
	 * Get current standings.
	 *
	 * @param array $count number fo lines of the current standing.
	 */
	protected function wpc_get_Top( $count ) {
		include 'globals.php';
		global $wpdb;

		//
		// lese alternative bezeichnungen.
		//
		$csx_label_group    = get_option( 'csx_label_group' );
		$csx_col_group      = get_option( 'csx_col_group' );
		$csx_label_icon1    = get_option( 'csx_label_icon1' );
		$csx_col_icon1      = get_option( 'csx_col_icon1' );
		$csx_label_match    = get_option( 'csx_label_match' );
		$csx_col_match      = get_option( 'csx_col_match' );
		$csx_label_icon2    = get_option( 'csx_label_icon2' );
		$csx_col_icon2      = get_option( 'csx_col_icon2' );
		$csx_label_location = get_option( 'csx_label_location' );
		$csx_col_location   = get_option( 'csx_col_location' );
		$csx_label_time     = get_option( 'csx_label_time' );
		$csx_col_time       = get_option( 'csx_col_time' );
		$csx_label_tip      = get_option( 'csx_label_tip' );
		$csx_col_tip        = get_option( 'csx_col_tip' );
		$csx_label_points   = get_option( 'csx_label_points' );
		$csx_col_points     = get_option( 'csx_col_points' );
		$csx_label_place    = get_option( 'csx_label_place', __( 'Rank', 'wp-championship' ) );
		$csx_col_place      = get_option( 'csx_col_place' );
		$csx_label_player   = get_option( 'csx_label_player', __( 'Player', 'wp-championship' ) );
		$csx_col_player     = get_option( 'csx_col_player' );
		$csx_label_upoints  = get_option( 'csx_label_upoints', __( 'Score', 'wp-championship' ) );
		$csx_col_upoints    = get_option( 'csx_col_upoints' );
		$csx_label_trend    = get_option( 'csx_label_trend', __( 'Tendency', 'wp-championship' ) );
		$csx_col_trend      = get_option( 'csx_col_trend' );
		$csx_label_steam    = get_option( 'csx_label_steam', __( 'Team', 'wp-championship' ) );
		$csx_col_steam      = get_option( 'csx_col_steam' );
		$csx_label_smatch   = get_option( 'csx_label_smatch', __( 'Matches', 'wp-championship' ) );
		$csx_col_smatch     = get_option( 'csx_col_smatch' );
		$csx_label_swin     = get_option( 'csx_label_swin', __( 'Wins', 'wp-championship' ) );
		$csx_col_swin       = get_option( 'csx_col_swin' );
		$csx_label_stie     = get_option( 'csx_label_stie', __( 'Draw', 'wp-championship' ) );
		$csx_col_stie       = get_option( 'csx_col_stie' );
		$csx_label_sloose   = get_option( 'csx_label_sloose', __( 'Defeat', 'wp-championship' ) );
		$csx_col_sloose     = get_option( 'csx_col_sloose' );
		$csx_label_sgoal    = get_option( 'csx_label_sgoal', __( 'goals', 'wp-championship' ) );
		$csx_col_sgoal      = get_option( 'csx_col_sgoal' );
		$csx_label_spoint   = get_option( 'csx_label_spoint', __( 'Points', 'wp-championship' ) );
		$csx_col_spoint     = get_option( 'csx_col_spoint' );
		$csx_tipp_sort      = get_option( 'csx_tipp_sort' );

		$out = '';
		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking();
		// $out .= "<div><h3 class='xwpc_head'>".__("Current score","wp-championship")."</h3>\n";.
		$out .= "<table class='xwpc_table'><tr>\n";
		if ( ! $csx_col_place ) {
			$out .= '<th class="xwpc_th">' . $csx_label_place . '</th>' . "\n";
		}
		if ( ! $csx_col_player ) {
			$out .= '<th  class="xwpc_th">' . $csx_label_player . '</th>' . "\n";
		}
		if ( ! $csx_col_upoints ) {
			$out .= '<th  class="xwpc_th">' . $csx_label_upoints . '</th>';
		}
		if ( get_option( 'cs_rank_trend' ) ) {
			$out .= '<th  class="xwpc_th">' . $csx_label_trend . '</th>';
		}
		$out .= "</tr>\n";

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

			if ( $i < $row->oldrank ) {
				$trend = '&uArr;';
			} elseif ( $i > $row->oldrank ) {
				$trend = '&dArr;';
			} else {
				$trend = '&rArr;';
			}

			$out .= '<tr>';

			if ( ! $csx_col_place ) {
				$out .= "<td  class='xwpc_td' align='center'>$i</td>";
			}
			if ( ! $csx_col_player ) {
				$out .= "<td  class='xwpc_td' align='center'>" . $row->user_nicename . '</td>';
			}
			if ( ! $csx_col_upoints ) {
				$out .= "<td  class='xwpc_td' align='center'>" . $row->points . '</td>';
			}

			if ( get_option( 'cs_rank_trend' ) ) {
				$out .= "<td class='xwpc_td' align='center'>$trend</td>";
			}
			$out .= '</tr>';

			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}
		$out .= '</table>' . "\n";

		return $out;
	}


	/**
	 * Get current rank.
	 *
	 * @param array $count number of lines of the current rank.
	 */
	protected function wpc_get_Rank( $count ) {

		include 'globals.php';
		global $wpdb;

		$out = '';
		//
		// lese alternative bezeichnungen.
		//
		$csx_label_group    = get_option( 'csx_label_group' );
		$csx_col_group      = get_option( 'csx_col_group' );
		$csx_label_icon1    = get_option( 'csx_label_icon1' );
		$csx_col_icon1      = get_option( 'csx_col_icon1' );
		$csx_label_match    = get_option( 'csx_label_match' );
		$csx_col_match      = get_option( 'csx_col_match' );
		$csx_label_icon2    = get_option( 'csx_label_icon2' );
		$csx_col_icon2      = get_option( 'csx_col_icon2' );
		$csx_label_location = get_option( 'csx_label_location' );
		$csx_col_location   = get_option( 'csx_col_location' );
		$csx_label_time     = get_option( 'csx_label_time' );
		$csx_col_time       = get_option( 'csx_col_time' );
		$csx_label_tip      = get_option( 'csx_label_tip' );
		$csx_col_tip        = get_option( 'csx_col_tip' );
		$csx_label_points   = get_option( 'csx_label_points' );
		$csx_col_points     = get_option( 'csx_col_points' );
		$csx_label_place    = get_option( 'csx_label_place', __( 'Rank', 'wp-championship' ) );
		$csx_col_place      = get_option( 'csx_col_place' );
		$csx_label_player   = get_option( 'csx_label_player', __( 'Player', 'wp-championship' ) );
		$csx_col_player     = get_option( 'csx_col_player' );
		$csx_label_upoints  = get_option( 'csx_label_upoints', __( 'Score', 'wp-championship' ) );
		$csx_col_upoints    = get_option( 'csx_col_upoints' );
		$csx_label_trend    = get_option( 'csx_label_trend', __( 'Tendency', 'wp-championship' ) );
		$csx_col_trend      = get_option( 'csx_col_trend' );
		$csx_label_steam    = get_option( 'csx_label_steam', __( 'Team', 'wp-championship' ) );
		$csx_col_steam      = get_option( 'csx_col_steam' );
		$csx_label_smatch   = get_option( 'csx_label_smatch', __( 'Matches', 'wp-championship' ) );
		$csx_col_smatch     = get_option( 'csx_col_smatch' );
		$csx_label_swin     = get_option( 'csx_label_swin', __( 'Wins', 'wp-championship' ) );
		$csx_col_swin       = get_option( 'csx_col_swin' );
		$csx_label_stie     = get_option( 'csx_label_stie', __( 'Draw', 'wp-championship' ) );
		$csx_col_stie       = get_option( 'csx_col_stie' );
		$csx_label_sloose   = get_option( 'csx_label_sloose', __( 'Defeat', 'wp-championship' ) );
		$csx_col_sloose     = get_option( 'csx_col_sloose' );
		$csx_label_sgoal    = get_option( 'csx_label_sgoal', __( 'goals', 'wp-championship' ) );
		$csx_col_sgoal      = get_option( 'csx_col_sgoal' );
		$csx_label_spoint   = get_option( 'csx_label_spoint', __( 'Points', 'wp-championship' ) );
		$csx_col_spoint     = get_option( 'csx_col_spoint' );
		$csx_tipp_sort      = get_option( 'csx_tipp_sort' );

		// Spielübersicht Vorrunde.
		if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
			$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
		} else {
			$iconpath = plugins_url( 'icons/', __FILE__ );
		}

		// tabellen loop
		// hole tabellen daten.
		$results = cs_get_team_clification();

		$groupid_old = '';

		if ( 'Vorrunde' == $count || 'Beide' == $count ) {
			$out .= "<h3  class='xwpc_head'>" . __( 'Preliminary', 'wp-championship' ) . "</h3>\n";
			$out .= "<div id='cs_stattab_v'>";

			foreach ( $results as $res ) {

				// bei gruppenwechsel footer / header ausgeben.
				if ( $res->groupid != $groupid_old ) {
					if ( '' != $groupid_old ) {
						$out .= '</table><p>&nbsp;</p>';
					}

					$out .= "<h4  class='xwpc_head'>" . __( 'Group', 'wp-championship' ) . ' ' . $res->groupid . "</h4>\n";
					$out .= "<table class='xwpc_table'><thead><tr>\n";
					if ( ! $csx_col_steam ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_steam . '</th>' . "\n";
					}
					if ( ! $csx_col_smatch ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_smatch . '</th>' . "\n";
					}
					if ( ! $csx_col_swin ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_swin . '</th>' . "\n";
					}
					if ( ! $csx_col_stie ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_stie . '</th>' . "\n";
					}
					if ( ! $csx_col_sloose ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_sloose . '</th>' . "\n";
					}
					if ( ! $csx_col_sgoal ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_sgoal . '</th>' . "\n";
					}
					if ( ! $csx_col_spoint ) {
						$out .= '<th  class="xwpc_th">' . $csx_label_spoint . '</th></tr>';
					}
					$out .= '</thead>' . "\n";
				}

				// hole statistiken des teams.
				$stats = array();
				$stats = cs_get_team_stats( $res->tid );

				// zeile ausgeben.
				$out .= '<tr>';
				if ( ! $csx_col_steam ) {
					if ( substr( $res->icon, 0, 4 ) == 'http' ) {
						$out .= "<td class='xwpc_td'><img class='csicon' alt='icon1' src='" . $res->icon . "' />";
					} else {
						$out .= "<td class='xwpc_td'><img class='csicon' alt='icon1' src='" . $iconpath . $res->icon . "' />";
					}
					$out .= "<font size='-1'>" . $res->name . '</font></td>';
				}
				if ( ! $csx_col_smatch ) {
					$out .= "<td  class='xwpc_td' align=\"center\">" . $stats['spiele'] . '</td>';
				}
				if ( ! $csx_col_swin ) {
					$out .= "<td  class='xwpc_td' align=\"center\">" . $stats['siege'] . '</td>';
				}
				if ( ! $csx_col_stie ) {
					$out .= "<td  class='xwpc_td' align=\"center\">" . $stats['unentschieden'] . '</td>';
				}
				if ( ! $csx_col_sloose ) {
					$out .= "<td  class='xwpc_td' align=\"center\">" . $stats['niederlagen'] . '</td>';
				}
				if ( ! $csx_col_sgoal ) {
					$out .= "<td  class='xwpc_td' align=\"center\"> " . $res->store . ' : ' . $res->sgegentore . ' </td>';
				}
				if ( ! $csx_col_spoint ) {
					$out .= "<td  class='xwpc_td' align='center'>" . $res->spoints . ' </td>';
				}
				$out .= "</tr>\n";

				// gruppenwechsel versorgen.
				$groupid_old = $res->groupid;
			}
			$out .= "</table><p>&nbsp;</p></div>\n";
		}

		if ( 'Finalrunde' == $count || 'Beide' == $count ) {
			// Finalrunde ausgeben.
			// @codingStandardsIgnoreStart
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid, b.icon as icon1, b.name as name1, c.icon as icon2, c.name as name2, a.result1 as result1,
					a.result2 as result2, a.location as location, date_format(a.matchtime,%s) as matchtime,
					a.matchtime as origtime, a.matchtime as matchts
					from  %i a inner join %i b on a.tid1=b.tid
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
				$out .= "<h3  class='xwpc_head' >" . __( 'Finals', 'wp-championship' ) . "</h3>\n";

				$out .= "<table class='xwpc_table'><thead><tr>\n";
				$out .= '<th  class="xwpc_th">' . __( 'Matchno.', 'wp-championship' ) . '</th>' . "\n";
				$out .= '<th  class="xwpc_th">&nbsp;</th>';
				$out .= '<th  class="xwpc_th">' . __( 'match', 'wp-championship' ) . '</th>' . "\n";
				$out .= '<th  class="xwpc_th">&nbsp;</th>';
				$out .= '<th  class="xwpc_th">' . __( 'Location', 'wp-championship' ) . '</th>' . "\n";
				$out .= '<th  class="xwpc_th">' . __( 'Date<br />Time' ) . '</th>' . "\n";
				$out .= '<th  class="xwpc_th">' . __( 'Result', 'wp-championship' ) . '</th>';
				$out .= '</tr></thead>' . "\n";
			}

			foreach ( $results as $res ) {
				// zeile ausgeben.
				$out .= '<tr>';
				$out .= "<td class='xwpc_td' align='center'>" . $res->mid . '</td>';
				if ( '' != $res->icon1 ) {
					if ( substr( $res->icon1, 0, 4 ) == 'http' ) {
						$out .= "<td class='xwpc_td' ><img class='csicon' alt='icon1' width='15' src='" . $res->icon1 . "' /></td>";
					} else {
						$out .= "<td class='xwpc_td' ><img class='csicon' alt='icon1' width='15' src='" . $iconpath . $res->icon1 . "' /></td>";
					}
				} else {
					$out .= "<td class='xwpc_td' >&nbsp;</td>";
				}
					$out .= "<td  class='xwpc_td' align='center'><font size='-1'>" . cs_team2text( $res->name1 ) . ' - ' . cs_team2text( $res->name2 ) . '</font></td>';
				if ( '' != $res->icon2 ) {
					if ( substr( $res->icon2, 0, 4 ) == 'http' ) {
						$out .= "<td class='xwpc_td' ><img class='csicon' alt='icon2' width='15' src='" . $res->icon2 . "' /></td>";
					} else {
						$out .= "<td class='xwpc_td' ><img class='csicon' alt='icon2' width='15' src='" . $iconpath . $res->icon2 . "' /></td>";
					}
				} else {
					$out .= "<td class='xwpc_td' >&nbsp;</td>";
				}
					$out .= "<td  class='xwpc_td' align=\"center\"><font size='-1'>" . $res->location . '</font></td>';
					$out .= "<td  class='xwpc_td' align=\"center\">" . $res->matchtime . '</td>';
					$out .= "<td  class='xwpc_td' align='center'>";
					$out .= ( -1 == $res->result1 ? '-' : $res->result1 ) . ' : ' . ( -1 == $res->result2 ? '-' : $res->result2 ) . '</td>';
					$out .= "</tr>\n";

			}
			if ( ! empty( $results ) ) {
				$out .= "</table>\n";
			}
		}

		return $out;

	}

	/**
	 * Show userstats1.
	 *
	 * @param array $parm Parameter for Userstat.
	 */
	protected function show_UserStats1( $parm ) {
		include 'globals.php';
		global $wpdb;

		$out    = '';
		$newday = $parm;

		if ( get_option( 'cs_modus' ) == 1 ) {
			// @codingStandardsIgnoreStart
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT b.user_nicename, sum(e.points) as punkte 
					from %i a inner join %i b on a.userid = b.ID left outer join 
					( select c.* from %i c inner join %i d on c.mid = d.mid where date(d.matchtime) = %s and c.points>0 ) e 
					on e.userid = a.userid 
					order by sum(e.points) desc',
					$cs_users,
					$wp_users,
					$cs_tipp,
					$cs_match,
					$newday
				)
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT b.user_nicename, sum(e.points) as punkte 
					from %i a inner join %i b on a.userid = b.ID left outer join 
					( select c.* from %i c inner join %i d on c.mid = d.mid where spieltag = %s and c.points>0 ) e 
					on e.userid = a.userid 
					group order by sum(e.points) desc',
					$cs_users,
					$wp_users,
					$cs_tipp,
					$cs_match,
					$newday
				)
			);
			// @codingStandardsIgnoreEnd
		}

		$out .= '<p>&nbsp;</p>';
		$out .= "<table class='xwpc_table' ><tr><th class='xwpc_th'>" . __( 'Player', 'wp-championship' ) . "</th><th class='xwpc_th'>" . __( 'Points', 'wp-championship' ) . "</th></tr>\n";

		foreach ( $r1 as $r ) {
			$out .= "<tr><td class='xwpc_td'>" . $r->user_nicename . "</td><td class='xwpc_td' align='right'>" . ( null == $r->punkte ? 0 : $r->punkte ) . "</td></tr>\n";
		}
		$out .= "</table>\n";

		return $out;
	}
	/**
	 * Show userstats4.
	 *
	 * @param array $parm Parameter for Userstat.
	 */
	protected function show_UserStats4( $parm ) {
		include 'globals.php';
		global $wpdb;

		$out      = '';
		$username = $parm;

		if ( 'All' == $username || 'Alle' == $username ) {
			$username = '?';
		}

		if ( get_option( 'cs_modus' ) == 1 ) {
			// @codingStandardsIgnoreStart
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.shortname as steam1, b.icon as icon1, c.name as team2, 
					c.shortname as steam2, c.icon as icon2,a.location as location,date_format(a.matchtime,%s) as matchtime,
					a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
					where a.round in ('V','F') 
					and result1>-1 
					and result2>-1 
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
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.shortname as steam1, b.icon as icon1, c.name as team2, 
					c.shortname as steam2, c.icon as icon2,a.location as location,date_format(a.matchtime,%s) as matchtime,
					a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid 
					where a.round = 'V' 
					and result1>-1 
					and result2>-1 
					order by spieltag,origtime;",
					'%d.%m<br />%H:%i',
					$cs_match,
					$cs_team,
					$cs_team
				)
			);
			// @codingStandardsIgnoreEnd
		}

		$out .= '<p>&nbsp;</p>';
		// hole tipps des users.
		if ( '?' != $username ) {
			// @codingStandardsIgnoreStart
			$r2 = $wpdb->get_results(
				$wpdb->prepare(
					'select mid,result1,result2 
					from  %i inner join %i on ID=userid 
					where user_nicename=%s
					order by mid',
					$cs_tipp,
					$wp_users,
					$username
				)
			);
			// @codingStandardsIgnoreEnd
			$out .= "<p>Tipps von $username:</p>";
		} else {
			// @codingStandardsIgnoreStart
			$r2 = $wpdb->get_results(
				$wpdb->prepare(
					'select mid,result1,result2,user_nicename 
					from %i inner join %i on ID=userid $tipgroup_sql 
					order by mid',
					$cs_tipp,
					$wp_users
				)
			);
			// @codingStandardsIgnoreEnd
			$out .= '<p>Tipps aller Spieler:</p>';
		}

		$tipps = array();
		foreach ( $r2 as $r ) {
			$tipps[ $r->mid ] = $r;
		}

		$out .= "<table class='xwpc_table' ><tr><th class='xwpc_th'>" . __( 'match', 'wp-championship' ) . '</th>';
		if ( '?' == $username ) {
			$out .= "<th class='xwpc_th'>" . __( 'Player', 'wp-championship' ) . '</td>';
		}
		$out .= "<th class='xwpc_th'>" . __( 'Result', 'wp-championship' ) . "</th><th class='xwpc_th'>" . __( 'Tip', 'wp-championship' ) . '</th></tr>';

		foreach ( $r1 as $r ) {
			if ( '' != $tipps[ $r->mid ]->user_nicename || '?' != $username ) {
				if ( get_option( 'cs_xmlrpc_shortname' ) > 0 ) {
					$out .= "<tr><td class='xwpc_td'>" . $r->steam1 . ' - ' . $r->steam2 . '</td>';
				} else {
					$out .= "<tr><td class='xwpc_td'>" . $r->team1 . ' - ' . $r->team2 . '</td>';
				}
				if ( '?' == $username ) {
					$out .= "<td class='xwpc_td'>" . $tipps[ $r->mid ]->user_nicename . '</td>';
				}
				$out .= "<td class='xwpc_td' align='center'>" . $r->result1 . ':' . $r->result2 . "</td>\n";
				$tr1  = ( -1 == $tipps[ $r->mid ]->result1 ? '-' : $tipps[ $r->mid ]->result1 );
				$tr2  = ( -1 == $tipps[ $r->mid ]->result2 ? '-' : $tipps[ $r->mid ]->result2 );

				$out .= "<td class='xwpc_td' align='center'>" . $tr1 . ':' . $tr2 . "</td></tr>\n";
			}
		}

		$out .= "</table>\n";

		return $out;
	}

	/**
	 * Show userstats5.
	 *
	 * @param array $parm Parameter for Userstat.
	 */
	protected function show_UserStats5( $parm ) {
		include 'globals.php';
		global $wpdb;

		$out     = '';
		$newday5 = $parm;

		// get data for header.
		if ( get_option( 'cs_modus' ) == 1 ) {
			// @codingStandardsIgnoreStart
			$r1 = $wpdb->get_results(
				$wpdb->prepare(
					"select a.mid as mid,b.groupid as groupid,b.name as team1,b.shortname as shortname1,b.icon as icon1, c.name as team2,
					c.shortname as shortname2, c.icon as icon2,a.location as location,date_format(a.matchtime, %s) as matchtime,
					a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where a.round in ('V','F') 
					and result1>-2 
					and result2>-2 
					and date(a.matchtime)=%s 
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
					c.shortname as shortname2, c.icon as icon2,a.location as location,date_format(a.matchtime, %i) as matchtime,
					a.matchtime as origtime,a.result1 as result1,a.result2 as result2,a.winner as winner,a.round as round, a.spieltag as spieltag 
					from %i a inner join %i b on a.tid1=b.tid 
					inner join %i c on a.tid2=c.tid 
					where a.round = 'V' 
					and result1>-2 
					and result2>-2 
					and spieltag=%s 
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

		$out .= '<p>&nbsp;</p>';
		$out .= "<table class='xwpc_table' ><tr><th class='xwpc_th'>" . __( 'Username', 'wp-championship' ) . '</th>';
		foreach ( $r1 as $r ) {
			$short_team1 = ( strlen( trim( $r->shortname1 ) ) > 0 ? $r->shortname1 : substr( $r->team1, 0, 3 ) );
			$short_team2 = ( strlen( trim( $r->shortname2 ) ) > 0 ? $r->shortname2 : substr( $r->team2, 0, 3 ) );

			$out .= "<th class='xwpc_th'>" . $short_team1 . '<br />' . ( -1 == $r->result1 ? '-' : $r->result1 ) . ':' . ( -1 == $r->result2 ? '-' : $r->result2 ) . '<br/>' . $short_team2 . '</th>';
		}
		$out .= "<th class='xwpc_th'>&empty;</th><th class='xwpc_th'>" . __( 'Points', 'wp-championship' ) . '</th>';
		$out .= '</tr>';

		$stats5_tippgroup = ( isset( $_GET['tippgroup'] ) ? sanitize_text_field( wp_unslash( $_GET['tippgroup'] ) ) : '' );
		$tippgroup_sql    = '';
		if ( '' != $stats5_tippgroup ) {
			$tippgroup_sql = " where tippgroup='$stats5_tippgroup' ";
		}

		// get data for table.
		$r2 = $wpdb->get_results(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				'select user_nicename, userid from %i inner join %i on ID=userid %i order by user_nicename;',
				$wp_users,
				$cs_users,
				$tippgroup_sql,
			)
		);

		foreach ( $r2 as $r ) {
			// fetch results per day and user.
			if ( get_option( 'cs_modus' ) == 1 ) {
				// @codingStandardsIgnoreStart
				$r3 = $wpdb->get_results(
					$wpdb->prepare(
						"select a.result1 as res1, a.result2 as res2, a.points as points, b.matchtime as origtime 
						from %i b left outer join %i a on a.mid=b.mid and a.userid=%d 
						where date(b.matchtime)=%s 
						and b.result1>-1 
						and b.result2>-1 
						and b.round in ('V','F') 
						order by origtime;",
						$cs_match,
						$cs_tipp,
						$r->userid,
						$newday5
					)
				);
				// @codingStandardsIgnoreEnd
			} else {
				$r3 = $wpdb->get_results(
					// @codingStandardsIgnoreStart
					$wpdb->prepare(
						"select a.result1 as res1, a.result2 as res2, a.points as points, b.matchtime as origtime 
						from %i b left outer join %i a on a.mid=b.mid and a.userid=%d 
						where spieltag=%d  
						and b.result1>-1 
						and b.result2>-1 
						and b.round ='V' 
						order by spieltag,origtime;",
						$cs_match,
						$cs_tipp,
						$r->userid,
						$newday5
					)
				);
				// @codingStandardsIgnoreEnd
			}

			if ( $r3 ) {

				$out .= "<tr><td class='xwpc_td'>" . $r->user_nicename . '</td>';
				$anz  = 0;
				$sum  = 0;
				foreach ( $r3 as $s ) {
					if ( -1 == $s->res1 || null === $s->res1 ) {
						$out .= "<td class='xwpc_td'>-:-<sub>-</sub></td>";
					} else {
						$out .= "<td class='xwpc_td'>" . $s->res1 . ':' . $s->res2 . '<sub>' . $s->points . '</sub></td>';
						$sum += $s->points;
						++$anz;
					}
				}
				if ( $anz > 0 ) {
					$out .= "<td class='xwpc_td'>" . round( $sum / $anz, 2 ) . '</td>';
				} else {
					$out .= "<td class='xwpc_td'>-</td>";
				}
				$out .= "<td class='xwpc_td'>$sum</td>";
				$out .= '</tr>';
			}
		}
		$out .= '</table>';

		return $out;
	}
}

// replace xmlrpc class for WordPress.
add_filter( 'wp_xmlrpc_server_class', array( 'WPC_XMLRPC', 'wpc_getName' ) );
