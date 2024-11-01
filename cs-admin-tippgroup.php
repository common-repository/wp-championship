<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2016-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

// function to show and maintain the set of teams for the championship.


if ( ! function_exists( 'cs_admin_tippgroup' ) ) {
	/**
	 * Function for the tppgroup admin dialog
	 */
	function cs_admin_tippgroup() {
		include 'globals.php';
		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_tippgroup.php';

		// get sql object.
		$wpdb = & $GLOBALS['wpdb'];
		$wpdb->show_errors( true );
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
			if ( ! isset( $_POST['wpc_nonce_tippgroup'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_tippgroup'] ) ), 'wpc_nonce_tippgroup' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}
		if ( '' != $action && 'remove' == $action || 'edit' == $action ) {
			if ( ! isset( $_GET['delnonce'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['delnonce'] ) ), 'wpc_nonce_tippgroup' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		// add or update team data.
		$errflag = 0;

		if ( 'savenew' == $action || 'update' == $action ) {
			// check form contents for mandatory fields and/or set default values.
			$pteam_name      = isset( $_POST['team_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_name'] ) ) : '';
			$pteam_shortname = isset( $_POST['team_shortname'] ) ? sanitize_text_field( wp_unslash( $_POST['team_shortname'] ) ) : '';
			$pteam_icon      = isset( $_POST['team_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['team_icon'] ) ) : '';
			$ppenalty        = isset( $_POST['penalty'] ) ? sanitize_text_field( wp_unslash( $_POST['penalty'] ) ) : '';
			$ptgid           = isset( $_POST['tgid'] ) ? intval( $_POST['tgid'] ) : -1;
			if ( '' == $pteam_name ) {
				$errflag = 1;
				admin_message( __( 'The name of the tip group can not be empty.', 'wp-championship' ) );
			} elseif ( substr( $pteam_name, 0, 1 ) == '#' ) {
				$errflag = 2;
				admin_message( __( 'The name of the tip group can not start with a #.', 'wp-championship' ) );
			}

			if ( '' == $pteam_icon ) {
				$pteam_icon = 'default.png';
			}

			if ( '' == $_POST['penalty'] ) {
				$_POST['penalty'] = 0;
			}

			// error in update form data causes to reprint the update form.
			if ( $errflag > 0 && 'update' == $action ) {
				$action = 'edit';
			}

			// insert new tippgroup into database.
			if ( 0 == $errflag && 'savenew' == $action ) {
				// @codingStandardsIgnoreStart
				$results = $wpdb->query(
					$wpdb->prepare(
						'insert into %i values (0, %s, %s, %s, %d);',
						$cs_tippgroup,
						$pteam_name,
						$pteam_shortname,
						$pteam_icon,
						$ppenalty
					)
				);
				// @codingStandardsIgnoreEnd

				if ( 1 == $results ) {
					admin_message( __( 'created tip group.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}

			// update tippgruppe.
			if ( 0 == $errflag && 'update' == $action ) {
				// @codingStandardsIgnoreStart
				$results = $wpdb->query(
					$wpdb->prepare(
						'update %i set name=%s, shortname=%s, icon=%s, penalty=%d where tgid=%d;',
						$cs_tippgroup,
						$pteam_name,
						$pteam_shortname,
						$pteam_icon,
						$ppenalty,
						$ptgid
					)
				);
				// @codingStandardsIgnoreEnd

				if ( 1 == $results ) {
					admin_message( __( 'Tip group successfully saved.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting', 'wp-championship' ) );
				}
			}
		}

		// remove data from database.
		if ( 'remove' == $action ) {
			$gtid = isset( $_GET['tgid'] ) ? intval( $_GET['tgid'] ) : -1;
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( 'delete from %i where tgid=%d;', $cs_tippgroup, $gtid ) );

			if ( 1 == $results ) {
				admin_message( __( 'Tip group deleted.', 'wp-championship' ) );
			} else {
				admin_message( __( 'Database error, aborting', 'wp-championship' ) );
			}
		}
		// output teams add/modify form.
		$resed = array(
			'name'      => '',
			'shortname' => '',
			'icon'      => '',
			'penalty'   => 0,
		);

		if ( 'edit' == $action ) {
			// select data to modify.
			$gtid = isset( $_GET['tgid'] ) ? intval( $_GET['tgid'] ) : -1;
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$resed = $wpdb->get_row( $wpdb->prepare( 'select * from %i where tgid=%d;', $cs_tippgroup, $gtid ), ARRAY_A );
		}

		// build form ==========================================================.

		$out = '';

		// select header for update or add tippgroup.
		if ( 'edit' == $action ) {
			$out .= '<div class="wrap"><h2>' . __( 'Change tip group', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="modifyteam" id="modifyteam" method="post" action="#"><input type="hidden" name="action" value="modifyteam" /><input type="hidden" name="tgid" value="' . $resed['tgid'] . '" />' . "\n";
		} else {
			$out .= '<div class="wrap"><h2>' . __( 'Add tip group', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="addteam" id="addteam" method="post" action="#"><input type="hidden" name="action" value="addteam" />' . "\n";
		}
		$out .= '<input name="wpc_nonce_tippgroup" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_tippgroup' ) . '" />';
		$out .= '<table class="editform" style="width:100%"><tr>';
		$out .= '<th style="width:33%" scope="row" ><label for="team_name">' . __( 'Name', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><input name="team_name" id="team_name" type="text" value="' . $resed['name'] . '" size="40" onblur="calc_shortname();" /></td></tr>' . "\n";
		$out .= '<th style="width:33%" scope="row" ><label for="team_shortname">' . __( 'Shortname', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><input name="team_shortname" id="team_shortname" type="text" value="' . $resed['shortname'] . '" size="5" maxlength="5" /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="team_icon">' . __( 'Symbol / emblem', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td><input name="team_icon" id="team_icon" type="text" value="' . $resed['icon'] . '" size="40" /></td></tr>' . "\n";

		// Penalty feld.
		$out .= '<tr><th scope="row" ><label for="penalty">' . __( 'Penalty', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="penalty" id="penalty" class="postform" tyoe="text" size="3" maxlength="3" value="' . $resed['penalty'] . '">' . "\n";
		$out .= '</td></tr>';
		$out .= '</table>' . "\n";
		// add submit button to form.

		if ( 'edit' == $action ) {
			$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save tip group', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		} else {
			$out .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Add tip group', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		}
		echo wp_kses( $out, wpc_allowed_tags() );

		// output tippgroup table.
		$out  = '';
		$out .= '<div class="wrap">';
		$out .= '<h2>' . __( 'Tip groups', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class=\"widefat\"><thead><tr>\n";
		$out .= '<th scope="col" style="text-align: center">ID</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Name', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Shortname', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Symbol / emblem', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center;width:90px">' . __( 'Number of players', 'wp-championship' ) . '</th>' . "\n";
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
		$results = $wpdb->get_results( $wpdb->prepare( 'select * from %i where name not like %s order by tgid;', $cs_tippgroup, '#%' ) );

		foreach ( $results as $res ) {
			$out .= "<tr><td style='text-align:center'>" . $res->tgid . '</td><td>' . $res->name . '</td>';
			$out .= '<td>' . $res->shortname . '</td>';
			$out .= "<td><img src='" . $iconpath . $res->icon . "' alt='icon' />" . $res->icon . '</td>';
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r5   = $wpdb->get_results( $wpdb->prepare( 'select count(*) as anz from %i where tippgroup=%s;', $cs_users, $res->tgid ) );
			$out .= "<td style='text-align:center'>" . $r5[0]->anz . '</td>';
			$out .= "<td style='text-align:center'>" . $res->penalty . '</td>';
			$out .= "<td style='text-align:center'><a href=\"" . $thisform . '&amp;delnonce=' . wp_create_nonce( 'wpc_nonce_tippgroup' ) . '&amp;action=modify&amp;tgid=' . $res->tgid . '">' . __( 'Update', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;';
			$out .= '<a href="' . $thisform . '&amp;delnonce=' . wp_create_nonce( 'wpc_nonce_tippgroup' ) . '&amp;action=remove&amp;tgid=' . $res->tgid . '">' . __( 'Delete', 'wp-championship' ) . "</a></td></tr>\n";
		}
		$out .= '</table></div>' . "\n";
		echo wp_kses( $out, wpc_allowed_tags() );
	}
}


