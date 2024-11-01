<?php
/** This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2014-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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
 * Function to import data from files in wp-championship/sql
 */
function wpc_import_cb() {
	// get sql object.
	global $wpdb;
	require dirname( __FILE__ ) . '/globals.php';

	// check nonce.
	if ( ! empty( $_POST ) ) {
		if ( ! isset( $_POST['wpc_nonce_import'] ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpc_nonce_import'] ) ), 'wpc_nonce_import' ) ) {
			die( "<br><br>Looks like you didn't send any credentials. Please reload the page. " );
		}

		$immode = '';

		if ( array_key_exists( 'immode', $_POST ) ) {
			$immode = sanitize_text_field( wp_unslash( $_POST['immode'] ) );
		}

		$csv_delall = false;
		if ( array_key_exists( 'csv_delall', $_POST ) ) {
			$csv_delall = ( sanitize_text_field( wp_unslash( $_POST['csv_delall'] ) ) == 'true' ? true : false );
		}

		$csv_file = '';
		if ( array_key_exists( 'csvfile', $_POST ) ) {
			$csv_file = sanitize_text_field( wp_unslash( $_POST['csvfile'] ) );
		}

		$fnmode = '';
		if ( array_key_exists( 'fnmode', $_POST ) ) {
			$fnmode = sanitize_text_field( wp_unslash( $_POST['fnmode'] ) );
		}

		// check if current data should be deleted.
		if ( true === $csv_delall ) {
			if ( 'team' == $immode ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$result = $wpdb->query( $wpdb->prepare( 'truncate table %i;', $cs_team ) ); // this also resets the auto increment counter.
			}

			if ( 'match' == $immode ) {
				// The placeholder ignores can be removed when %i is supported by WPCS.
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$result = $wpdb->query( $wpdb->prepare( 'truncate table %i', $cs_match ) );
			}
			echo esc_attr__( 'Data deleted.', 'wp-championship' ) . '<br />';
		}

		// insert new data.
		$row    = 1;
		$handle = fopen( dirname( __FILE__ ) . '/sql/' . $csv_file, 'r' );

		if ( 'true' == $fnmode ) {
			// ersten Datensatz überspringen, wenn feldnamen enthalten sind.
			$data = fgetcsv( $handle, 512, ',', "'" );
		}
		while ( ( $data = fgetcsv( $handle, 512, ',', "'" ) ) !== false ) {
			$num = count( $data );
			$row++;
			$errorflag = false;

			if ( 'team' == $immode ) {

				if ( 7 == $num ) {
					$wpdb->query(
						$wpdb->prepare(
							// The placeholder ignores can be removed when %i is supported by WPCS.
							// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							'insert into %i (tid,name,shortname,icon,groupid,qualified,penalty) values ( %d, %s, %s, %s, %s, %d, %d);',
							$cs_team,
							$data[0],
							$data[1],
							$data[2],
							$data[3],
							$data[4],
							$data[5],
							$data[6]
						)
					);
				} else {
					$errorflag = true;
					$cnum      = count( $num );
					echo esc_attr__( 'The record contains the wrong number of values and was not inserted.', 'wp-championship' ) . esc_attr( "( $row - $cnum )" ) . '<br />';
				}
			}

			if ( 'match' == $immode ) {

				if ( 12 == $num ) {
					$wpdb->query(
						$wpdb->prepare(
							// The placeholder ignores can be removed when %i is supported by WPCS.
							// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
							'insert into %i (mid,round,spieltag,tid1,tid2,location,matchtime,result1,result2,winner,ptid1,ptid2) 
							values (0,%s,%d,%d,%d,%s,%s,%d,%d,%d,%d,%d);',
							$cs_match,
							$data[1],
							$data[2],
							$data[3],
							$data[4],
							$data[5],
							$data[6],
							$data[7],
							$data[8],
							$data[9],
							$data[10],
							$data[11],
						)
					);
				} else {
					$erroflag = true;
					$cnum     = count( $num );
					echo esc_attr__( 'The record contains the wrong number of values and was not inserted.', 'wp-championship' ) . esc_attr( "( $row - $cnum )" ) . '<br />';
				}
			}

			if ( $result && ! $errorflag ) {
				echo esc_attr__( 'Record inserted.', 'wp-championship' ) . esc_attr( "( {$data[0]} )" ) . '<br />';
			} else {
				echo esc_attr__( 'Database error, record not inserted.', 'wp-championship' ) . '<br />';
			}
		}
		fclose( $handle );
		// you must end here to stop the displaying of the html below.
		exit( 0 );
	}

	// import formular aufbauen ===================================================.

		$out = '';
	// add log area style.
	$out .= '<style>#message {margin:20px; padding:20px; background:#cccccc; color:#cc0000;}</style>';
	$out .= '<div id="importform" class="wrap" >';
	$out .= '<h2>wp-championship ' . __( 'Import', 'wp-championship' ) . '</h2>';
	// add nonce.
	$out .= '<input name="wpc_nonce_import" id="wpc_nonce_import" type="hidden" value="' . wp_create_nonce( 'wpc_nonce_import' ) . '" />';
	$out .= '<label for="immode">' . __( 'Select data to import', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<select name="immode" id="immode">' . "\n";
	$out .= '<option value="team">' . __( 'Teams', 'wp-championship' ) . '</option>\n';
	$out .= '<option value="match">' . __( 'Matches', 'wp-championship' ) . '</option>\n';
	$out .= "</select><br/><br/>\n";
	$out .= '<p>' . __( 'All csv-files must be saved in the directory wp-championship/sql in order to be used as import file.', 'wp-championship' ) . '</p>';
	$out .= '<label for="csvfile">' . __( 'Select CSV-File', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<select name="csvfile" id="csvfile">' . "\n";
	// icon file list on disk.
	$flist = scandir( ( dirname( __FILE__ ) . '/sql' ) );
	// file loop.
	$pak_select_html = '';

	foreach ( $flist as $pfile ) {

		if ( substr( $pfile, 0, 1 ) != '.' && substr( $pfile, strlen( $pfile ) - 4, 4 ) == '.csv' ) {
			$pak_select_html .= "<option value='" . $pfile . "' ";
			$pak_select_html .= '>' . $pfile . "</option>\n";
		}
	}
	$out .= $pak_select_html . "</select><br/><br/>\n";
	// first line contains fieldnames.
	$out .= '<label for="fnmode">' . __( 'First row contains column names', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<input name="fnmode" id="fnmode" type="checkbox" value="1" /><br/>' . "\n";
	// import mit oder ohne überschreiben.
	$out .= '<label for="csvdelall">' . __( 'Delete data before import', 'wp-championship' ) . ':</label>' . "\n";
	$out .= '<input name="csvdelall" id="csvdelall" type="checkbox" value="1" />' . "\n";
	// add submit button to form.
	$href = site_url( 'wp-admin' ) . '/admin.php?page=cs_admin.php';
	$out .= '<p class="submit">';
	$out .= '<input type="submit" name="startimport" id="startimport" value="' . __( 'Start import', 'wp-championship' ) . ' &raquo;" onclick="submit_this(\'import\')" />';
	$out .= '&nbsp;&nbsp;&nbsp;';
	$out .= '<input type="submit" name="cancelimport" id="cancelimport" value="' . __( 'Close', 'wp-championship' ) . '" onclick="tb_remove();" /></p>';
	$out .= '<hr />' . "\n";
	// div container fuer das verarbeitungs log.
	$out .= '<div id="message">' . __( 'Import log', 'wp-championship' ) . '</div>';
	$out .= "</div>\n";
	echo wp_kses( $out, wpc_allowed_tags() );
	wp_die();
}

