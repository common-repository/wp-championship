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
 * @package wp-cahmpionship
 */

// generic functions.
require_once 'functions.php';
require_once 'supp/supp.php';

// function to show and maintain the set of teams for the championship.


if ( ! function_exists( 'cs_admin_team' ) ) { // make it pluggable.
	/**
	 * Function for team-dialog.
	 */
	function cs_admin_team() {
		include 'globals.php';
		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_team.php';
		// get group option and define group ids.
		$groupstr  = 'ABCDEFGHIJKLM';
		$cs_groups = get_option( 'cs_groups' );
		// get sql object.
		$wpdb = & $GLOBALS['wpdb'];
		$wpdb->show_errors( true );
		// find out what we have to do.
		$action = '';

		if ( isset( $_POST['submit'] ) ) {
			$action = 'savenew';
		} elseif ( isset( $_POST['update'] ) ) {
			$action = 'update';
		} elseif ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) ) == 'remove' ) {
			$action = 'remove';
		} elseif ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) ) == 'modify' ) {
			$action = 'edit';
		}

		// check nonce.
		if ( '' != $action && 'remove' != $action && 'edit' != $action ) {
			if ( ! isset( $_POST['wpc_nonce_team'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_team'] ) ), 'wpc_nonce_team' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		// add or update team data.
		//
		$errflag = 0;

		if ( 'savenew' == $action || 'update' == $action ) {
			// check form contents for mandatory fields and/or set default values.
			$team_name      = ( isset( $_POST['team_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_name'] ) ) : '' );
			$team_shortname = ( isset( $_POST['team_shortname'] ) ? sanitize_text_field( wp_unslash( $_POST['team_shortname'] ) ) : '' );
			$team_icon      = ( isset( $_POST['team_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['team_icon'] ) ) : '' );
			$team_qualified = ( isset( $_POST['qualified'] ) ? sanitize_text_field( wp_unslash( $_POST['qualified'] ) ) : '' );
			$team_penalty   = ( isset( $_POST['penalty'] ) ? sanitize_text_field( wp_unslash( $_POST['penalty'] ) ) : '' );
			$team_group     = ( isset( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '' );
			$team_tid       = ( isset( $_POST['tid'] ) ? sanitize_text_field( wp_unslash( $_POST['tid'] ) ) : '' );

			if ( '' == $team_name ) {
				$errflag = 1;
			} elseif ( '#' == substr( $team_name, 0, 1 ) ) {
				$errflag = 2;
			}

			if ( '' == $team_icon ) {
				$team_icon = 'default.png';
			}

			if ( '' == $team_qualified ) {
				$team_qualified = 0;
			}

			if ( '' == $team_penalty ) {
				$team_penalty = 0;
			}

			// send a message about mandatory data.
			if ( 1 == $errflag ) {
				admin_message( __( 'The name of the team can not be empty.', 'wp-championship' ) );
			}

			if ( 2 == $errflag ) {
				admin_message( __( 'The name of the team can not start with a #.', 'wp-championship' ) );
			}

			// error in update form data causes to reprint the update form.
			if ( $errflag > 0 && 'update' == $action ) {
				$action = 'edit';
			}

			// insert new team into database.
			if ( 0 == $errflag && 'savenew' == $action ) {
				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						'insert into %i values (0, %s, %s, %s, %s, %d, %d);',
						$cs_team,
						$team_name,
						$team_shortname,
						$team_icon,
						$team_group,
						$team_qualified,
						$team_penalty
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'Team successfully added.', 'wp-championship' ) );
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
						'update %i set name=%s, shortname=%s, icon=%s,groupid=%s,qualified=%d, penalty=%d where tid=%d;',
						$cs_team,
						$team_name,
						$team_shortname,
						$team_icon,
						$team_group,
						$team_qualified,
						$team_penalty,
						$team_tid
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'Team successfully saved.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}
		}
		// remove data from database.

		if ( 'remove' == $action ) {
			$tid = ( isset( $_GET['tid'] ) ? sanitize_text_field( wp_unslash( $_GET['tid'] ) ) : '' );
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( 'delete from %i where tid=%d;', $tid, $cs_team ) );
			if ( 1 == $results ) {
				admin_message( __( 'Team deleted.', 'wp-championship' ) );
			} else {
				admin_message( __( 'Database error, aborting', 'wp-championship' ) );
			}
		}

		// output teams add/modify form.
		$resed = array(
			'name'      => '',
			'shortname' => '',
			'icon'      => '',
			'groupid'   => '',
			'qualified' => '',
			'penalty'   => 0,
		);

		if ( 'edit' == $action ) {
			// select data to modify.
			$tid = ( isset( $_GET['tid'] ) ? sanitize_text_field( wp_unslash( $_GET['tid'] ) ) : '' );
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$resed = $wpdb->get_row( $wpdb->prepare( 'select * from  %i where tid=%d;', $cs_team, $tid ), ARRAY_A );
		}

		// build form ==========================================================.

				$out = '';
		// select header for update or add team.

		if ( 'edit' == $action ) {
			$out .= '<div class="wrap"><h2>' . __( 'Update team', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="modifyteam" id="modifyteam" method="post" action="#"><input type="hidden" name="action" value="modifyteam" /><input type="hidden" name="tid" value="' . $resed['tid'] . '" />' . "\n";
		} else {
			$out .= '<div class="wrap"><h2>' . __( 'Add team', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="addteam" id="addteam" method="post" action="#"><input type="hidden" name="action" value="addteam" />' . "\n";
		}
		// add nonce.
		$out .= '<input name="wpc_nonce_team" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_team' ) . '" />';

		$out .= '<table class="editform" style="width:100%"><tr>';
		$out .= '<th style="width:33%" scope="row" ><label for="team_name">' . __( 'Name', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><input name="team_name" id="team_name" type="text" value="' . $resed['name'] . '" size="40" onblur="calc_shortname();" /></td></tr>' . "\n";
		$out .= '<th style="width:33%" scope="row" ><label for="team_shortname">' . __( 'Shortname', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><input name="team_shortname" id="team_shortname" type="text" value="' . $resed['shortname'] . '" size="5" maxlength="5" /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="team_icon">' . __( 'Symbol / emblem', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td><input name="team_icon" id="team_icon" type="text" value="' . $resed['icon'] . '" size="40" /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="group">' . __( 'Group', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><select name="group" id="group" class="postform">' . "\n";
		// build group selection box.

		for ( $i = 0;$i < $cs_groups;$i++ ) {
			$charone = substr( $groupstr, $i, 1 );
			$out    .= '<option value="' . $charone . '"';

			if ( $charone == $resed['groupid'] ) {
				$out .= ' selected="selected"';
			}
			$out .= '>' . $charone . '</option>';
		}
		$out .= '</select></td>	</tr>';
		$out .= '<tr><th scope="row" ><label for="qualified">' . __( 'preliminary placement', 'wp-championship' ) . ':</label></th>' . "\n";
		$sql  = "select count(*) as anz from $cs_team where groupid='" . $resed['groupid'] . "';";
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$res1 = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from %i where groupid=%s;', $cs_team, $resed['groupid'] ) );
		$out .= '<td><select name="qualified" id="qualified" class="postform">' . "\n";
		// build qualified selection box.

		for ( $i = 0;$i <= $res1->anz;$i++ ) {
			$out .= '<option value="' . $i . '"';

			if ( $i == $resed['qualified'] ) {
				$out .= ' selected="selected"';
			}
			$out .= '>' . $i . '</option>';
		}
		$out .= '</select></td></tr>';
		// Penalty feld.
		$out .= '<tr><th scope="row" ><label for="penalty">' . __( 'Penalty', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="penalty" id="penalty" class="postform" tyoe="text" size="3" maxlength="3" value="' . $resed['penalty'] . '">' . "\n";
		$out .= '</td></tr>';
		$out .= '</table>' . "\n";
		// add submit button to form.

		if ( 'edit' == $action ) {
			$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save team', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		} else {
			$out .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Add team', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		}
		echo wp_kses( $out, wpc_allowed_tags() );

		// output teams table.
		$out  = '';
		$out .= '<div class="wrap">';
		$out .= '<h2>' . __( 'Teams', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class=\"widefat\"><thead><tr>\n";
		$out .= '<th scope="col" style="text-align: center">ID</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Name', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Shortname', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Symbol / emblem', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center;width:90px">' . __( 'Group', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center;width:90px">' . __( 'placement', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center;width:90px">' . __( 'Penalty', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center">' . __( 'Action', 'wp-championship' ) . '</th>' . "</tr></thead>\n";
		// teams loop.

		if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
			$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
		} else {
			$iconpath = plugins_url( 'icons/', __FILE__ );
		}

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results = $wpdb->get_results( $wpdb->prepare( 'select * from  %i where name not like %s order by tid;', $cs_team, '#%' ) );

		foreach ( $results as $res ) {
			$out .= "<tr><td style='text-align:center'>" . $res->tid . '</td><td>' . $res->name . '</td>';
			$out .= '<td>' . $res->shortname . '</td>';
			if ( substr( $res->icon, 0, 4 ) == 'http' ) {
				$out .= "<td><img src='" . $res->icon . "' alt='icon' height='32px' width='32px'/>" . $res->icon . '</td>';
			} else {
				$out .= "<td><img src='" . $iconpath . $res->icon . "' alt='icon' height='32px' width='32px'/>" . $res->icon . '</td>';
			}
			$out .= "<td style='text-align:center'>&nbsp;" . $res->groupid . '</td>';
			$out .= "<td style='text-align:center'>" . $res->qualified . '</td>';
			$out .= "<td style='text-align:center'>" . $res->penalty . '</td>';
			$out .= "<td style='text-align:center'><a href=\"" . $thisform . '&amp;action=modify&amp;tid=' . $res->tid . '">' . __( 'Update', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;';
			$out .= '<a href="' . $thisform . '&amp;action=remove&amp;tid=' . $res->tid . '">' . __( 'Delete', 'wp-championship' ) . "</a></td></tr>\n";
		}
		$out .= '</table></div>' . "\n";
		echo wp_kses( $out, wpc_allowed_tags() );
	}
} //make it pluggable.


