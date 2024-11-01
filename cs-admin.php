<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2006-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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
if ( ! function_exists( 'cs_admin' ) ) { // make it pluggable.
	/**
	 * Function for the admin maindialog.
	 */
	function cs_admin() {
		include 'globals.php';
		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin.php';
		// get options and define group ids.
		$groupstr = 'ABCDEFGHIJKLM';
		// get sql object.
		global $wbdb;
		// find out what we have to do.
		$action = '';

		if ( isset( $_POST['update'] ) ) {
			$action = 'update';
		} elseif ( isset( $_POST['deltipps'] ) ) {
			$action = 'deltipps';
		} elseif ( isset( $_POST['delresults'] ) ) {
			$action = 'delresults';
		} elseif ( isset( $_POST['deltables'] ) ) {
			$action = 'deltables';
		} elseif ( isset( $_POST['mailservice1'] ) ) {
			$action = 'mailservice1';
		} elseif ( isset( $_POST['newcalc1'] ) ) {
			$action = 'newcalc1';
		}

		// check nonce.
		if ( '' != $action ) {
			if ( ! isset( $_POST['wpc_nonce'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce'] ) ), 'wpc_nonce' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page." );
			}
		}

		// update options.
		//
		$errflag = 0;

		if ( 'update' == $action ) {
			// check form contents for mandatory fields and/or set default values.
			$manfields = array( 'cs_group_teams', 'cs_pts_winner', 'cs_pts_looser', 'cs_pts_deuce', 'cs_pts_champ', 'cs_pts_tipp', 'cs_pts_tendency', 'cs_pts_supertipp', 'cs_pts_oneside', 'cs_goalsum', 'cs_pts_goalsum' );
			foreach ( $manfields as $mf ) {
				if ( ! isset( $_POST[ $mf ] ) || '' == $_POST[ $mf ] ) {
					$errflag = 1;
				}
			}

			// send a message about mandatory data.
			if ( 1 == $errflag ) {
				admin_message( __( 'Please fill in all fields.', 'wp-championship' ) );
			}

			// update settings.
			if ( 0 == $errflag && 'update' == $action ) {
				$pnarr = array(
					'cs_groups',
					'cs_pts_winner',
					'cs_pts_looser',
					'cs_pts_deuce',
					'cs_final_teams',
					'cs_pts_tipp',
					'cs_pts_tendency',
					'cs_pts_supertipp',
					'cs_pts_champ',
					'cs_pts_oneside',
					'cs_oneside_tendency',
					'cs_goalsum',
					'cs_goalsum_auto',
					'cs_pts_goalsum',
					'cs_group_teams',
					'cs_stellv_schalter',
					'cs_modus',
					'cs_reminder',
					'cs_reminder_hours',
					'cs_floating_link',
					'cs_lock_round1',
					'cs_rank_trend',
					'cs_xmlrpc',
					'cs_xmlrpc_shortname',
					'cs_xmlrpc_alltipps',
					'cs_xmlrpc_news',
					'cs_newuser_auto',
					'cs_hovertable',
					'cs_goalsum_equal',
					'cs_final_winner',
					'cs_number_of_tippdays',
					'cs_use_tippgroup',
					'cs_joker_idlist',
					'cs_joker_player',
				);

				foreach ( $pnarr as $pname ) {
					// these are the only text field.
					if ( 'cs_xmlrpc_news' == $pname ) {
						$fv = ( isset( $_POST['cs_xmlrpc_news'] ) ? sanitize_text_field( wp_unslash( $_POST['cs_xmlrpc_news'] ) ) : '' );
						update_option( 'cs_xmlrpc_news', $fv );
					} elseif ( 'cs_joker_idlist' == $pname ) {
						$fv = ( isset( $_POST['cs_joker_idlist'] ) ? sanitize_text_field( wp_unslash( $_POST['cs_joker_idlist'] ) ) : '' );
						update_option( 'cs_joker_idlist', $fv );
					} else { // all integers.
						if ( ! isset( $_POST[ $pname ] ) ) {
							$_POST[ $pname ] = 0;
						}
						update_option( $pname, intval( $_POST[ $pname ] ) );
					}
				}
				admin_message( __( 'Settings successfully saved.', 'wp-championship' ) );
			}
		}

		if ( 'deltipps' == $action && isset( $_POST['deltipps_ok'] ) ) {
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( "update %i set champion=-1, championtime='1900-01-01 00:00';", $cs_users ) );
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( 'delete from  %i where 1=1;', $cs_tipp ) );
			admin_message( __( 'All tips deleted', 'wp-championship' ) );
		}

		if ( 'delresults' == $action && isset( $_POST['delresults_ok'] ) ) {
			// Ergebnisse  entfernen.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( 'update %i set result1=-1, result2=-1, winner=-1;', $cs_match ) );
			// Pseudo ids wieder aktivieren.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( "update %i set tid1=ptid1, tid2=ptid2 where round='F';", $cs_match ) );
			// manuelle platzierungen entfernen.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( 'update  %i set qualified=0 where qualified <>0;', $cs_team ) );
			admin_message( __( 'All results deleted', 'wp-championship' ) );
		}

		if ( 'deltables' == $action && isset( $_POST['deltables_ok'] ) ) {
			// Tabellen  entfernen.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( 'drop table %i, %i, %i, %i, %i;', $cs_users, $cs_match, $cs_team, $cs_tipp, $cs_tippgroup ) );
			admin_message( __( 'All wp-championship tables deleted', 'wp-championship' ) );
		}

		if ( 'mailservice1' == $action && isset( $_POST['mailservice_ok'] ) ) {
				cs_mailservice();
				cs_mailservice2();
				cs_mailservice_tippgroup();
				admin_message( __( 'Emails were sent.', 'wp-championship' ) );
		}

		if ( 'newcalc1' == $action && isset( $_POST['newcalc_ok'] ) ) {
			// punkt nach eingabe neu berechnen.
			cs_calc_points();
			// finalrunde eintraege aktualisieren.
			cs_update_finals();
			admin_message( __( 'Recalculating is done.', 'wp-championship' ) );
		}
		// load options.
		$cs_groups             = get_option( 'cs_groups' );
		$cs_pts_winner         = get_option( 'cs_pts_winner' );
		$cs_pts_looser         = get_option( 'cs_pts_looser' );
		$cs_pts_deuce          = get_option( 'cs_pts_deuce' );
		$cs_final_teams        = get_option( 'cs_final_teams' );
		$cs_pts_tipp           = get_option( 'cs_pts_tipp' );
		$cs_pts_tendency       = get_option( 'cs_pts_tendency' );
		$cs_pts_supertipp      = get_option( 'cs_pts_supertipp' );
		$cs_pts_champ          = get_option( 'cs_pts_champ' );
		$cs_pts_oneside        = get_option( 'cs_pts_oneside' );
		$cs_oneside_tendency   = get_option( 'cs_oneside_tendency' );
		$cs_goalsum            = get_option( 'cs_goalsum' );
		$cs_goalsum_auto       = get_option( 'cs_goalsum_auto' );
		$cs_pts_goalsum        = get_option( 'cs_pts_goalsum' );
		$cs_group_teams        = get_option( 'cs_group_teams' );
		$cs_stellv_schalter    = get_option( 'cs_stellv_schalter' );
		$cs_modus              = get_option( 'cs_modus' );
		$cs_reminder           = get_option( 'cs_reminder' );
		$cs_reminder_hours     = get_option( 'cs_reminder_hours' );
		$cs_floating_link      = get_option( 'cs_floating_link' );
		$cs_lock_round1        = get_option( 'cs_lock_round1' );
		$cs_rank_trend         = get_option( 'cs_rank_trend' );
		$cs_xmlrpc             = get_option( 'cs_xmlrpc' );
		$cs_xmlrpc_alltipps    = get_option( 'cs_xmlrpc_alltipps' );
		$cs_xmlrpc_shortname   = get_option( 'cs_xmlrpc_shortname' );
		$cs_xmlrpc_news        = get_option( 'cs_xmlrpc_news' );
		$cs_newuser_auto       = get_option( 'cs_newuser_auto' );
		$cs_hovertable         = get_option( 'cs_hovertable' );
		$cs_goalsum_equal      = get_option( 'cs_goalsum_equal' );
		$cs_final_winner       = get_option( 'cs_final_winner' );
		$cs_number_of_tippdays = get_option( 'cs_number_of_tippdays' );
		$cs_use_tippgroup      = get_option( 'cs_use_tippgroup' );
		$cs_joker_idlist       = get_option( 'cs_joker_idlist' );
		$cs_joker_player       = get_option( 'cs_joker_player' );

		// build form.
		$out = '';
		// general options form.
		$out .= '<div class="wrap"><h2>' . __( 'wp-championship settings', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
		$out .= tl_add_supp();
		// add import/export link.
		$out .= '<div style="text-align:right;padding-bottom:10px;">';
		if ( class_exists( 'SOAPClient' ) ) {
			$out .= '<a class="button-secondary thickbox" href="' . site_url() . '/wp-admin/admin-ajax.php?action=wpc_openligadbimport&amp;height=700&amp;width=550&amp;fn=match" >' . __( 'OpenLigaDB Import', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;' . "\n";
		} else {
			$out .= '<a class="button-secondary" style="color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none;" href="#" title="' . __( 'Class SOAPClient not found', 'wp-championship' ) . '" >' . __( 'OpenLigaDB Import', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;' . "\n";
		}
		$out .= '<a class="button-secondary thickbox" href="' . site_url() . '/wp-admin/admin-ajax.php?action=wpc_import&amp;height=700&amp;width=550&amp;fn=match" >' . __( 'Data Import', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;' . "\n";
		$out .= '<a class="button-secondary thickbox" href="' . site_url() . '/wp-admin/admin-ajax.php?action=wpc_export&amp;height=700&amp;width=550&amp;fn=match" >' . __( 'Data Export', 'wp-championship' ) . '</a>&nbsp;&nbsp;&nbsp;</div>' . "\n";
		$out .= '<form name="options" id="options" method="post" action="#"><input type="hidden" name="action" value="update" />' . "\n";
		// add nonce.
		$out .= '<input name="wpc_nonce" type="hidden" value="' . wp_create_nonce( 'wpc_nonce' ) . '" />';
		$out .= '<table class="editform" ><tr>';
		$out .= '<th style="width:30%" scope="row" ><label for="cs_groups">' . __( 'Number of groups in preliminary', 'wp-championship' ) . ':</label></th>' . "\n";
		// number of group box.
		$out .= '<td><select name="cs_groups" id="cs_groups" class="postform">' . "\n";

		for ( $i = 1;$i < 13;$i++ ) {
			$out .= '<option value="' . $i . '"';

			if ( $i == $cs_groups ) {
				$out .= ' selected="selected"';
			}
			$out .= '>' . $i . '</option>';
		}
		$out .= '</select></td>' . "\n";
		// bestätigungs feld um die neuberechnung auszuloesen.
		$out .= '<th scope="row" ><label for="newcalc_ok">' . __( 'Recalculate points and placement?', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="newcalc_ok" id="newcalc_ok" type="checkbox" value="1"  />';
		// button zum ausloesen der neuberechnung.
		$out .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="newcalc1" class="button" value="' . __( 'Recalculate', 'wp-championship' ) . ' &raquo;" /></td></tr>' . "\n";
		// points for winning team.
		$out .= '<tr><th scope="row" ><label for="cs_pts_winner">' . __( 'Points for the winner of a match', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_pts_winner" id="cs_pts_winner" type="text" value="' . $cs_pts_winner . '" size="3" /></td>' . "\n";
		// bestätigungs feld um die mailservice auszuloesen.
		$out .= '<th scope="row" ><label for="mailservice_ok">' . __( 'Send emails once?', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="mailservice_ok" id="mailservice_ok" type="checkbox" value="1"  />';
		// button zum ausloesen des mailservice.
		$out .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="mailservice1" class="button" value="' . __( 'Send emails', 'wp-championship' ) . ' &raquo;" /></td></tr>' . "\n";
		// points for loosing team.
		$out .= '<tr><th scope="row" ><label for="cs_pts_looser">' . __( 'Points for the looser of a match', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_pts_looser" id="cs_pts_looser" type="text" value="' . $cs_pts_looser . '" size="3" /></td>' . "\n";
		// bestätigungs feld um die tipps zu löschen.
		$out .= '<th scope="row" ><label for="deltipps_ok">' . __( 'Delete all tips?', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="deltipps_ok" id="deltipps_ok" type="checkbox" value="1"  />';
		// button zum loeschen der tipps.
		$out .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="deltipps" class="button" value="' . __( 'Delete tips', 'wp-championship' ) . ' &raquo;" /></td></tr>' . "\n";
		// points for deuce.
		$out .= '<tr><th scope="row" ><label for="cs_pts_deuce">' . __( 'Points for draw', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_pts_deuce" id="cs_pts_deuce" type="text" value="' . $cs_pts_deuce . '" size="3" /></td>' . "\n";
		// bestätigungsfeld um die ergebnisse zu löschen.
		$out .= '<th scope="row" ><label for="delresults_ok">' . __( 'Delete all results?', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="delresults_ok" id="delresults_ok" type="checkbox" value="1"  />';
		// button zum loeschen der ergebnisse.
		$out .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="delresults" class="button" value="' . __( 'Delete results', 'wp-championship' ) . ' &raquo;" /></td></tr>' . "\n";
		// number of teams from each group joining finalround.
		$out .= '<tr><th scope="row" ><label for="cs_group_teams">' . __( 'Number of teams per group qualifying for the finals', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_group_teams" id="cs_group_teams" type="text" value="' . $cs_group_teams . '" size="3" /></td>' . "\n";
		// bestätigungsfeld um die tabellen zu löschen.
		$out .= '<th scope="row" ><label for="deltables_ok">' . __( 'Remove all wp-chmpionship tables from database?', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="deltables_ok" id="deltables_ok" type="checkbox" value="1"  />';
		// button zum loeschen der tabellen.
		$out .= '&nbsp;&nbsp;&nbsp;<input type="submit" name="deltables" class="button" value="' . __( 'Remove database tables', 'wp-championship' ) . ' &raquo;" /></td></tr>' . "\n";
		// points for wright tipp.
		$out .= '<tr><th scope="row" ><label for="cs_pts_tipp">' . __( 'Points for exact tip', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_pts_tipp" id="cs_pts_tipp" type="text" value="' . $cs_pts_tipp . '" size="3" /></td>' . "\n";
		// add new users to guessing game automatically.
		$out .= '<th scope="row" ><label for="cs_newuser_auto">' . __( 'Add new users automatically', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_newuser_auto" id="cs_newuser_auto" type="checkbox" value="1"  ';

		if ( $cs_newuser_auto > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>';
		// points for tendency.
		$out .= '<tr><th scope="row" ><label for="cs_pts_tendency">' . __( 'Points for correct tendency', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_pts_tendency" id="cs_pts_tendency" type="text" value="' . $cs_pts_tendency . '" size="3" /></td>' . "\n";
		// schalter fuer stellvertreterfunktion.
		$out .= '<th scope="row" ><label for="cs_stellv_schalter">' . __( 'Deactivate proxy feature', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_stellv_schalter" id="cs_stellv_schalter" type="checkbox" value="1"  ';

		if ( $cs_stellv_schalter > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>';
		// points for supertipp.
		$out .= '<tr><th scope="row" ><label for="cs_pts_supertipp">' . __( 'Points for correct tendency and goal difference', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_pts_supertipp" id="cs_pts_supertipp" type="text" value="' . $cs_pts_supertipp . '" size="3" /></td>' . "\n";
		// turniermodus.
		$out .= '<th scope="row" ><label for="cs_modus">' . __( 'tournament mode', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><select name="cs_modus" id="cs_modus" class="postform">' . "\n";
		$out .= '<option value="1"';

		if ( 1 == $cs_modus ) {
			$out .= ' selected="selected"';
		}
		$out .= '>' . __( 'default', 'wp-championship' ) . '</option>';
		$out .= '<option value="2"';

		if ( 2 == $cs_modus ) {
			$out .= ' selected="selected"';
		}
		$out .= '>' . __( 'German Bundesliga', 'wp-championship' ) . '</option>';
		$out .= '</select></td></tr>' . "\n";
		// field for champion tipp points.
		$out .= '<tr><th scope="row" ><label for="cs_pts_champ">' . __( 'Points for the correct championship winner tip', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_pts_champ" id="cs_pts_champ" type="text" value="' . $cs_pts_champ . '" size="3" /></td>' . "\n";
		// schalter fuer erinnerungsfunktion.
		$out .= '<th scope="row" ><label for="cs_reminder">' . __( 'Reminder mail service', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_reminder" id="cs_reminder" type="checkbox" value="1"  ';

		if ( $cs_reminder > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>';
		// field for correct one side tipp points.
		$out .= '<tr><th scope="row" ><label for="cs_pts_oneside">' . __( 'Points for one-sided correct tip', 'wp-championship' ) . ':</label>' . "\n";
		// oneside tipp hits only if tendency is correct.
		$out .= '<br /><label  style="font-size: 9px;" for="cs_oneside_tendency">' . __( 'Onesided tip strikes ', 'wp-championship' ) . ':</label>' . "\n";
		// $out .= '<input name="cs_oneside_tendency" id="cs_oneside_tendency" type="checkbox" value="1" ';
		$csotsel0 = ( 0 == $cs_oneside_tendency ? 'selected="selected" ' : '' );
		$csotsel1 = ( 1 == $cs_oneside_tendency ? 'selected="selected" ' : '' );
		$csotsel2 = ( 2 == $cs_oneside_tendency ? 'selected="selected" ' : '' );
		$out     .= '<select  style="font-size: 9px;" id="cs_oneside_tendency" class="postform" name="cs_oneside_tendency">';
		$out     .= '<option ' . $csotsel0 . 'value="0">' . __( 'always', 'wp-championship' ) . '</option>';
		$out     .= '<option ' . $csotsel1 . 'value="1">' . __( 'only with tendency', 'wp-championship' ) . '</option>';
		$out     .= '<option ' . $csotsel2 . 'value="2">' . __( 'only without tendency', 'wp-championship' ) . '</option>';
		$out     .= '</select>';

		$out .= '<br /></th>' . "\n";
		$out .= '<td ><input name="cs_pts_oneside" id="cs_pts_oneside" type="text" value="' . $cs_pts_oneside . '" size="3" /></td>' . "\n";
		// wert wie lang vor dem spiel erinnert wird.
		$out .= '<th scope="row" ><label for="cs_reminder_hours">' . __( 'hours till the match starts (tip-reminder).', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_reminder_hours" id="cs_reminder_hours" type="text" value="' . $cs_reminder_hours . '" size="3" /></td></tr>' . "\n";
		// field for min goal sum to get points.
		$out .= '<tr><th scope="row" ><label for="cs_goalsum">' . __( 'Threshold for sum of goals', 'wp-championship' ) . ':</label>' . "\n";
		// oneside tipp not as separate tip but from summ of tipp goals.
		$out .= '<br /><label style="font-size: 9px;" for="cs_goalsum_auto">' . __( 'no separate goal tip', 'wp-championship' ) . ':</label>' . "\n";
		$out .= '<input name="cs_goalsum_auto" id="cs_goalsum_auto" type="checkbox" value="1" ';

		if ( $cs_goalsum_auto > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/>' . "\n";
		$out .= '<br /><label style="font-size: 9px;" for="cs_goalsum_equal">' . __( 'only draw strikes', 'wp-championship' ) . ':</label>' . "\n";
		$out .= '<input name="cs_goalsum_equal" id="cs_goalsum_equal" type="checkbox" value="1" ';

		if ( $cs_goalsum_equal > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/><br /></th>' . "\n";
		$out .= '<td ><input name="cs_goalsum" id="cs_goalsum" type="text" value="' . $cs_goalsum . '" size="3" /></td>' . "\n";
		// switch to activate/deactivate ranking trend.
		$out .= '<th scope="row" ><label for="cs_rank_trend">' . __( 'Recalculate placement tendency', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_rank_trend" id="cs_rank_trend" type="checkbox" value="1"  ';

		if ( $cs_rank_trend > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>' . "\n";
		// Sieger des Finales festlegen (z.B. wenn man nur auf 90 Minuten tippt).

		// teamliste fuer select aufbauen.
		$team1_select_html = '';
		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$results1           = $wpdb->get_results( $wpdb->prepare( 'select tid,name from %i where name not like %s order by name;', $cs_team, '#%' ) );
		$team1_select_html .= "<option value='-1'>-</option>";

		foreach ( $results1 as $res ) {
			$team1_select_html .= "<option value='" . $res->tid . "' ";

			if ( $res->tid == $cs_final_winner ) {
				$team1_select_html .= "selected='selected'";
			}
			$team1_select_html .= '>' . $res->name . "</option>\n";
		}
		$out .= '<tr><th>&nbsp;</th><td>&nbsp;</td><th scope="row" ><label for="cs_final_winner">' . __( 'Championship winner', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><select id="cs_final_winner" name="cs_final_winner" >' . $team1_select_html . '</select></td></tr>';
		// field for high goal sum tipp.
		$out .= '<tr><th scope="row" ><label for="cs_pts_goalsum">' . __( 'Points for sum of goals is greater than threshold', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_pts_goalsum" id="cs_pts_goalsum" type="text" value="' . $cs_pts_goalsum . '" size="3" /></td>' . "\n";
		// switch to activate/deactivate floating link.
		$out .= '<th scope="row" ><label for="cs_floating_link">' . __( 'Activate floating link', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_floating_link" id="cs_floating_link" type="checkbox" value="1"  ';

		if ( $cs_floating_link > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>' . "\n";

		// joker feature - list of match ids to get double points for.
		$out .= '<tr><th scope="row" ><label for="cs_joker_idlist">' . __( 'Joker - List of Match-Ids to get doubled points for', 'wp-championship' ) . ':</label>' . "\n";
		$out .= '<br /><span style="font-size: 9px;">' . __( 'Leave empty to disable, comma separated list of Match-Ids', 'wp-championship' ) . '</span></th>' . "\n";
		$out .= '<td ><input name="cs_joker_idlist" id="cs_joker_idlist" type="text" value="' . $cs_joker_idlist . '" size="15" /></td>' . "\n";

		// switch to activate/deactivate bubble group table when hovering over the group id.
		$out .= '<th scope="row" ><label for="cs_hovertable">' . __( 'Show Tip-Hints in tip form', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_hovertable" id="cs_hovertable" type="checkbox" value="1"  ';

		if ( $cs_hovertable > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>' . "\n";

		// joker feature for players - enter number of jokers each player can set.
		$out .= '<tr><th scope="row" ><label for="cs_joker_idlist">' . __( 'Joker - Number of Jokers each player can use', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_joker_player" id="cs_joker_player" type="text" value="' . $cs_joker_player . '" size="3" /></td>' . "\n";

		// number of days to show in tipp dialog.
		$out .= '<th scope="row" ><label for="cs_number_of_tippdays">' . __( 'Number of days to display', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_number_of_tippdays" id="cs_number_of_tippdays" type="text" value="' . $cs_number_of_tippdays . '" size="3"  ';
		$out .= '/></td></tr>' . "\n";

		// switch to lock round1.
		$out .= '<tr><td colspan="2">&nbsp;</td><th scope="row" ><label for="cs_lock_round1">' . __( 'Lock preliminary tips', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_lock_round1" id="cs_lock_round1" type="checkbox" value="1"  ';
		if ( $cs_lock_round1 > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>' . "\n";

		// tippgroup switch.
		$out .= '<tr><td colspan="2">&nbsp;</td><th scope="row" ><label for="cs_use_tippgroup">' . __( 'Use tip groups:', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><input name="cs_use_tippgroup" id="cs_use_tippgroup" type="checkbox" value="1"  ';
		if ( $cs_use_tippgroup > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></td></tr>' . "\n";

		// Schalter für XMLRPC Erweiterungen.
		$out .= '<tr><th scope="row" ><label for="cs_xmlrpc">' . __( 'Activate XMLRPC extension', 'wp-championship' ) . ':</label>' . "\n";
		// XMLRPC Parameter: Alle Tipps anzeigen und Shortnames verwenden.
		$out .= '<br /><label style="font-size: 9px;" for="cs_xmlrpc_alltipps">' . __( 'Show tips of all players', 'wp-championship' ) . ':</label>' . "\n";
		$out .= '<input name="cs_xmlrpc_alltipps" id="cs_xmlrpc_alltipps" type="checkbox" value="1" ';

		if ( $cs_xmlrpc_alltipps > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= ' />';
		$out .= '<br /><label style="font-size: 9px;" for="cs_xmlrpc_shortname">' . __( 'Use short name', 'wp-championship' ) . ':</label>' . "\n";
		$out .= '<input name="cs_xmlrpc_shortname" id="cs_xmlrpc_shortname" type="checkbox" value="1" ';

		if ( $cs_xmlrpc_shortname > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= '/></th>' . "\n";
		$out .= '<td class="td-admin"><input name="cs_xmlrpc" id="cs_xmlrpc" type="checkbox" value="1"  ';

		if ( $cs_xmlrpc > 0 ) {
			$out .= " checked='checked' ";
		}
		$out .= ' /></td>' . "\n";
		// News Text für XMLRPC.
		$out .= '<th scope="row" ><label for="cs_xmlrpc_news">' . __( 'News to show via XMLRPC', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td ><textarea name="cs_xmlrpc_news" id="cs_xmlrpc_news" cols="25" rows="4"> ' . $cs_xmlrpc_news . '</textarea></td></tr>' . "\n";

		$out .= '</table>' . "\n";
		// add submit button to form.
		$out .= '<p class="submit"><input type="submit" name="update" class="button button-primary" value="' . __( 'Save settings', 'wp-championship' ) . ' &raquo;" /></p>';
		$out .= '</form></div>' . "\n";
		echo wp_kses( $out, wpc_allowed_tags() );
	}
} //make it pluggable.
