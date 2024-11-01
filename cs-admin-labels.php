<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2007-2024  Hans Matzen  (email : webmaster at tuxlog.de)
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
// function to show and maintain the labels.
//
if ( ! function_exists( 'cs_admin_labels' ) ) {// make it pluggable.
	/**
	 * Function to display the admin dialog for the labels.
	 */
	function cs_admin_labels() {
		include 'globals.php';

		// base url for links.
		$thisform = 'admin.php?page=wp-championship/cs_admin_labels.php';

		// get sql object.
		global $wbdb;

		$fieldnames = array(
			'cs_label_group',
			'cs_col_group',
			'cs_label_matchid',
			'cs_col_matchid',
			'cs_label_icon1',
			'cs_col_icon1',
			'cs_label_match',
			'cs_col_match',
			'cs_label_icon2',
			'cs_col_icon2',
			'cs_label_location',
			'cs_col_location',
			'cs_label_time',
			'cs_col_time',
			'cs_label_tip',
			'cs_col_tip',
			'cs_label_points',
			'cs_col_points',
			'cs_label_place',
			'cs_col_place',
			'cs_label_player',
			'cs_col_player',
			'cs_label_upoints',
			'cs_col_upoints',
			'cs_label_trend',
			'cs_col_trend',
			'cs_label_championtip',
			'cs_col_championtip',
			'cs_label_steam',
			'cs_col_steam',
			'cs_label_smatch',
			'cs_col_smatch',
			'cs_label_swin',
			'cs_col_swin',
			'cs_label_stie',
			'cs_col_stie',
			'cs_label_sloose',
			'cs_col_sloose',
			'cs_label_sgoal',
			'cs_col_sgoal',
			'cs_label_spoint',
			'cs_col_spoint',
			'cs_tipp_sort',
			'cs_stats4_showall',
			'cs_stats8_sort_average',
			'cs_stats_show_fullnames',
		);

		$xfieldnames = array(
			'csx_label_group',
			'csx_col_group',
			'csx_label_matchid',
			'csx_col_matchid',
			'csx_label_icon1',
			'csx_col_icon1',
			'csx_label_match',
			'csx_col_match',
			'csx_label_icon2',
			'csx_col_icon2',
			'csx_label_location',
			'csx_col_location',
			'csx_label_time',
			'csx_col_time',
			'csx_label_tip',
			'csx_col_tip',
			'csx_label_points',
			'csx_col_points',
			'csx_label_place',
			'csx_col_place',
			'csx_label_player',
			'csx_col_player',
			'csx_label_upoints',
			'csx_col_upoints',
			'csx_label_trend',
			'csx_col_trend',
			'csx_label_championtip',
			'csx_col_championtip',
			'csx_label_steam',
			'csx_col_steam',
			'csx_label_smatch',
			'csx_col_smatch',
			'csx_label_swin',
			'csx_col_swin',
			'csx_label_stie',
			'csx_col_stie',
			'csx_label_sloose',
			'csx_col_sloose',
			'csx_label_sgoal',
			'csx_col_sgoal',
			'csx_label_spoint',
			'csx_col_spoint',
			'csx_tipp_sort',
		);

		// find out what we have to do.
		$action = '';
		if ( isset( $_POST['update'] ) ) {
			$action = 'update';
		} elseif ( isset( $_POST['xupdate'] ) ) {
			$action = 'xupdate';
		}

		// check nonce.
		if ( 'update' == $action ) {
			if ( ! isset( $_POST['wpc_nonce_labels'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_labels'] ) ), 'wpc_nonce_labels' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}
		if ( 'xupdate' == $action ) {
			if ( ! isset( $_POST['wpc_nonce_xlabels'] ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_xlabels'] ) ), 'wpc_nonce_xlabels' ) ) {
				die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
			}
		}

		// update options.
		//
		if ( 'update' == $action ) {
			foreach ( $fieldnames as $fn ) {
				if ( isset( $_POST[ "$fn" ] ) ) {
					update_option( $fn, sanitize_text_field( wp_unslash( $_POST[ "$fn" ] ) ) );
				} else {
					update_option( $fn, '' );
				}
			}
			admin_message( __( 'Settings successfully saved.', 'wp-championship' ) );
		}

		if ( 'xupdate' == $action ) {
			foreach ( $xfieldnames as $fn ) {
				if ( isset( $_POST[ "$fn" ] ) ) {
					update_option( $fn, sanitize_text_field( wp_unslash( $_POST[ "$fn" ] ) ) );
				} else {
					update_option( $fn, '' );
				}
			}
			admin_message( __( 'Settings successfully saved.', 'wp-championship' ) );
		}

		// load options.
		$cs_label_matchid        = get_option( 'cs_label_matchid' );
		$cs_col_matchid          = get_option( 'cs_col_matchid' );
		$cs_label_championtip    = get_option( 'cs_label_championtip' );
		$cs_col_championtip      = get_option( 'cs_col_championtip' );
		$cs_label_group          = get_option( 'cs_label_group' );
		$cs_col_group            = get_option( 'cs_col_group' );
		$cs_label_icon1          = get_option( 'cs_label_icon1' );
		$cs_col_icon1            = get_option( 'cs_col_icon1' );
		$cs_label_match          = get_option( 'cs_label_match' );
		$cs_col_match            = get_option( 'cs_col_match' );
		$cs_label_icon2          = get_option( 'cs_label_icon2' );
		$cs_col_icon2            = get_option( 'cs_col_icon2' );
		$cs_label_location       = get_option( 'cs_label_location' );
		$cs_col_location         = get_option( 'cs_col_location' );
		$cs_label_time           = get_option( 'cs_label_time' );
		$cs_col_time             = get_option( 'cs_col_time' );
		$cs_label_tip            = get_option( 'cs_label_tip' );
		$cs_col_tip              = get_option( 'cs_col_tip' );
		$cs_label_points         = get_option( 'cs_label_points' );
		$cs_col_points           = get_option( 'cs_col_points' );
		$cs_label_place          = get_option( 'cs_label_place', __( 'Rank', 'wp-championship' ) );
		$cs_col_place            = get_option( 'cs_col_place' );
		$cs_label_player         = get_option( 'cs_label_player', __( 'Player', 'wp-championship' ) );
		$cs_col_player           = get_option( 'cs_col_player' );
		$cs_label_upoints        = get_option( 'cs_label_upoints', __( 'Score', 'wp-championship' ) );
		$cs_col_upoints          = get_option( 'cs_col_upoints' );
		$cs_label_trend          = get_option( 'cs_label_trend', __( 'Tendency', 'wp-championship' ) );
		$cs_col_trend            = get_option( 'cs_col_trend' );
		$cs_label_steam          = get_option( 'cs_label_steam', __( 'Team', 'wp-championship' ) );
		$cs_col_steam            = get_option( 'cs_col_steam' );
		$cs_label_smatch         = get_option( 'cs_label_smatch', __( 'Matches', 'wp-championship' ) );
		$cs_col_smatch           = get_option( 'cs_col_smatch' );
		$cs_label_swin           = get_option( 'cs_label_swin', __( 'Wins', 'wp-championship' ) );
		$cs_col_swin             = get_option( 'cs_col_swin' );
		$cs_label_stie           = get_option( 'cs_label_stie', __( 'Draw', 'wp-championship' ) );
		$cs_col_stie             = get_option( 'cs_col_stie' );
		$cs_label_sloose         = get_option( 'cs_label_sloose', __( 'Defeat', 'wp-championship' ) );
		$cs_col_sloose           = get_option( 'cs_col_sloose' );
		$cs_label_sgoal          = get_option( 'cs_label_sgoal', __( 'goals', 'wp-championship' ) );
		$cs_col_sgoal            = get_option( 'cs_col_sgoal' );
		$cs_label_spoint         = get_option( 'cs_label_spoint', __( 'Points', 'wp-championship' ) );
		$cs_col_spoint           = get_option( 'cs_col_spoint' );
		$cs_tipp_sort            = get_option( 'cs_tipp_sort' );
		$cs_stats4_showall       = get_option( 'cs_stats4_showall' );
		$cs_stats8_sort_average  = get_option( 'cs_stats8_sort_average' );
		$cs_stats_show_fullnames = get_option( 'cs_stats_show_fullnames' );

		// Options for XML RPC.
		$csx_label_matchid     = get_option( 'csx_label_matchid' );
		$csx_col_matchid       = get_option( 'csx_col_matchid' );
		$csx_label_championtip = get_option( 'csx_label_championtip' );
		$csx_col_championtip   = get_option( 'csx_col_championtip' );
		$csx_label_group       = get_option( 'csx_label_group' );
		$csx_col_group         = get_option( 'csx_col_group' );
		$csx_label_icon1       = get_option( 'csx_label_icon1' );
		$csx_col_icon1         = get_option( 'csx_col_icon1' );
		$csx_label_match       = get_option( 'csx_label_match' );
		$csx_col_match         = get_option( 'csx_col_match' );
		$csx_label_icon2       = get_option( 'csx_label_icon2' );
		$csx_col_icon2         = get_option( 'csx_col_icon2' );
		$csx_label_location    = get_option( 'csx_label_location' );
		$csx_col_location      = get_option( 'csx_col_location' );
		$csx_label_time        = get_option( 'csx_label_time' );
		$csx_col_time          = get_option( 'csx_col_time' );
		$csx_label_tip         = get_option( 'csx_label_tip' );
		$csx_col_tip           = get_option( 'csx_col_tip' );
		$csx_label_points      = get_option( 'csx_label_points' );
		$csx_col_points        = get_option( 'csx_col_points' );
		$csx_label_place       = get_option( 'csx_label_place', __( 'Rank', 'wp-championship' ) );
		$csx_col_place         = get_option( 'csx_col_place' );
		$csx_label_player      = get_option( 'csx_label_player', __( 'Player', 'wp-championship' ) );
		$csx_col_player        = get_option( 'csx_col_player' );
		$csx_label_upoints     = get_option( 'csx_label_upoints', __( 'Score', 'wp-championship' ) );
		$csx_col_upoints       = get_option( 'csx_col_upoints' );
		$csx_label_trend       = get_option( 'csx_label_trend', __( 'Tendency', 'wp-championship' ) );
		$csx_col_trend         = get_option( 'csx_col_trend' );
		$csx_label_steam       = get_option( 'csx_label_steam', __( 'Team', 'wp-championship' ) );
		$csx_col_steam         = get_option( 'csx_col_steam' );
		$csx_label_smatch      = get_option( 'csx_label_smatch', __( 'Matches', 'wp-championship' ) );
		$csx_col_smatch        = get_option( 'csx_col_smatch' );
		$csx_label_swin        = get_option( 'csx_label_swin', __( 'Wins', 'wp-championship' ) );
		$csx_col_swin          = get_option( 'csx_col_swin' );
		$csx_label_stie        = get_option( 'csx_label_stie', __( 'Draw', 'wp-championship' ) );
		$csx_col_stie          = get_option( 'csx_col_stie' );
		$csx_label_sloose      = get_option( 'csx_label_sloose', __( 'Defeat', 'wp-championship' ) );
		$csx_col_sloose        = get_option( 'csx_col_sloose' );
		$csx_label_sgoal       = get_option( 'csx_label_sgoal', __( 'goals', 'wp-championship' ) );
		$csx_col_sgoal         = get_option( 'csx_col_sgoal' );
		$csx_label_spoint      = get_option( 'csx_label_spoint', __( 'Points', 'wp-championship' ) );
		$csx_col_spoint        = get_option( 'csx_col_spoint' );
		$csx_tipp_sort         = get_option( 'csx_tipp_sort' );

		// build form.
		$out  = '';
		$out .= tl_add_supp();

		// tabs header.
		$out .= '<script type="text/javascript">jQuery(function() { jQuery( "#tabs" ).tabs(); });</script>';
		$out .= '<div id="tabs" class="atabsbody">';
		$out .= '<ul><li><a href="#tabs-1" class="activetab">Web-Bezeichnungen</a></li>';
		$out .= '<li><a href="#tabs-2" class="deactivetab">XMLRPC-Bezeichnungen</a></li>';
		$out .= '</ul><p>&nbsp;</p><div class="tabsbody" id="tabs-1">';

		//
		// für die tabellen Tipp, Statistiken
		// Tippseite:   Vorrundenspiele Finalrunde
		// Spalten Gruppe, Icon 1, Begegnung, Icon2, Ort, Datum/Zeit, Tipp/Ergebnis, Punkte
		// Statistik: Vorrunde Finalrunde Gruppe
		// Mannschaft Spiele Siege Unentschieden Niederlagen Tore Punkte
		// Aktueller Punktestand:
		// Platz, Spieler, Punktestand, Trend
		// Spalte 1-n anzeigen ja/nein, Beschriftung text ggf. mit ohne Icon  nach welcher Spalte standardmäßig sortiert.
		//
		// labels options form.
		$out .= '<div class="wrap"><h2>' . __( 'wp-championship Web-Labels', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
		$out .= '<form name="options" id="options" method="post" action="#"><input type="hidden" name="action" value="update" />' . "\n";
		// add nonce.
		$out .= '<input name="wpc_nonce_labels" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_labels' ) . '" />';

		$out .= '<table class="editform" style="width:100%" >';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Preliminary' ) . '/' . __( 'Finals' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_group">' . __( 'Label column 0 (group)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_group" id="cs_label_group" type="text" value="' . $cs_label_group . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_group" id="cs_col_group" type="checkbox" value="1" ' . ( 1 == $cs_col_group ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . '</td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_matchid">' . __( 'Label column 1 (Match-Id)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_matchid" id="cs_label_matchid" type="text" value="' . $cs_label_matchid . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_matchid" id="cs_col_matchid" type="checkbox" value="1" ' . ( 1 == $cs_col_matchid ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . '</td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_icon1">' . __( 'Label column (Icon 1)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_icon1" id="cs_label_icon1" type="text" value="' . $cs_label_icon1 . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_icon1" id="cs_col_icon1" type="checkbox" value="1" ' . ( 1 == $cs_col_icon1 ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_match">' . __( 'Label column 3 (match)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_match" id="cs_label_match" type="text" value="' . $cs_label_match . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_match" id="cs_col_match" type="checkbox" value="1" ' . ( 1 == $cs_col_match ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_icon2">' . __( 'Label column 4 (icon 2)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_icon2" id="cs_label_icon2" type="text" value="' . $cs_label_icon2 . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_icon2" id="cs_col_icon2" type="checkbox" value="1" ' . ( 1 == $cs_col_icon2 ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_location">' . __( 'Label column 5 (location)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_location" id="cs_label_location" type="text" value="' . $cs_label_location . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_location" id="cs_col_location" type="checkbox" value="1" ' . ( 1 == $cs_col_location ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_time">' . __( 'Label column 6 (date/time)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_time" id="cs_label_time" type="text" value="' . $cs_label_time . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_time" id="cs_col_time" type="checkbox" value="1" ' . ( 1 == $cs_col_time ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_tip">' . __( 'Label column 7 (tip/result)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_tip" id="cs_label_tip" type="text" value="' . $cs_label_tip . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_tip" id="cs_col_tip" type="checkbox" value="1" ' . ( 1 == $cs_col_tip ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_points">' . __( 'Label column 8 (points)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_points" id="cs_label_points" type="text" value="' . $cs_label_points . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_points" id="cs_col_points" type="checkbox" value="1" ' . ( 1 == $cs_col_points ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		// number of group box.
		$out .= '<tr><th scope="row" style="text-align:left">' . __( 'Default sort order by column', 'wp-championship' ) . ':</th><td colspan="2"><select name="cs_tipp_sort" id="cs_tipp_sort" class="postform">' . "\n";
		for ( $i = 1; $i < 9; $i++ ) {
			$out .= '<option value="' . $i . '"';
			if ( $i == $cs_tipp_sort ) {
				$out .= ' selected="selected"';
			}
			$out .= '>' . $i . '</option>';
		}
		$out .= '</select></td></tr>' . "\n";
		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Current score', 'wp-championship' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_place">' . __( 'Label column 1 (placement)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_place" id="cs_label_place" type="text" value="' . $cs_label_place . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_place" id="cs_col_place" type="checkbox" value="1" ' . ( 1 == $cs_col_place ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_player">' . __( 'Label column 2 (player)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_player" id="cs_label_player" type="text" value="' . $cs_label_player . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_player" id="cs_col_player" type="checkbox" value="1" ' . ( 1 == $cs_col_player ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_upoints">' . __( 'Label column 3 (score)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_upoints" id="cs_label_upoints" type="text" value="' . $cs_label_upoints . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_upoints" id="cs_col_upoints" type="checkbox" value="1" ' . ( 1 == $cs_col_upoints ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_trend">' . __( 'Label column 4 (tendency)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_trend" id="cs_label_trend" type="text" value="' . $cs_label_trend . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_trend" id="cs_col_trend" type="checkbox" value="1" ' . ( 1 == $cs_col_trend ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_championtip">' . __( 'Label column 5 (championtip)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_championtip" id="cs_label_championtip" type="text" value="' . $cs_label_championtip . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_championtip" id="cs_col_championtip" type="checkbox" value="1" ' . ( 1 == $cs_col_championtip ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<td>&nbsp;</td></tr>';
		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Stats' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_steam">' . __( 'Label column 1 (team)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_steam" id="cs_label_steam" type="text" value="' . $cs_label_steam . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_steam" id="cs_col_steam" type="checkbox" value="1" ' . ( 1 == $cs_col_steam ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_smatch">' . __( 'Label column 2 (matches)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_smatch" id="cs_label_smatch" type="text" value="' . $cs_label_smatch . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_smatch" id="cs_col_smatch" type="checkbox" value="1" ' . ( 1 == $cs_col_smatch ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_swin">' . __( 'Label column 3 (wins)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_swin" id="cs_label_swin" type="text" value="' . $cs_label_swin . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_swin" id="cs_col_swin" type="checkbox" value="1" ' . ( 1 == $cs_col_swin ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_stie">' . __( 'Label column 4 (draw)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_stie" id="cs_label_stie" type="text" value="' . $cs_label_stie . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_stie" id="cs_col_stie" type="checkbox" value="1" ' . ( 1 == $cs_col_stie ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_sloose">' . __( 'Label column 5 (defeats)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_sloose" id="cs_label_sloose" type="text" value="' . $cs_label_sloose . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_sloose" id="cs_col_sloose" type="checkbox" value="1" ' . ( 1 == $cs_col_sloose ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_sgoal">' . __( 'Label column 6 (goals)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_sgoal" id="cs_label_sgoal" type="text" value="' . $cs_label_sgoal . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_sgoal" id="cs_col_sgoal" type="checkbox" value="1" ' . ( 1 == $cs_col_sgoal ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_label_spoint">' . __( 'Label column 7 (points)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_label_spoint" id="cs_label_spoint" type="text" value="' . $cs_label_spoint . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="cs_col_spoint" id="cs_col_spoint" type="checkbox" value="1" ' . ( 1 == $cs_col_spoint ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_stats4_showall">' . __( 'Show all tips in statistic 4 (cs_stats4)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_stats4_showall" id="cs_stats4_showall" type="checkbox" value="1" ' . ( 1 == $cs_stats4_showall ? 'checked="checked"' : '' ) . ' />&nbsp;</td><td>&nbsp;</td><td></td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_stats8_sort_average">' . __( 'Sort Tippgroup stats by average (cs-stats8)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_stats8_sort_average" id="cs_stats8_sort_average" type="checkbox" value="1" ' . ( 1 == $cs_stats8_sort_average ? 'checked="checked"' : '' ) . ' />&nbsp;</td><td>&nbsp;</td><td></td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="cs_stats_show_fullnames">' . __( 'Show fullnames in ranking table (cs-userstats)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="cs_stats_show_fullnames" id="cs_stats_show_fullnames" type="checkbox" value="1" ' . ( 1 == $cs_stats_show_fullnames ? 'checked="checked"' : '' ) . ' />&nbsp;</td><td>&nbsp;</td><td></td></tr>';

		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '</table>' . "\n";

		// add submit button to form.
		$out .= '<p class="submit"><input type="submit" name="update" value="' . __( 'Save settings', 'wp-championship' ) . ' &raquo;" /></p>';

		$out .= '</form></div>' . "\n";

		$out .= '</div><div id="tabs-2" class="tabsbody">';

		// labels options form.
		$out .= '<div class="wrap"><h2>' . __( 'wp-championship XMLRPC-labels', 'wp-championship' ) . '</h2><div id="ajax-response"></div>' . "\n";
		$out .= '<form name="options" id="options" method="post" action="#"><input type="hidden" name="action" value="update" />' . "\n";
		// add nonce.
		$out .= '<input name="wpc_nonce_xlabels" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_xlabels' ) . '" />';

		$out .= '<table class="editform" style="width:100%" >';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Preliminary' ) . '/' . __( 'Finals' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_group">' . __( 'Label column 0 (group)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_group" id="csx_label_group" type="text" value="' . $csx_label_group . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_group" id="csx_col_group" type="checkbox" value="1" ' . ( 1 == $csx_col_group ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . '</td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_matchid">' . __( 'Label column 1 (Match-Id)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_matchid" id="csx_label_matchid" type="text" value="' . $csx_label_matchid . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_matchid" id="csx_col_matchid" type="checkbox" value="1" ' . ( 1 == $csx_col_matchid ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . '</td></tr>';

		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_icon1">' . __( 'Label column (Icon 1)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_icon1" id="csx_label_icon1" type="text" value="' . $csx_label_icon1 . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_icon1" id="csx_col_icon1" type="checkbox" value="1" ' . ( 1 == $csx_col_icon1 ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_match">' . __( 'Label column 3 (match)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_match" id="csx_label_match" type="text" value="' . $csx_label_match . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_match" id="csx_col_match" type="checkbox" value="1" ' . ( 1 == $csx_col_match ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_icon2">' . __( 'Label column 4 (icon 2)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_icon2" id="csx_label_icon2" type="text" value="' . $csx_label_icon2 . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_icon2" id="csx_col_icon2" type="checkbox" value="1" ' . ( 1 == $csx_col_icon2 ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_location">' . __( 'Label column 5 (location)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_location" id="csx_label_location" type="text" value="' . $csx_label_location . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_location" id="csx_col_location" type="checkbox" value="1" ' . ( 1 == $csx_col_location ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_time">' . __( 'Label column 6 (date/time)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_time" id="csx_label_time" type="text" value="' . $csx_label_time . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_time" id="csx_col_time" type="checkbox" value="1" ' . ( 1 == $csx_col_time ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_tip">' . __( 'Label column 7 (tip/result)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_tip" id="csx_label_tip" type="text" value="' . $csx_label_tip . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_tip" id="csx_col_tip" type="checkbox" value="1" ' . ( 1 == $csx_col_tip ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_points">' . __( 'Label column 8 (points)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_points" id="csx_label_points" type="text" value="' . $csx_label_points . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_points" id="csx_col_points" type="checkbox" value="1" ' . ( 1 == $csx_col_points ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		// number of group box.
		$out .= '<tr><th scope="row" style="text-align:left">' . __( 'Default sort order by column', 'wp-championship' ) . ':</th><td colspan="2"><select name="csx_tipp_sort" id="csx_tipp_sort" class="postform">' . "\n";
		for ( $i = 1; $i < 9; $i++ ) {
			$out .= '<option value="' . $i . '"';
			if ( $i == $csx_tipp_sort ) {
				$out .= ' selected="selected"';
			}
			$out .= '>' . $i . '</option>';
		}
		$out .= '</select></td></tr>' . "\n";
		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Current score' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_place">' . __( 'Label column 1 (placement)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_place" id="csx_label_place" type="text" value="' . $csx_label_place . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_place" id="csx_col_place" type="checkbox" value="1" ' . ( 1 == $csx_col_place ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_player">' . __( 'Label column 2 (player)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_player" id="csx_label_player" type="text" value="' . $csx_label_player . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_player" id="csx_col_player" type="checkbox" value="1" ' . ( 1 == $csx_col_player ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_upoints">' . __( 'Label column 3 (score)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_upoints" id="csx_label_upoints" type="text" value="' . $csx_label_upoints . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_upoints" id="csx_col_upoints" type="checkbox" value="1" ' . ( 1 == $csx_col_upoints ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_icon2">' . __( 'Label column 4 (tendency)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_trend" id="csx_label_trend" type="text" value="' . $csx_label_trend . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_trend" id="csx_col_trend" type="checkbox" value="1" ' . ( 1 == $csx_col_trend ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_championtip">' . __( 'Label column 5 (championtip)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_championtip" id="csx_label_championtip" type="text" value="' . $csx_label_championtip . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_championtip" id="csx_col_championtip" type="checkbox" value="1" ' . ( 1 == $csx_col_championtip ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';

		$out .= '<td>&nbsp;</td></tr>';
		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '<tr><th colspan="3" style="text-align:left" scope="row">' . __( 'Stats' ) . ':</th></tr>' . "\n";

		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_steam">' . __( 'Label column 1 (team)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_steam" id="csx_label_steam" type="text" value="' . $csx_label_steam . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_steam" id="csx_col_steam" type="checkbox" value="1" ' . ( 1 == $csx_col_steam ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_smatch">' . __( 'Label column 2 (matches)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_smatch" id="csx_label_smatch" type="text" value="' . $csx_label_smatch . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_smatch" id="csx_col_smatch" type="checkbox" value="1" ' . ( 1 == $csx_col_smatch ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_swin">' . __( 'Label column 3 (wins)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_swin" id="csx_label_swin" type="text" value="' . $csx_label_swin . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_swin" id="csx_col_swin" type="checkbox" value="1" ' . ( 1 == $csx_col_swin ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_stie">' . __( 'Label column 4 (draw)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_stie" id="csx_label_stie" type="text" value="' . $csx_label_stie . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_stie" id="csx_col_stie" type="checkbox" value="1" ' . ( 1 == $csx_col_stie ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_sloose">' . __( 'Label column 5 (defeats)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_sloose" id="csx_label_sloose" type="text" value="' . $csx_label_sloose . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_sloose" id="csx_col_sloose" type="checkbox" value="1" ' . ( 1 == $csx_col_sloose ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_sgoal">' . __( 'Label column 6 (goals)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_sgoal" id="csx_label_sgoal" type="text" value="' . $csx_label_sgoal . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_sgoal" id="csx_col_sgoal" type="checkbox" value="1" ' . ( 1 == $csx_col_sgoal ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><th scope="row" class="wpc-label"><label for="csx_label_spoint">' . __( 'Label column 7 (points)', 'wp-championship' ) . ':</label></th>' . "\n";
		$out .= '<td><input name="csx_label_spoint" id="csx_label_spoint" type="text" value="' . $csx_label_spoint . '" size="20" /></td>' . "\n";
		$out .= '<td><input name="csx_col_spoint" id="csx_col_spoint" type="checkbox" value="1" ' . ( 1 == $csx_col_spoint ? 'checked="checked"' : '' ) . ' /> ' . __( 'hide', 'wp-championship' ) . ' </td></tr>';
		$out .= '<tr><td colspan="3">&nbsp;</td></tr>';

		$out .= '</table>' . "\n";

		// add submit button to form.
		$out .= '<p class="submit"><input type="submit" name="xupdate" value="' . __( 'Save settings', 'wp-championship' ) . ' &raquo;" /></p>';

		$out .= '</form></div>' . "\n";

		$out .= '</div></div>';

		echo wp_kses( $out, wpc_allowed_tags() );
	}
}//make it pluggable.

