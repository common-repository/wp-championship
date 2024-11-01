<?php
/**
 * Plugin Name: wp-championship
 * Plugin URI: http://www.tuxlog.de/wp-championship
 * Description: wp-championship is a plugin for WordPress letting you play a guessing game of a tournament e.g. soccer.
 * Version: 10.9
 * Author: tuxlog
 * Author URI: http://www.tuxlog.de
 * Text Domain: wp-championship
 *
 * Copyright 2007-2024  Hans Matzen  (email : webmaster at tuxlog dot de)
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

// globale konstanten einlesen / Parameter.
require 'globals.php';
// include setup functions.
require_once 'setup.php';

// admin dialog.
require_once 'cs-admin.php';
require_once 'cs-admin-team.php';
require_once 'cs-admin-match.php';
require_once 'cs-admin-finals.php';
require_once 'cs-admin-users.php';
require_once 'cs-admin-stats.php';
require_once 'cs-admin-labels.php';
require_once 'cs-admin-tippgroup.php';
require_once 'cs-admin-export.php';
require_once 'cs-admin-import.php';
// if ( class_exists( 'SOAPClient' ) ) {.
	require_once 'cs-admin-openligadbimport.php';
// }
require_once 'cs-usertipp.php';
require_once 'cs-userstats.php';
require_once 'cs-stats.php';
// use customized groupstats if available.
if ( file_exists( get_stylesheet_directory() . '/wp-championship/cs-groupstats.php' ) ) {
	require_once get_stylesheet_directory_uri() . '/wp-championship/cs-groupstats.php';
} else {
	require_once 'cs-groupstats.php';
}
// use customized matchstats if available.
if ( file_exists( get_stylesheet_directory() . '/wp-championship/cs-matchstats.php' ) ) {
	require_once get_stylesheet_directory_uri() . '/wp-championship/cs-matchstats.php';
} else {
	require_once 'cs-matchstats.php';
}
require_once 'wpc-autoupdate.php';
require_once 'class-cs-widget.php';
require_once 'class-cs-widget-user.php';
require_once 'class-cs-widget-tippgroup.php';
// xmlrpc extension laden wenn diese aktiviert ist.
if ( get_option( 'cs_xmlrpc' ) > 0 ) {
	require_once 'class-wpc-xmlrpc.php';
}

// set this to the demo user id to enable demo mode, everything can be read without being logged in
// or to 0 or false to disable demo mode.
static $wpcs_demo = 0;

// activating deactivating the plugin.
register_activation_hook( __FILE__, 'wp_championship_install' );

// aktion fuer erinnerungsmails hinzufügen.
add_action( 'cs_mailreminder', 'cs_mailservice2' );

// uncomment this to loose everything when deactivating the plugin.
register_deactivation_hook( __FILE__, 'wp_championship_deinstall' );

// add option page.
add_action( 'admin_menu', 'add_menus' );

// init plugin.
add_action( 'init', 'wp_championship_init' );

add_action(
	'widgets_init',
	function() {
		return register_widget( 'cs_widget' );
	}
);
add_action(
	'widgets_init',
	function() {
		return register_widget( 'cs_widget_user' );
	}
);
add_action(
	'widgets_init',
	function() {
		return register_widget( 'cs_widget_tippgroup' );
	}
);

if ( get_option( 'cs_newuser_auto' ) == 1 ) {
	add_action( 'user_register', 'cs_add_user' );
}

// add triggers for ajax/thickbox call for export and import.
if ( is_admin() ) {
		add_action( 'wp_ajax_wpc_openligadbimport', 'wpc_openligadbimport_cb' );
		add_action( 'wp_ajax_wpc_openligadb_getleagues', 'wpc_openligadb_getleagues' );
		add_action( 'wp_ajax_wpc_export', 'wpc_export_cb' );
		add_action( 'wp_ajax_wpc_import', 'wpc_import_cb' );
}

/**
 * Just return the css link
 * this function is called via the wp_head hook.
 */
function wpcs_css() {
	$def  = 'wp-championship-default.css';
	$user = 'wp-championship.css';

	if ( file_exists( WP_PLUGIN_DIR . '/wp-championship/' . $user ) ) {
		$def = $user;
	}

	$css_url = plugins_url( $def, __FILE__ );

	if ( file_exists( get_stylesheet_directory() . '/wp-championship/wp-championship.css' ) ) {
		$css_url = get_stylesheet_directory_uri() . '/wp-championship/wp-championship.css';
	}

	wp_enqueue_style( 'wp-championship-css', $css_url, array(), '9999' );
}


/**
 * Init funvction for the wp-championship plugin
 */
function wp_championship_init() {
	// get translation.
	load_plugin_textdomain( 'wp-championship', false, basename( dirname( __FILE__ ) ) . '/lang/' );

	if ( function_exists( 'add_shortcode' ) ) {
		add_shortcode( 'cs-usertipp', 'show_usertippform' );
		add_shortcode( 'cs-userstats', 'show_user_stats' );
		add_shortcode( 'cs-stats1', 'show_stats1' );
		add_shortcode( 'cs-stats2', 'show_stats2' );
		add_shortcode( 'cs-stats3', 'show_stats3' );
		add_shortcode( 'cs-stats4', 'show_stats4' );
		add_shortcode( 'cs-stats5', 'show_stats5' );
		add_shortcode( 'cs-stats6', 'show_stats6' );
		add_shortcode( 'cs-stats7', 'show_stats7' );
		add_shortcode( 'cs-stats8', 'show_stats8' );
		add_shortcode( 'cs-stats9', 'show_stats9' );
	}

	// CSS hinzufügen.
	add_action( 'wp_enqueue_scripts', 'wpcs_css' );
	add_action( 'admin_print_styles', 'wpcs_css' );

	// javascript hinzufügen für tablesorter / floating menu und statistik ajaxeffekt.
	if ( ! is_admin() ) {
		wp_enqueue_script( 'cs_tablesort', plugins_url( 'jquery.tablesorter.min.js', __FILE__ ), array( 'jquery' ), '2.0.3', true );

		if ( file_exists( get_stylesheet_directory() . '/wp-championship/cs-stats.js' ) ) {
			wp_enqueue_script( 'cs-stats', get_stylesheet_directory_uri() . '/wp-championship/cs-stats.js', array( 'jquery' ), '9999' );
		} else {
			wp_enqueue_script( 'cs-stats', plugins_url( 'cs-stats.js', __FILE__ ), array( 'jquery' ), '9999' );
		}
		$pdu = array( 'wpc_ajaxurl' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'cs-stats', 'wpcobj', $pdu );

		wp_enqueue_script( 'cs_hovertable', plugins_url( 'jquery.tooltip.js', __FILE__ ), array( 'jquery' ), '9999' );
	}
}

/**
 * Add javascript for the backend.
 */
function wpcs_add_adminjs() {
	// javascript hinzufügen für tablesorter / floating menu und statistik ajaxeffekt.
	wp_enqueue_script( 'cs_admin', plugins_url( 'cs-admin.js', __FILE__ ), array(), '9999' );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
}

/**
 * Adds the admin menustructure.
 */
function add_menus() {

	$ppath = plugin_dir_path( __FILE__ );

	$jspage = add_menu_page( 'wp-champion', __( 'Guessing-game', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin.php', 'cs_admin', plugin_dir_url( __FILE__ ) . '/worldcup-icon.png' );
	add_action( 'admin_print_styles-' . $jspage, 'wpcs_add_adminjs' );
	add_action( 'load-' . $jspage, 'wpc_contextual_help' );

	$sp1 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship teams', 'wp-championship' ), __( 'Teams', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_team.php', 'cs_admin_team', 1 );
	add_action( 'admin_print_styles-' . $jspage, 'wpcs_add_adminjs' );
	add_action( 'load-' . $sp1, 'wpc_contextual_help' );

	$sp2 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship maches', 'wp-championship' ), __( 'Preliminary', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_match.php', 'cs_admin_match', 2 );
	add_action( 'load-' . $sp2, 'wpc_contextual_help' );

	$sp3 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship finals', 'wp-championship' ), __( 'Finals', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_finals.php', 'cs_admin_finals', 3 );
	add_action( 'load-' . $sp3, 'wpc_contextual_help' );

	$sp4 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship users', 'wp-championship' ), __( 'Player', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_users.php', 'cs_admin_users', 4 );
	add_action( 'load-' . $sp4, 'wpc_contextual_help' );

	$sp5 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship stats', 'wp-championship' ), __( 'Stats', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_stats.php', 'cs_admin_stats', 5 );
	add_action( 'load-' . $sp5, 'wpc_contextual_help' );

	$sp6 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship labels', 'wp-championship' ), __( 'Labels', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_labels.php', 'cs_admin_labels', 6 );
	add_action( 'admin_print_styles-' . $sp6, 'wpcs_add_adminjs' );
	add_action( 'load-' . $sp6, 'wpc_contextual_help' );

	if ( get_option( 'cs_use_tippgroup' ) ) {
		$sp7 = add_submenu_page( $ppath . 'cs_admin.php', __( 'wp-championship tip groups', 'wp-championship' ), __( 'Tip groups', 'wp-championship' ), 'manage_options', $ppath . 'cs_admin_tippgroup.php', 'cs_admin_tippgroup', 7 );
		add_action( 'load-' . $sp7, 'wpc_contextual_help' );
	}

	add_users_page( __( 'Guessing-game', 'wp-championship' ), __( 'Guessing-game', 'wp-championship' ), 'read', 'wpcs-usertipp', 'backend_show_usertippform' );
}

 /**
  * Just get the usertipp dialog and print it for use in backend.
  */
function backend_show_usertippform() {
	$erg = show_usertippform();
	echo wp_kses( $erg, wpc_allowed_tags() );
}
