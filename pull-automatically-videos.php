<?php
/**
 * Pull Automatically Videos.
 *
 * @package   Pull_automatically_Videos
 * @author    Matias Esteban <estebanmatias92@gmail.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Matias Esteban
 *
 * @wordpress-plugin
 * Plugin Name: Pull Automatically Videos
 * Plugin URI:  TODO
 * Description: Pull videos from YouTube, Vimeo (& more sites soon), automatically, and post these in Wordpress.
 * Version:     0.1.0
 * Author:      Matias Esteban
 * Author URI:  TODO
 * Text Domain: pull_automatically_videos-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define root path of this plugin
if ( ! defined( 'PAV_PLUGIN_ROOT' ) ) {
	define( 'PAV_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
}

// Includes
require_once( plugin_dir_path( __FILE__ ) . 'class-pull-automatically-videos.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Pull_automatically_Videos', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Pull_automatically_Videos', 'deactivate' ) );

// Plugin class instance
Pull_automatically_Videos::get_instance();
