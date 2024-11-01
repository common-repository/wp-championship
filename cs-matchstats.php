<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2012-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

/**
 * Tooltip function for the match statistic
 */
function tooltip_matchstats() {
	include 'globals.php';
	global $wpdb,$wpcs_demo;

	$teamid = ( isset( $_GET['teamid'] ) ? intval( $_GET['teamid'] ) : '' );
	$args   = array();

	// initialisiere ausgabe variable.
	$out = '';

	// pruefe ob anwender angemeldet ist, wenn nicht gebe hinweis aus und beende die funktion.
	if ( ! is_user_logged_in() && $wpcs_demo <= 0 ) {
		$out .= __( 'You are not logged in.', 'wp-championship' ) . '<br />';
		$out .= __( 'To attend the guessing game, you need an account at this website', 'wp-championship' ) . '<br />';
		echo wp_kses( $out );
		exit;
	}

	// ausgabe alle spiele des teams.
	// -------------------------------------------------------------------.

	if ( file_exists( get_stylesheet_directory() . '/wp-championship/icons/' ) ) {
		$iconpath = get_stylesheet_directory_uri() . '/wp-championship/icons/';
	} else {
		$iconpath = plugins_url( 'icons/', __FILE__ );
	}

	$matches = cs_get_team_matches( $teamid );

	$out .= '<p>&nbsp;</p>';
	$out .= "<table border='1' >\n";
	$out .= '<tr><th>' . __( 'Date', 'wp-championship' ) . '</th><th>&nbsp;</th><th>' . __( 'match', 'wp-championship' ) . '</th><th>&nbsp;</th><th>' . __( 'Result', 'wp-championship' ) . '</th></tr>';
	foreach ( $matches as $m ) {
			$out .= '<tr><td>' . $m['date'] . '</td>';
		if ( substr( $m['icon1'], 0, 4 ) == 'http' ) {
			$out .= "<td><img src='" . $m['icon1'] . "' width='30'></td>";
		} else {
			$out .= "<td><img src='" . $iconpath . $m['icon1'] . "' width='30'></td>";
		}
			$out .= "<td  style='text-align:center'>" . $m['name1'] . ' - ' . $m['name2'] . '</td>';
		if ( substr( $m['icon2'], 0, 4 ) == 'http' ) {
			$out .= "<td><img src='" . $m['icon2'] . "' width='30'></td>";
		} else {
			$out .= "<td><img src='" . $iconpath . $m['icon2'] . "' width='30'></td>";
		}
			$out .= "<td style='text-align:center'>" . $m['res1'] . ':' . $m['res2'] . "</td></tr>\n";
	}
	$out .= "</table>\n";

	$out .= "<p>&nbsp;</p></div>\n";

	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}
add_action( 'wp_ajax_tooltip_matchstats', 'tooltip_matchstats' );

