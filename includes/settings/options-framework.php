<?php
/**
 * Options Framework
 *
 * @package   Options Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2014 WP Theming
 *
 * @wordpress-plugin
 * Plugin Name: Options Framework
 * Plugin URI:  http://wptheming.com
 * Description: A framework for building theme options.
 * Version:     1.8.4
 * Author:      Devin Price
 * Author URI:  http://wptheming.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: optionsframework
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function wdxxx_optionsframework_init() {

	//  If user can't edit theme options, exit
	if ( !current_user_can( 'edit_theme_options' ) )
		return;

	// Load translation files
	load_plugin_textdomain( 'options-framework', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Loads the required Options Framework classes.
	require plugin_dir_path( __FILE__ ) . 'includes/class-options-framework.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-options-framework-admin.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-options-interface.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-options-media-uploader.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-options-sanitization.php';

	// Instantiate the main plugin class.
	$wdxxx_Options_Framework = new wdxxx_Options_Framework;
	$wdxxx_Options_Framework->init();

	// Instantiate the options page.
	$wdxxx_wdxxx_Options_Framework_Admin = new wdxxx_wdxxx_Options_Framework_Admin;
	$wdxxx_wdxxx_Options_Framework_Admin->init();

	// Instantiate the media uploader class
	$wdxxx_Options_Framework_media_uploader = new wdxxx_Options_Framework_Media_Uploader;
	$wdxxx_Options_Framework_media_uploader->init();

}
add_action( 'init', 'wdxxx_optionsframework_init', 20 );

/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * Not in a class to support backwards compatibility in themes.
 */

if ( ! function_exists( 'wdxxx_of_get_option' ) ) :

function wdxxx_of_get_option( $name, $default = false ) {
	$config = get_option( 'optionsframework' );

	if ( ! isset( $config['id'] ) ) {
		return $default;
	}

	$options = get_option( $config['id'] );

	if ( isset( $options[$name] ) ) {
		return $options[$name];
	}

	return $default;
}

endif;