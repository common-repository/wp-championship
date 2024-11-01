<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2012-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

/**
 * Function for tooltip groupstats.
 */
function tooltip_groupstats() {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	$groupid = ( isset( $_GET['groupid'] ) ? sanitize_text_field( wp_unslash( $_GET['groupid'] ) ) : '' );
	$args    = array();
	$out     = '';

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus.
	// und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		echo wp_kses( $out, wpc_allowed_tags() );
		exit;
	}

	//
	// lese alternative bezeichnungen.
	//
	$cs_label_group    = get_option( 'cs_label_group' );
	$cs_col_group      = get_option( 'cs_col_group' );
	$cs_label_icon1    = get_option( 'cs_label_icon1' );
	$cs_col_icon1      = get_option( 'cs_col_icon1' );
	$cs_label_match    = get_option( 'cs_label_match' );
	$cs_col_match      = get_option( 'cs_col_match' );
	$cs_label_icon2    = get_option( 'cs_label_icon2' );
	$cs_col_icon2      = get_option( 'cs_col_icon2' );
	$cs_label_location = get_option( 'cs_label_location' );
	$cs_col_location   = get_option( 'cs_col_location' );
	$cs_label_time     = get_option( 'cs_label_time' );
	$cs_col_time       = get_option( 'cs_col_time' );
	$cs_label_tip      = get_option( 'cs_label_tip' );
	$cs_col_tip        = get_option( 'cs_col_tip' );
	$cs_label_points   = get_option( 'cs_label_points' );
	$cs_col_points     = get_option( 'cs_col_points' );
	$cs_label_place    = get_option( 'cs_label_place', __( 'Rank', 'wp-championship' ) );
	$cs_col_place      = get_option( 'cs_col_place' );
	$cs_label_player   = get_option( 'cs_label_player', __( 'Player', 'wp-championship' ) );
	$cs_col_player     = get_option( 'cs_col_player' );
	$cs_label_upoints  = get_option( 'cs_label_upoints', __( 'Score', 'wp-championship' ) );
	$cs_col_upoints    = get_option( 'cs_col_upoints' );
	$cs_label_trend    = get_option( 'cs_label_trend', __( 'Tendency', 'wp-championship' ) );
	$cs_col_trend      = get_option( 'cs_col_trend' );
	$cs_label_steam    = get_option( 'cs_label_steam', __( 'Team', 'wp-championship' ) );
	$cs_col_steam      = get_option( 'cs_col_steam' );
	$cs_label_smatch   = get_option( 'cs_label_smatch', __( 'Matches', 'wp-championship' ) );
	$cs_col_smatch     = get_option( 'cs_col_smatch' );
	$cs_label_swin     = get_option( 'cs_label_swin', __( 'Wins', 'wp-championship' ) );
	$cs_col_swin       = get_option( 'cs_col_swin' );
	$cs_label_stie     = get_option( 'cs_label_stie', __( 'Draw', 'wp-championship' ) );
	$cs_col_stie       = get_option( 'cs_col_stie' );
	$cs_label_sloose   = get_option( 'cs_label_sloose', __( 'Defeat', 'wp-championship' ) );
	$cs_col_sloose     = get_option( 'cs_col_sloose' );
	$cs_label_sgoal    = get_option( 'cs_label_sgoal', __( 'Goals', 'wp-championship' ) );
	$cs_col_sgoal      = get_option( 'cs_col_sgoal' );
	$cs_label_spoint   = get_option( 'cs_label_spoint', __( 'Points', 'wp-championship' ) );
	$cs_col_spoint     = get_option( 'cs_col_spoint' );
	$cs_tipp_sort      = get_option( 'cs_tipp_sort' );

	// ausgabe der gruppen tabelle.
	// -------------------------------------------------------------------.

	// Spielübersicht Vorrunde.
	if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
		$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
	} else {
		$iconpath = plugins_url( 'icons/', __FILE__ );
	}

	// tabellen loop.
	// hole tabellen daten.
	$results = cs_get_team_clification( $groupid );

	$groupid_old = '';

	$out .= "<div id='cs_stattab_v'>";

	foreach ( $results as $res ) {

		// bei gruppenwechsel footer / header ausgeben.
		if ( $res->groupid != $groupid_old ) {
			if ( '' != $groupid_old ) {
				$out .= '</table><p>&nbsp;</p>';
			}

			$out .= "<h2 id='cs_sh_$res->groupid' class='cs_grouphead' >" . __( 'Group', 'wp-championship' ) . ' ' . $res->groupid . "</h2>\n";
			$out .= "<table id='cs_stattab_$res->groupid' class='tablesorter' ><thead><tr>\n";
			if ( ! $cs_col_steam ) {
				$out .= '<th style="text-align: center">' . $cs_label_steam . '</th>' . "\n";
			}
			if ( ! $cs_col_smatch ) {
				$out .= '<th style="text-align: center">' . $cs_label_smatch . '</th>' . "\n";
			}
			if ( ! $cs_col_swin ) {
				$out .= '<th style="text-align: center">' . $cs_label_swin . '</th>' . "\n";
			}
			if ( ! $cs_col_stie ) {
				$out .= '<th style="text-align: center">' . $cs_label_stie . '</th>' . "\n";
			}
			if ( ! $cs_col_sloose ) {
				$out .= '<th style="text-align: center">' . $cs_label_sloose . '</th>' . "\n";
			}
			if ( ! $cs_col_sgoal ) {
				$out .= '<th style="text-align: center">' . $cs_label_sgoal . '</th>' . "\n";
			}
			if ( ! $cs_col_spoint ) {
				$out .= '<th style="text-align:center">' . $cs_label_spoint . '</th></tr>';
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
				$out .= "<td><img class='csicon' alt='icon1' width='20' src='" . $res->icon . "' />";
			} else {
				$out .= "<td><img class='csicon' alt='icon1' width='20' src='" . $iconpath . $res->icon . "' />";
			}
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

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}

add_action( 'wp_ajax_tooltip_groupstats', 'tooltip_groupstats' );

