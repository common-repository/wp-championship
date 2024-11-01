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

// function to show and maintain the set of matches for the championship.


if ( ! function_exists( 'cs_admin_match' ) ) { // make it pluggable.
	/**
	 * Function to edit match data.
	 */
	function cs_admin_match() {
		include 'globals.php';
		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_match.php';
		// get group option and define group ids.
		$groupstr  = 'ABCDEFGHIJKLM';
		$cs_groups = get_option( 'cs_groups' );
		// get sql object.
		$wpdb = & $GLOBALS['wpdb'];

		// find out what we have to do.
		$action = '';

		if ( isset( $_POST['submit'] ) ) {
			$action = 'savenew';
		} elseif ( isset( $_POST['update'] ) ) {
			$action = 'update';
		} elseif ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) == 'remove' ) ) {
			$action = 'remove';
		} elseif ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) == 'modify' ) ) {
			$action = 'edit';
		}

		// check nonce.
		if ( '' != $action && 'remove' != $action && 'edit' != $action ) {
			if ( ! isset( $_POST['wpc_nonce_match'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_match'] ) ), 'wpc_nonce_match' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
		}
		if ( '' != $action && 'remove' == $action ) {
			if ( ! isset( $_GET['delnonce'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['delnonce'] ) ), 'wpc_nonce_match_delete' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
		}

		// add or update match data.
		//
		$errflag = 0;

		if ( 'savenew' == $action || 'update' == $action ) {
			// check form contents for mandatory fields and/or set default values.
			$matchtime = ( isset( $_POST['matchtime'] ) ? sanitize_text_field( wp_unslash( $_POST['matchtime'] ) ) : '' );
			if ( '' == $matchtime ) {
				$errflag = 1;
			}

			$location = ( isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '' );
			if ( '' == $location ) {
				$location = __( 'nowhere', 'wp-championship' );
			}

			$mid = 0;
			if ( isset( $_POST['mid'] ) ) {
				$mid = intval( $_POST['mid'] );
			}

			$spieltag = 0;
			if ( isset( $_POST['spieltag'] ) ) {
				$spieltag = sanitize_text_field( wp_unslash( $_POST['spieltag'] ) );
			}

			$team1 = ( isset( $_POST['team1'] ) ? intval( $_POST['team1'] ) : -1 );
			$team2 = ( isset( $_POST['team2'] ) ? intval( $_POST['team2'] ) : -1 );

			// send a message about mandatory data.
			if ( 1 == $errflag ) {
				admin_message( __( 'Date and time are both mandatory.', 'wp-championship' ) );
			}

			// error in update form data causes to reprint the update form.
			if ( 1 == $errflag && 'update' == $action ) {
				$action = 'edit';
			}

			// insert new match into database.
			if ( 0 == $errflag && 'savenew' == $action ) {
				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						"insert into %i values (0,'V',%d,%d,%d,%s,%s,-1,-1,-1,-1,-1);",
						$cs_match,
						$spieltag,
						$team1,
						$team2,
						$location,
						$matchtime
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'Match successfully added.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}

			// update team.
			if ( 0 == $errflag && 'update' == $action ) {
				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						'update %i set tid1=%d, tid2=%d, location=%s, matchtime=%s, spieltag=%d where mid=%d;',
						$cs_match,
						$team1,
						$team2,
						$location,
						$matchtime,
						$spieltag,
						$mid
					)
				);

				if ( false === $results ) {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				} else {
					admin_message( __( 'Match successfully saved.', 'wp-championship' ) );
				}
			}
		}
		// remove data from database.

		if ( 'remove' == $action ) {
			$mid = ( isset( $_GET['mid'] ) ? intval( $_GET['mid'] ) : -1 );
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( 'delete from %i where mid=%d;', $cs_match, $mid ) );

			if ( 1 == $results ) {
				admin_message( __( 'Match deleted.', 'wp-championship' ) );
			} else {
				admin_message( __( 'Database error, aborting', 'wp-championship' ) );
			}
		}
		// output teams add/modify form.

		if ( 'edit' == $action ) {
			// select data to modify.
			$mid = ( isset( $_GET['mid'] ) ? intval( $_GET['mid'] ) : -1 );
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->get_row( $wpdb->prepare( 'select * from %i where mid=%d;', $cs_match, $mid ) );
		}

		// build form ==========================================================.

		$out               = '';
		$team1_select_html = '';
		$team2_select_html = '';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( 'select tid,name from %i where name not like %s order by name;', $cs_team, '#%' ) );

		foreach ( $results1 as $res ) {
			$team1_select_html .= "<option value='" . $res->tid . "' ";

			if ( isset( $results->tid1 ) && $res->tid == $results->tid1 ) {
				$team1_select_html .= "selected='selected'";
			}
			$team1_select_html .= '>' . $res->name . "</option>\n";
			$team2_select_html .= "<option value='" . $res->tid . "' ";

			if ( isset( $results->tid2 ) && $res->tid == $results->tid2 ) {
				$team2_select_html .= "selected='selected'";
			}
			$team2_select_html .= '>' . $res->name . "</option>\n";
		}
		// select header for update or add match.

		if ( 'edit' == $action ) {
			$out .= '<div class="wrap"><h2>' . __( 'Update match', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="modifymatch" id="modifymatch" method="post" action="#"><input type="hidden" name="action" value="modifymatch" /><input type="hidden" name="mid" value="' . $results->mid . '" />' . "\n";
		} else {
			$out .= '<div class="wrap"><h2>' . __( 'Add Match', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="addmatch" id="addmatch" method="post" action="#"><input type="hidden" name="action" value="addmatch" />' . "\n";
		}
		// add nonce.
		$out .= '<input name="wpc_nonce_match" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_match' ) . '" />';

		$out .= '<table class="editform" style="width:100%"><tr>';
		$out .= '<th style="width:33%" scope="row" ><label for="team1">' . __( 'Team 1', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><select id="team1" name="team1">' . $team1_select_html . '</select></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="team2">' . __( 'Team 2', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td><select id="team2" name="team2">' . $team2_select_html . '</select></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="location">' . __( 'Location', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="location" id="location" type="text" value="' . ( isset( $results->location ) ? $results->location : '' ) . '" size="40" /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="matchtime">' . __( 'Date / time', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="matchtime" id="matchtime" type="text" value="' . ( isset( $results->matchtime ) ? $results->matchtime : '' ) . '" size="40" /></td></tr>' . "\n";

		// spieltag ausgeben wenn in liga-modus.
		if ( 2 == get_option( 'cs_modus' ) ) {
			$out .= '<tr><th scope="row"><label for="spieltag">' . __( 'Match-Day', 'wp-championship' ) . ':</label></th>' . "\n";
			$out .= '<td><input name="spieltag" id="spieltag" type="text" value="';
			if ( isset( $results ) && isset( $results->spieltag ) && -1 != $results->spieltag ) {
				$out .= $results->spieltag;
			}
			$out .= '" size="3" /></td></tr>' . "\n";
		}
		$out .= '</table>' . "\n";
		// add submit button to form.

		if ( 'edit' == $action ) {
			$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save match', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		} else {
			$out .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Add Match', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		}
		echo wp_kses( $out, wpc_allowed_tags() );

		// output match table.
		$out  = '';
		$out  = '<div class="wrap">';
		$out .= '<h2>' . __( 'Matches', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class=\"widefat\"><thead><tr>\n";
		$out .= '<th scope="col" style="text-align: center">ID</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Team 1', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Team 2', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Location', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Date / time', 'wp-championship' ) . '</th>' . "\n";

		if ( get_option( 'cs_modus' ) == 2 ) {
			$out .= '<th scope="col" style="width:10px;text-align: center">' . __( 'Match-Day', 'wp-championship' ) . '</th>' . "\n";
		}
		$out .= '<th style="text-align: center">' . __( 'Action', 'wp-championship' ) . '</th></tr></thead>' . "\n";
		// match loop.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select a.mid as mid,b.name as team1,c.name as team2,a.location as location,a.matchtime as matchtime, a.spieltag as spieltag from %i a inner join %i b on a.tid1=b.tid inner join %i c on a.tid2=c.tid where a.round='V' order by mid;",
				$cs_match,
				$cs_team,
				$cs_team
			)
		);

		// create nonce.
		$delnonce = wp_create_nonce( 'wpc_nonce_match_delete' );

		foreach ( $results as $res ) {
			$out .= "<tr><td style='text-align:center'>" . $res->mid . '</td><td>' . $res->team1 . '</td>';
			$out .= '<td>' . $res->team2 . "</td><td style='text-align:center'>" . $res->location . '</td>';
			$out .= "<td style='text-align:center'>" . $res->matchtime . '</td>';

			if ( 2 == get_option( 'cs_modus' ) ) {
				$out .= "<td style='text-align:center'>" . ( -1 == $res->spieltag ? '-' : $res->spieltag ) . '</td>';
			}
			$out .= "<td style='text-align:center'><a href=\"" . $thisform . '&amp;action=modify&amp;mid=' . $res->mid . '">' . __( 'Update', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;';
			$out .= "<a href='$thisform&amp;action=remove&amp;mid={$res->mid}&amp;delnonce=$delnonce'>" . __( 'Delete', 'wp-championship' ) . "</a></td></tr>\n";
		}
		$out .= '</table></div>' . "\n";
		echo wp_kses( $out, wpc_allowed_tags() );
	}
} //make it pluggable.


