<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2008-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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

require_once 'supp/supp.php';

// -----------------------------------------------------------------------------------
// Funktion zur Ausgabe der Admin Statistikseite.
// -----------------------------------------------------------------------------------
if ( ! function_exists( 'cs_admin_stats' ) ) {// make it pluggable.
	/**
	 * Function to determine the admin statistics
	 */
	function cs_admin_stats() {
		 include 'globals.php';
		global $wpdb;

		// initialisiere ausgabe variable.
		$out  = '';
		$out .= tl_add_supp();
		$out .= "<div class='wrap'>";

		// The placeholder ignores can be removed when %i is supported by WPCS.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r0 = $wpdb->get_row( $wpdb->prepare( "select count(*) as anz from  %i where round='V' ;", $cs_match ) );
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r1 = $wpdb->get_row( $wpdb->prepare( "select count(*) as anz from  %i where round='F' ;", $cs_match ) );
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$r2 = $wpdb->get_row( $wpdb->prepare( 'select count(*) as anz from  %i where winner<>-1;', $cs_match ) );

		$out .= 'Das Turnier besteht aus ' . ( $r0->anz + $r1->anz ) . ' Spielen.<br />';
		$out .= 'Davon ' . $r0->anz . ' in der Vorrunde und ' . $r1->anz . ' in der Finalrunde.<br />';
		$out .= 'Es wurden bereits ' . $r2->anz . ' Begegnungen entschieden. <br />';

		// anzeigen der gewinnermannschaft falls tunier schon beendet.
		$cswinner = cs_get_cswinner();
		if ( $cswinner ) {
			$out .= '<hr>' . __( 'The championship winner is:', 'wp-championship' ) . "<b>$cswinner</b><hr>";
		}

		// ausgabe des aktuellen punktestandes und des ranges.
		$rank = cs_get_ranking();
		$i    = 0;
		$out .= '<h2>' . __( 'Current score', 'wp-championship' ) . "</h2>\n";
		$out .= "<table border='1' style='width:500px' ><tr>\n";
		$out .= '<th scope="col" style="text-align: center">Platz</th>' . "\n";
		$out .= '<th scope="col" style="text-align: center">Spieler</th>' . "\n";
		$out .= '<th style="width:20">' . __( 'Score', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th style="width:20">' . __( 'Number of tips', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th style="width:20">' . __( 'Missed tips so far', 'wp-championship' ) . '</th>' . "\n";
		$out .= '<th style="width:20">' . __( 'Champion tip', 'wp-championship' ) . '</th>' . "</tr>\n";

		$i            = 0;
		$j            = 1;
		$pointsbefore = -1;
		foreach ( $rank as $row ) {
			// platzierung erhoehen, wenn punkte sich veraendern.
			if ( $row->points != $pointsbefore ) {
				$i = $i + $j;
				$j = 1;
			} else {
				++$j;
			}

			// ermittle anzahl abgegebener tipps.
			$r0 = $wpdb->get_row(
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$wpdb->prepare( 'select count(*) as anz from %i a inner join %i b on a.mid=b.mid where a.result1<>-1 and a.result2<>-1 and userid=%d;', $cs_tipp, $cs_match, $row->userid ),
			);

			// ermittle verpasste Tipps (Tipps, die nicht rchtzeitig abgegeben wurden).
			$blog_now = current_time( 'mysql', 0 );
			$r2       = $wpdb->get_row(
				$wpdb->prepare(
					'select count(*) as anz from cs_tipp a inner join cs_match b on a.mid=b.mid 
					where b.matchtime < %s 
					  and a.result1=-1 
					  and a.result2=-1 
					  and userid=%s',
					$blog_now,
					$row->userid
				)
			);

			// ermittle champion tipp.
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$r1 = $wpdb->get_row( $wpdb->prepare( 'select name from %i a inner join %i b on a.tid = b.champion where userid=%d;', $cs_team, $cs_users, $row->userid ) );

			$out .= "<tr><td style='text-align:center'>$i</td>";
			$out .= "<td style='text-align:center'>" . $row->vdisplay_name . '</td>';
			$out .= "<td style='text-align:center'>" . $row->points . '</td>';
			$out .= "<td style='text-align:center'>" . $r0->anz . '</td>';
			$out .= "<td style='text-align:center'>" . $r2->anz . '</td>';
			$out .= "<td style='text-align:center'>" . ( isset( $r1->name ) ? $r1->name : '-' ) . '</td></tr>';
			// gruppenwechsel versorgen.
			$pointsbefore = $row->points;
		}

		$out .= '</table>' . "<p>&nbsp;</p></div>\n";

		echo wp_kses( $out, wpc_allowed_tags() );
	}
}//make it pluggable.


