<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2007-2018  Hans Matzen  (email : webmaster at tuxlog.de)
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
// function to show and maintain the set of users for wp-championship.
//
if ( ! function_exists( 'cs_admin_users' ) ) {// make it pluggable.
	/**
	 * Function for the user admin dialoh.
	 */
	function cs_admin_users() {
		 include 'globals.php';

		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_users.php';

		// get sql object.
		$wpdb =& $GLOBALS['wpdb'];

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
			if ( ! isset( $_POST['wpc_nonce_users'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_users'] ) ), 'wpc_nonce_users' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		if ( '' != $action && 'remove' == $action ) {
			if ( ! isset( $_GET['delnonce'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['delnonce'] ) ), 'wpc_nonce_users_delete' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		// add or update user data.
		//
		if ( 'savenew' == $action || 'update' == $action ) {
			// sanitise posted values.
			$puser         = ( isset( $_POST['user'] ) ? intval( $_POST['user'] ) : 0 );
			$pisadmin      = ( isset( $_POST['isadmin'] ) ? intval( $_POST['isadmin'] ) : 0 );
			$pmailservice  = ( isset( $_POST['mailservice'] ) ? intval( $_POST['mailservice'] ) : 0 );
			$pmailreceipt  = ( isset( $_POST['mailreceipt'] ) ? intval( $_POST['mailreceipt'] ) : 0 );
			$pstellv       = ( isset( $_POST['stellv'] ) ? intval( $_POST['stellv'] ) : 0 );
			$pchamptipp    = ( isset( $_POST['champtipp'] ) ? intval( $_POST['champtipp'] ) : 0 );
			$ptippgroup    = ( isset( $_POST['tippgroup'] ) ? intval( $_POST['tippgroup'] ) : 0 );
			$ppenalty      = ( isset( $_POST['penalty'] ) ? intval( $_POST['penalty'] ) : 0 );
			$pmailformat   = ( isset( $_POST['mailformat'] ) ? intval( $_POST['mailformat'] ) : 0 );
			$phidefinmatch = ( isset( $_POST['hidefinmatch'] ) ? intval( $_POST['hidefinmatch'] ) : 0 );

			// insert new user into database.
			if ( 'savenew' == $action ) {
				$anz = $wpdb->get_var(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						'select count(*) as anz from %i where userid=%d',
						$cs_users,
						$puser
					)
				);

				if ( 0 == $anz ) {
					$results = $wpdb->query(
						$wpdb->prepare(
							// The placeholder ignores can be removed when %i is supported by WPCS.
							// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							"insert into %i values (%d, %d, %d, %d, %d, %d,'1900-01-01 00:00:00',-1,%s,%d, %d, %d,'')",
							$cs_users,
							$puser,
							$pisadmin,
							$pmailservice,
							$pmailreceipt,
							$pstellv,
							$pchamptipp,
							$ptippgroup,
							$ppenalty,
							$pmailformat,
							$phidefinmatch
						)
					);

					if ( 1 == $results ) {
						admin_message( __( 'Added player.', 'wp-championship' ) );
					} else {
						admin_message( __( 'Database error, aborting.', 'wp-championship' ) );
					}
				} else {
					admin_message( __( 'Player already in the database.', 'wp-championship' ) );
				}
			}

			// update users.
			if ( 'update' == $action ) {
				$results = $wpdb->query(
					$wpdb->prepare(
						// The placeholder ignores can be removed when %i is supported by WPCS.
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						'update %i set admin=%d, mailservice=%d, mailreceipt=%d, stellvertreter=%d, champion=%d, tippgroup=%s, penalty=%d, mailformat=%d , hidefinmatch=%d where userid=%d',
						$cs_users,
						$pisadmin,
						$pmailservice,
						$pmailreceipt,
						$pstellv,
						$pchamptipp,
						$ptippgroup,
						$ppenalty,
						$pmailformat,
						$phidefinmatch,
						$puser
					)
				);

				if ( 1 == $results ) {
					admin_message( __( 'Player successfully saved.', 'wp-championship' ) );
				} else {
					admin_message( __( 'Database error, aborting.', 'wp-championship' ) );
				}
			}
		}

		// remove data from database.
		if ( 'remove' == $action ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->query( $wpdb->prepare( 'delete from %i where userid=%d', $cs_users, isset( $_GET['userid'] ) ? intval( $_GET['userid'] ) : -1 ) );
			if ( $results >= 1 ) {
				admin_message( __( 'Player deleted.', 'wp-championship' ) );
			} else {
				admin_message( __( 'Database error, aborting.', 'wp-championship' ) );
			}
		}

		// output user add/modify form.
		if ( 'edit' == $action ) {
			// select data to modify.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->get_row( $wpdb->prepare( 'select * from  %i where userid=%d', $cs_users, isset( $_GET['userid'] ) ? intval( $_GET['userid'] ) : -1 ) );
		}

		//
		// build form ==========================================================.
		//
		$out = '';

		$champtipp_select_html = '<option value="-1">-</option>';

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( 'select tid,name from %i where name not like %s order by name;', $cs_team, '#%' ) );
		foreach ( $results1 as $res ) {
			$champtipp_select_html .= "<option value='" . $res->tid . "' ";
			if ( isset( $results->champion ) && $res->tid == $results->champion ) {
				$champtipp_select_html .= "selected='selected'";
			}
			$champtipp_select_html .= '>' . $res->name . "</option>\n";
		}

		$stellv_select_html = '<option value="0">-</option>';
		$user_select_html   = '';
		$results1           = $wpdb->get_results(
			$wpdb->prepare(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"select ID, case when display_name != '' then display_name when display_name is null then user_login else user_login end as vdisplay_name from %i order by vdisplay_name;",
				$wp_users
			)
		);

		foreach ( $results1 as $res ) {
			$stellv_select_html .= "<option value='" . $res->ID . "' ";
			if ( isset( $results->stellvertreter ) && $res->ID == $results->stellvertreter ) {
				$stellv_select_html .= "selected='selected'";
			}
			$stellv_select_html .= '>' . $res->vdisplay_name . "</option>\n";

			$user_select_html .= "<option value='" . $res->ID . "' ";
			if ( isset( $results->userid ) && $res->ID == $results->userid ) {
				$user_select_html .= "selected='selected'";
			}
			$user_select_html .= '>' . $res->vdisplay_name . "</option>\n";
		}

		$tippgroup_select_html = '<option value="-1">-</option>';

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1 = $wpdb->get_results( $wpdb->prepare( 'select tgid,name from %i where name not like %s order by name;', $cs_tippgroup, '#%' ) );
		foreach ( $results1 as $res ) {
			$tippgroup_select_html .= "<option value='" . $res->tgid . "' ";
			if ( isset( $results->tippgroup ) && $res->tgid == $results->tippgroup ) {
				$tippgroup_select_html .= "selected='selected'";
			}
			$tippgroup_select_html .= '>' . $res->name . "</option>\n";
		}

		// select header for update or add match.
		if ( 'edit' == $action ) {
			$out .= '<div class="wrap"><h2>' . __( 'Update player', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="modifyuser" id="modifyuser" method="post" action="#"><input type="hidden" name="action" value="modifyuser" /><input type="hidden" name="uid" value="' . $results->userid . '" />' . "\n";
		} else {
			$out .= '<div class="wrap"><h2>' . __( 'Add player', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
			$out .= tl_add_supp();
			$out .= '<form name="adduser" id="adduser" method="post" action="#"><input type="hidden" name="action" value="adduser" />' . "\n";
		}

		// add nonce.
		$out .= '<input name="wpc_nonce_users" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_users' ) . '" />';

		$out .= '<table class="editform" style="width:100%"><tr>';
		$out .= '<th style="width:33%" scope="row" ><label for="user">' . __( 'Player', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td style="width:67%"><select id="user" name="user">' . $user_select_html . '</select></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="hidefinmatch">' . __( 'Hide finished matches', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="hidefinmatch" id="hidefinmatch" type="checkbox" value="1" ' . ( 'edit' == $action && 1 == $results->hidefinmatch ? 'checked="checked"' : '' ) . '  /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="mailservice">' . __( 'Mailservice', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="mailservice" id="mailservice" type="checkbox" value="1" ' . ( 'edit' == $action && 1 == $results->mailservice ? 'checked="checked"' : '' ) . '  /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="mailreceipt">' . __( 'Mailconfirmation', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="mailreceipt" id="mailreceipt" type="checkbox" value="1" ' . ( 'edit' == $action && 1 == $results->mailreceipt ? 'checked="checked"' : '' ) . '  /></td></tr>' . "\n";

		$out .= '<tr><th scope="row" ><label for="mailformat">' . __( 'Mailformat', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= "<td><select id='mailformat' name='mailformat'><option value='0'" . ( 'edit' == $action && 0 == $results->mailformat ? 'selected="selected"' : '' ) . ">HTML</option><option value='1'" . ( 'edit' == $action && 1 == $results->mailformat ? 'selected="selected"' : '' ) . ">Text</option></select></td></tr>\n";

		$out .= '<tr><th scope="row" ><label for="isadmin">' . __( 'Guessing-game admin', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="isadmin" id="isadmin" type="checkbox" value="1" ' . ( 'edit' == $action && '1' == $results->admin ? 'checked="checked"' : '' ) . '  /></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="stellv">' . __( 'Proxy', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td><select id="stellv" name="stellv">' . $stellv_select_html . '</select></td></tr>' . "\n";
		$out .= '<tr><th scope="row" ><label for="champtipp">' . __( 'Winner-tip', 'wp-championship' ) . ' :</label></th>' . "\n";
		$out .= '<td><select id="champtipp" name="champtipp">' . $champtipp_select_html . '</select></td></tr>' . "\n";

		$out .= '<tr><th scope="row" ><label for="tippgroup">' . __( 'Tip Group', 'wp-championship' ) . ':</label></th>' . "\n";
		// $out .= '<td><input name="tippgroup" id="tippgroup" type="text" value="'. (isset($results->tippgroup)?$results->tippgroup:"").'"  /></td></tr>'."\n";
		$out .= '<td><select id="tippgroup" name="tippgroup">' . $tippgroup_select_html . '</select></td></tr>' . "\n";

		$out .= '<tr><th scope="row" ><label for="penalty">' . __( 'Penalty', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="penalty" id="penalty" type="text" value="' . ( isset( $results->penalty ) ? $results->penalty : '' ) . '"  /></td></tr>' . "\n";

		$out .= '</table>' . "\n";

		// add submit button to form.
		if ( 'edit' == $action ) {
			$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save player', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		} else {
			$out .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Add player', 'wp-championship' ) . ' &raquo;" /></p></form></div>' . "\n";
		}

		echo wp_kses( $out, wpc_allowed_tags() );

		//
		// output user table.
		//
		$out  = '';
		$out  = '<div class="wrap">';
		$out .= '<h2>' . __( 'Player', 'wp-championship' ) . "</h2>\n";
		$out .= "<table class=\"widefat\"><thead><tr>\n";
		$out .= '<th scope="col" style="text-align: center">Mitspieler-ID</th>' . "\n";
		$out .= '<th scope="col">' . __( 'Name', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:70px;text-align: center">' . __( 'Guessing-game admin', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:70px;text-align: center">' . __( 'Hide finished matches', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:70px;text-align: center">' . __( 'Mailservice', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:70px;text-align: center">' . __( 'Mailconfirmation', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:70px;text-align: center">' . __( 'Mailformat', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Proxy', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Winner-tip', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Tip Group', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="width:90px;text-align: center">' . __( 'Penalty', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center">' . __( 'Action', 'wp-championship' ) . '</th></tr></thead>' . "\n";

		// user loop.
		// @codingStandardsIgnoreStart
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"select a.*, case when b.display_name != '' then b.display_name when b.display_name is null then b.user_login else b.user_login end 
				as vdisplay_name, b.ID, c.name 
				from %i a inner join %i b on a.userid=b.ID 
				left outer join %i c on a.champion = c.tid 
				order by vdisplay_name;",
				$cs_users,
				$wp_users,
				$cs_team
			)
		);
		// @codingStandardsIgnoreEnd

		// create nonce.
		$delnonce = wp_create_nonce( 'wpc_nonce_users_delete' );

		// loop through users and print table.
		foreach ( $results as $res ) {
			$out .= "<tr><td style='text-align:center'>" . $res->userid . '</td><td>' . $res->vdisplay_name . '</td>';
			$out .= "<td style='text-align:center'>" . $res->admin . '</td>';
			$out .= "<td style='text-align:center'>" . $res->hidefinmatch . '</td>';
			$out .= "<td style='text-align:center'>" . $res->mailservice . "</td><td style='text-align:center'>" . $res->mailreceipt . '</td>';
			$out .= "<td style='text-align:center'>" . ( 0 == $res->mailformat ? 'HTML' : 'Text' ) . '</td>';
			$out .= "<td style='text-align:center'>" . $res->stellvertreter . '</td>';
			$out .= "<td style='text-align:center'>" . ( -1 == $res->champion ? '-' : $res->name ) . '</td>';

			$tgname = '-';
			if ( intval( $res->tippgroup ) > 0 ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$tgname = $wpdb->get_var( $wpdb->prepare( 'select name from %i where tgid=%d', $cs_tippgroup, $res->tippgroup ) );
			}
			$out .= "<td style='text-align:center'>" . $tgname . '</td>';
			$out .= "<td style='text-align:center'>" . ( '' != $res->penalty ? $res->penalty : '-' ) . '</td>';
			$out .= "<td style='text-align:center'><a href=\"" . $thisform . '&amp;action=modify&amp;userid=' . $res->userid . '">' . __( 'Update', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;';
			$out .= "<a href='$thisform&amp;action=remove&amp;userid={$res->userid}&amp;delnonce=$delnonce'>" . __( 'Delete', 'wp-championship' ) . "</a></td></tr>\n";
		}
		$out .= '</table></div>' . "\n";

		echo wp_kses( $out, wpc_allowed_tags() );
	}
}//make it pluggable.
