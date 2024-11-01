<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2007-2014  Hans Matzen  (email : webmaster at tuxlog.de)
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

// generic functions.
require_once 'functions.php';
require_once 'supp/supp.php';
//
// function to show and maintain the set of matches for the championship.
//
if ( ! function_exists( 'cs_admin_finals' ) ) {
	/**
	 * Function for the final round admin dialog.
	 */
	function cs_admin_finals() {
		include 'globals.php';

		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_finals.php';
		// get group option and define group ids.
		$groupstr  = 'ABCDEFGHIJKLM';
		$cs_groups = get_option( 'cs_groups' );
		// get sql object.
		$wpdb =& $GLOBALS['wpdb'];

		// find out what we have to do.
		$action = '';
		if ( isset( $_POST['submit'] ) ) {
			$action = 'savenew';
		} elseif ( isset( $_POST['update'] ) ) {
			$action = 'update';
		} elseif ( isset( $_GET['action'] ) && 'remove' == $_GET['action'] ) {
			$action = 'remove';
		} elseif ( isset( $_GET['action'] ) && 'modify' == $_GET['action'] ) {
			$action = 'edit';
		}

		// check nonce.
		if ( '' != $action && 'remove' != $action && 'edit' != $action ) {
			if ( ! isset( $_POST['wpc_nonce_finals'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_finals'] ) ), 'wpc_nonce_finals' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}
		if ( '' != $action && 'remove' == $action ) {
			if ( ! isset( $_GET['delnonce'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['delnonce'] ) ), 'wpc_nonce_finals_delete' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		// add or update match data.
		//
		$errflag = 0;

		if ( 'savenew' == $action || 'update' == $action ) {
			// check form contents for mandatory fields and/or set default values.
			$pmatchtime = ! empty( $_POST['matchtime'] ) ? sanitize_text_field( wp_unslash( $_POST['matchtime'] ) ) : '';
			if ( '' == $pmatchtime ) {
				admin_message( __( 'Please enter time and date of the match (e.g. 2010-06-21 18:00:00).', 'wp-championship' ) );
			}

			$plocation = ! empty( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
			if ( '' == $plocation ) {
				$_plocation = __( 'nowhere', 'wp-championship' );
			}

			$pmid = ! empty( $_POST['mid'] ) ? intval( $_POST['mid'] ) : -1;

			// get the teams.
			$pwinner1  = isset( $_POST['winner1'] ) ? sanitize_text_field( wp_unslash( $_POST['winner1'] ) ) : '-1';
			$pfgroup1  = ! empty( $_POST['fgroup1'] ) ? sanitize_text_field( wp_unslash( $_POST['fgroup1'] ) ) : '';
			$pfplace1  = ! empty( $_POST['fplace1'] ) ? sanitize_text_field( wp_unslash( $_POST['fplace1'] ) ) : '';
			$pmatchid1 = ! empty( $_POST['matchid1'] ) ? intval( $_POST['matchid1'] ) : -1;
			if ( '-1' == $pwinner1 ) {
				$team1 = $pfgroup1 . $pfplace1;
			} else {
				$team1 = ( '1' == $pwinner1 ? 'W' : 'V' ) . $pmatchid1;
			}

			$pwinner2  = isset( $_POST['winner2'] ) ? sanitize_text_field( wp_unslash( $_POST['winner2'] ) ) : '-1';
			$pfgroup2  = ! empty( $_POST['fgroup2'] ) ? sanitize_text_field( wp_unslash( $_POST['fgroup2'] ) ) : '';
			$pfplace2  = ! empty( $_POST['fplace2'] ) ? sanitize_text_field( wp_unslash( $_POST['fplace2'] ) ) : '';
			$pmatchid2 = ! empty( $_POST['matchid2'] ) ? intval( $_POST['matchid2'] ) : -1;
			if ( '-1' == $pwinner2 ) {
				$team2 = $pfgroup2 . $pfplace2;
			} else {
				$team2 = ( '1' == $pwinner2 ? 'W' : 'V' ) . $pmatchid2;
			}

			// check if teams already exist.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r0 = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where name=%s;', $cs_team, '#' . $team1 ) );
			if ( 0 == $r0->anz ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$results = $wpdb->query( $wpdb->prepare( "insert into %i values (0,%s,'','','',1,0);", $cs_team, '#' . $team1 ) );

				if ( 1 == $results ) {
					admin_message( __( 'Team one added automatically.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, team 1 could not be added automatically.', 'wp-championship' ) );
				}
			}

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r0 = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where name=%s;', $cs_team, '#' . $team2 ) );
			if ( 0 == $r0->anz ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$results = $wpdb->query( $wpdb->prepare( "insert into %i values (0,%s,'','','',1,0);", $cs_team, '#' . $team2 ) );

				if ( 1 == $results ) {
					admin_message( __( 'Team 2 added automatically.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, team 2 could not be added automatically.', 'wp-championship' ) );
				}
			}

			// error in update form data causes to reprint the update form.
			if ( 1 == $errflag && 'update' == $action ) {
				$action = 'edit';
			}

			// insert new match into database.
			if ( 0 == $errflag && 'savenew' == $action ) {
				// get team ids.
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r0   = $wpdb->get_row( $wpdb->prepare( 'select tid as tid1 from %i where name=%s;', $cs_team, '#' . $team1 ) );
				$tid1 = $r0->tid1;

				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r0   = $wpdb->get_row( $wpdb->prepare( 'select tid as tid2 from %i where name=%s;', $cs_team, '#' . $team2 ) );
				$tid2 = $r0->tid2;

				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						"insert into %i values (0,'F',-1, %d, %d,%s,%s,-1,-1,-1,%d,%d);",
						$cs_match,
						$tid1,
						$tid2,
						$plocation,
						$pmatchtime,
						$tid1,
						$tid2
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'final match successfully created.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}

			// update match.
			if ( 0 == $errflag && 'update' == $action ) {
				// get team ids.
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r0   = $wpdb->get_row( $wpdb->prepare( 'select tid as tid1 from %i where name=%s;', $cs_team, '#' . $team1 ) );
				$tid1 = $r0->tid1;

				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$r0   = $wpdb->get_row( $wpdb->prepare( 'select tid as tid2 from %i where name=%s;', $cs_team, '#' . $team2 ) );
				$tid2 = $r0->tid2;

				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						'update %i set tid1=%d, tid2=%d, location=%s, matchtime=%s, ptid1=%d, ptid2=%d where mid=%d;',
						$cs_match,
						$tid1,
						$tid2,
						$plocation,
						$pmatchtime,
						$tid1,
						$tid2,
						$pmid
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'final match successfully saved.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}
		}

		// remove data from database.
		if ( 'remove' == $action ) {
			$gmid = ! empty( $_GET['mid'] ) ? intval( $_GET['mid'] ) : -1;
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( 'delete from %i where mid=%d;', $cs_match, $gmid ) );
			if ( 1 == $results ) {
				admin_message( __( 'final match deleted.', 'wp-championship' ) );
			} else {
				admin_message( __( 'Database error, aborting', 'wp-championship' ) );
			}
		}

		// output teams add/modify form.
		$w1 = -1;
		$w2 = -1;
		$g1 = -1;
		$g2 = -1;
		$p1 = -1;
		$p2 = -1;
		$m1 = -1;
		$m2 = -1;
		if ( 'edit' == $action ) {
			// select data to modify.
			$gmid = ! empty( $_GET['mid'] ) ? intval( $_GET['mid'] ) : -1;
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->get_row( $wpdb->prepare( 'select * from  %i where mid=%d', $cs_match, $gmid ) );

			// select stored data for preselection in form.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r0 = $wpdb->get_row( $wpdb->prepare( 'select * from %i where tid=%d;', $cs_team, $results->ptid1 ) );

			$g1    = -1;
			$p1    = -1;
			$w1    = -1;
			$m1    = -1;
			$code1 = substr( $r0->name, 1, 1 );
			if ( 'W' == $code1 || 'V' == $code1 ) {
				$w1 = $code1;
				$m1 = substr( $r0->name, 2 );
			} else {
				$g1 = $code1;
				$p1 = substr( $r0->name, 2 );
			}

			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r1 = $wpdb->get_row( $wpdb->prepare( 'select * from %i where tid=%d;', $cs_team, $results->ptid2 ) );

			$g2    = -1;
			$p2    = -1;
			$w2    = -1;
			$m2    = -1;
			$code1 = substr( $r1->name, 1, 1 );
			if ( 'W' == $code1 || 'V' == $code1 ) {
				$w2 = $code1;
				$m2 = substr( $r1->name, 2 );
			} else {
				$g2 = $code1;
				$p2 = substr( $r1->name, 2 );
			}
		}

		//
		// build form ==========================================================.
		//
		$out = '';

		$match1_select_html = '<option value="-1">-</option>';
		$match2_select_html = '<option value="-1">-</option>';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( "select mid from %i where round='F' order by mid;", $cs_match ) );

		foreach ( $results1 as $res ) {
			$match1_select_html .= "<option value='" . $res->mid . "' ";
			if ( $res->mid == $m1 ) {
				$match1_select_html .= "selected='selected'";
			}
			$match1_select_html .= '>' . $res->mid . "</option>\n";

			$match2_select_html .= "<option value='" . $res->mid . "' ";
			if ( $res->mid == $m2 ) {
				$match2_select_html .= "selected='selected'";
			}
			$match2_select_html .= '>' . $res->mid . "</option>\n";
		}
		// build the selection boxes.
		$groupsel1_html = cs_get_group_selector( get_option( 'cs_groups' ), 'fgroup1', $g1 );
		$placesel1_html = cs_get_place_selector( get_option( 'cs_group_teams' ), 'fplace1', $p1 );
		$groupsel2_html = cs_get_group_selector( get_option( 'cs_groups' ), 'fgroup2', $g2 );
		$placesel2_html = cs_get_place_selector( get_option( 'cs_group_teams' ), 'fplace2', $p2 );

		$wsel1_html = '<select name="winner1"><option value="-1" ' . ( -1 == $w1 ? 'selected="selected"' : '' ) . '>-</option><option value="1" ' . ( 'W' == $w1 ? 'selected="selected"' : '' ) . '>Gewinner</option><option value="0" ' . ( 'V' == $w1 ? 'selected="selected"' : '' ) . '>Verlierer</option></select>';
		$wsel2_html = '<select name="winner2"><option value="-1" ' . ( -1 == $w2 ? 'selected="selected"' : '' ) . '>-</option><option value="1" ' . ( 'W' == $w2 ? 'selected="selected"' : '' ) . '>Gewinner</option><option value="0" ' . ( 'V' == $w2 ? 'selected="selected"' : '' ) . '>Verlierer</option></select>';

		// select header for update or add match.
		if ( 'edit' == $action ) {
			$out .= '<div class="wrap"><h2>' . __( 'final match updated.', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="modifymatch" id="modifymatch" method="post" action="#"><input type="hidden" name="action" value="modifymatch" /><input type="hidden" name="mid" value="' . $results->mid . '" />' . "\n";
		} else {
			$out .= '<div class="wrap"><h2>' . __( 'Add final match', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="addmatch" id="addmatch" method="post" action="#"><input type="hidden" name="action" value="addmatch" />' . "\n";
		}

		// add nonce.
		$out .= '<input name="wpc_nonce_finals" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_finals' ) . '" />';

		$out .= '<table class="editform" style="width:100%" ><tr>';
		$out .= '<th style="width:33%" scope="row" ><label for="matchid1">' . __( 'Team 1', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%">Gruppe:' . $groupsel1_html . ' Platz:' . $placesel1_html . ' oder ' . $wsel1_html . ' Match Nr. <select id="matchid1" name="matchid1">' . $match1_select_html . '</select></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="matchid2">' . __( 'Team 2', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td style="width:67%">Gruppe:' . $groupsel2_html . ' Platz:' . $placesel2_html . ' oder ' . $wsel2_html . ' Match Nr. <select id="matchid2" name="matchid2">' . $match2_select_html . '</select></td></tr>' . "\n";

		$out .= '<tr><th scope="row" ><label for="location">' . __( 'Location', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="location" id="location" type="text" value="' . ( isset( $results->location ) ? $results->location : '' ) . '" size="40" /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="matchtime">' . __( 'Date / time', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="matchtime" id="matchtime" type="text" value="' . ( isset( $results->matchtime ) ? $results->matchtime : '' ) . '" size="40" /></td></tr>' . "\n";

		$out .= '</table>' . "\n";

		// add submit button to form.
		if ( 'edit' == $action ) {
			$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save final match', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		} else {
			$out .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Add final match', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		}

		echo wp_kses( $out, wpc_allowed_tags() );

		//
		// output match table.
		//
		$out  = '';
		$out  = '<div class="wrap">';
		$out .= '<h2>' . __( 'Final matches', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class=\"widefat\"><thead><tr>\n";
		$out .= '<th scope="col" style="text-align: center">ID</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Team 1', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Team 2', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Location', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Date / time', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th style="text-align: center">' . __( 'Action', 'wp-championship' ) . '</th></tr></thead>' . "\n";
		// match loop.
		// @codingStandardsIgnoreStart
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"select a.mid as mid,b.name as team1,c.name as team2,a.location as location,a.matchtime as matchtime 
				from %i a inner join %i b on a.ptid1=b.tid 
				inner join %i c on a.ptid2=c.tid 
				where a.round='F' 
				order by mid;",
				$cs_match,
				$cs_team,
				$cs_team
			)
		);
		// @codingStandardsIgnoreEnd

		// create nonce.
		$delnonce = wp_create_nonce( 'wpc_nonce_finals_delete' );

		foreach ( $results as $res ) {
			$out .= "<tr><td style='text-align:center'>" . $res->mid . '</td><td>' . cs_team2text( $res->team1 ) . '</td>';
			$out .= '<td>' . cs_team2text( $res->team2 ) . "</td><td style='text-align:center'>" . $res->location . '</td>';
			$out .= "<td style='text-align:center'>" . $res->matchtime . '</td>';
			$out .= "<td style='text-align:center'><a href=\"" . $thisform . '&amp;action=modify&amp;mid=' . $res->mid . '">' . __( 'Update', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;';
			$out .= "<a href='$thisform&amp;action=remove&amp;mid={$res->mid}&amp;delnonce=$delnonce'>" . __( 'Delete', 'wp-championship' ) . "</a></td></tr>\n";
		}
		$out .= '</table></div>' . "\n";

		echo wp_kses( $out, wpc_allowed_tags() );
	}
}//make it pluggable.


