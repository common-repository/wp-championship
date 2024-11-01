<?php
/**
 * This file is part of the wp-championship plugin for WordPress
 *
 * Copyright 2010-2023  Hans Matzen  (email : webmaster at tuxlog.de)
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
 * Function to backup user edited files during updates
 */
function hm_backup_wpc() {
	global $wp_filesystem;

	// name for backup directory.
	$backupdir = $wp_filesystem->wp_content_dir() . '/upgrade/wpc_update/';

	// wenn vorhanden, altes backup verzeichnis löschen.
	if ( is_dir( $backupdir ) ) {
		$wp_filesystem->delete( $backupdir, true );
	}

	// backupdir anlegen.
	$wp_filesystem->mkdir( $backupdir );

	// individuelle css datei sichern.
	if ( $wp_filesystem->is_file( dirname( __FILE__ ) . '/wp-championship.css' ) ) {
		$wp_filesystem->copy(
			dirname( __FILE__ ) . '/wp-championship.css',
			$backupdir . '/wp-championship.css'
		);
	}

}

/**
 * Function to restore user edited files during updates
 */
function hm_recover_wpc() {
	global $wp_filesystem;

	$backupdir = $wp_filesystem->wp_content_dir() . '/upgrade/wpc_update/';
	$pdir      = dirname( __FILE__ );

	// individuelle css datei zurück holen.
	if ( $wp_filesystem->is_file( $backupdir . '/wp-championship.css' ) ) {
		$wp_filesystem->copy(
			$backupdir . '/wp-championship.css',
			$pdir . '/wp-championship.css'
		);
	}

	// backup verzeichnis löschen.
	$wp_filesystem->delete( $backupdir, true );
}

// add filter.
add_filter( 'upgrader_pre_install', 'hm_backup_wpc', 10, 2 );
add_filter( 'upgrader_post_install', 'hm_recover_wpc', 10, 2 );
