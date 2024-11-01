<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2014-2018  Hans Matzen  (email : webmaster at tuxlog.de)
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

/**
 * Function to export team and match data.
 */
function wpc_export_cb() {
	// get sql object.
	global $wpdb;
	require dirname( __FILE__ ) . '/globals.php';

	$dlmode      = '';
	$export_data = '';

	// check nonce.
	if ( array_key_exists( 'dlmode', $_POST ) ) {
		if ( ! isset( $_POST['wpc_nonce_export'] ) ) {
			die( "Looks like you didn't send any credentials. Please reload the page. " );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_export'] ) ), 'wpc_nonce_export' ) ) {
			die( "Looks like you didn't send any credentials. Please reload the page. " );
		}
	}

	if ( array_key_exists( 'dlmode', $_GET ) ) {
		if ( ! isset( $_GET['wpc_nonce_export'] ) ) {
			die( "Looks like you didn't send any credentials. Please reload the page. " );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wpc_nonce_export'] ) ), 'wpc_nonce_export' ) ) {
			die( "Looks like you didn't send any credentials. Please reload the page. " );
		}
	}

	// export daten zusammen stellen.
	if ( array_key_exists( 'dlmode', $_POST ) ) {
		$dlmode = sanitize_text_field( wp_unslash( $_POST['dlmode'] ) );
	}

	if ( array_key_exists( 'dlmode', $_GET ) ) {
		$dlmode = sanitize_text_field( wp_unslash( $_GET['dlmode'] ) );
	}

	$exmode = '';
	if ( array_key_exists( 'exmode', $_POST ) ) {
		$exmode = sanitize_text_field( wp_unslash( $_POST['exmode'] ) );
	}

	if ( array_key_exists( 'exmode', $_GET ) ) {
		$exmode = sanitize_text_field( wp_unslash( $_GET['exmode'] ) );
	}

	$fnmode = '';
	if ( array_key_exists( 'fnmode', $_POST ) ) {
		$fnmode = sanitize_text_field( wp_unslash( $_POST['fnmode'] ) );
	}

	if ( array_key_exists( 'fnmode', $_GET ) ) {
		$fnmode = sanitize_text_field( wp_unslash( $_GET['fnmode'] ) );
	}

	if ( '' != $dlmode ) {

		if ( 'team' == $exmode ) {

			if ( 'true' == $fnmode ) {
				$export_data = __( "'TeamID','Name','Shortname','Icon','GroupID','Qualified','Penalty'\n", 'wp-championship' );
			}
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->get_results( $wpdb->prepare( 'select * from %i order by tid;', $cs_team ) );

			foreach ( $results as $res ) {
				$export_data .= $res->tid . ",'";
				$export_data .= $res->name . "','";
				$export_data .= $res->shortname . "','";
				$export_data .= $res->icon . "','";
				$export_data .= $res->groupid . "',";
				$export_data .= $res->qualified . ',';
				$export_data .= $res->penalty . "\n";
			}
		}

		if ( 'match' == $exmode ) {

			if ( 'true' == $fnmode ) {
				$export_data = __( "'MatchID','Round','Matchday','TeamID1','TeamID2','Location','DateTime','Result1','Result2','Winner','origTeamID1','origTeamID2'\n", 'wp-championship' );
			}
			// The placeholder ignores can be removed when %i is supported by WPCS.
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$results = $wpdb->get_results( $wpdb->prepare( 'select * from %i order by mid;', $cs_match ) );

			foreach ( $results as $res ) {
				$export_data .= $res->mid . ",'";
				$export_data .= $res->round . "',";
				$export_data .= $res->spieltag . ',';
				$export_data .= $res->tid1 . ',';
				$export_data .= $res->tid2 . ",'";
				$export_data .= $res->location . "','";
				$export_data .= $res->matchtime . "',";
				$export_data .= $res->result1 . ',';
				$export_data .= $res->result2 . ',';
				$export_data .= $res->winner . ',';
				$export_data .= $res->ptid1 . ',';
				$export_data .= $res->ptid2 . "\n";
			}
		}
		// display csv or download it.

		if ( 'true' == $dlmode ) {
			$filename = $exmode . '.csv';
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', false );
			header( 'Content-type: text/csv' );
			header( "Content-Disposition: attachment; filename={$filename}" );
			header( 'Content-Transfer-Encoding: binary' );
			$fh = @fopen( 'php://output', 'w' );
			if ( 'match' == $exmode ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$results = $wpdb->get_results( $wpdb->prepare( 'select * from %i order by mid;', $cs_match ), ARRAY_A );
			} elseif ( 'team' == $exmode ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$results = $wpdb->get_results( $wpdb->prepare( 'select * from %i order by tid;', $cs_team ), ARRAY_A );
			}

			foreach ( $results as $res ) {
				fputcsv( $fh, $res, ',', "'" );
			}
			// Close the file.
			fclose( $fh );
		} else {
			echo esc_attr( $export_data );
		}
		// you must end here to stop the displaying of the html below.
		exit( 0 );
	}

	// export ausgeben ===================================================.

	$out = '';
	// add log area style.
	$out .= '<style>#message {margin:20px; padding:20px; background:#cccccc; color:#cc0000;}</style>';
	$out .= '<div id="exportform" class="wrap" >';
	$out .= '<h2>wp-championship ' . __( 'Export', 'wp-championship' ) . '</h2><br/>';
	// add nonce.
	$out .= '<input name="wpc_nonce_export" id="wpc_nonce_export" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_export' ) . '" />';
	$out .= '<label for="exmode">' . __( 'Select data to export', 'wp-championship' ) . ':</label></th>' . "\n";
	$out .= '<select name="exmode" id="exmode">' . "\n";
	$out .= '<option value="team">' . __( 'Teams', 'wp-championship' ) . '</option>\n';
	$out .= '<option value="match">' . __( 'Matches', 'wp-championship' ) . '</option>\n';
	$out .= "</select><br/>\n";
	// first line contains fieldnames.
	$out .= '<label for="fnmode">' . __( 'Output column names', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<input name="fnmode" id="fnmode" type="checkbox" value="1" /><br/>' . "\n";
	// show or download data.
	$out .= '<label for="dlmode">' . __( 'Download data as CSV-File', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<input name="dlmode" id="dlmode" type="checkbox" value="1" /><br/>' . "\n";
	// add submit button to form.
	$href = site_url( 'wp-admin' ) . '/admin.php?page=cs_admin.php';
	$out .= '<p class="submit">';
	$out .= '<input type="submit" name="startexport" id="startexport" value="' . __( 'Start export', 'wp-championship' ) . ' &raquo;" onclick="submit_this(\'export\')" />';
	$out .= '&nbsp;&nbsp;&nbsp;';
	$out .= '<input type="submit" name="cancelexport" id="cancelexport" value="' . __( 'Close', 'wp-championship' ) . '" onclick="tb_remove();" />';
	$out .= '</p>' . "\n";
	$out .= '<p>' . __( 'You can mark the csv-export with Ctrl-a, copy it with Ctrl-c and paste it to your favorite editor or spreadsheet with Ctrl-v.', 'wp-championship' ) . '</p>';
	$out .= '<hr />' . "\n";
	// div container fuer das verarbeitungs log.
	$out .= '<textarea name="message" id="message" cols="55" rows="15">&nbsp;</textarea>';
	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}

