<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://fiverr.com/junaidzx90
 * @since             1.0.0
 * @package           Wcrecorder
 *
 * @wordpress-plugin
 * Plugin Name:       WCRecoder
 * Plugin URI:        https://fiverr.com/junaidzx90
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Devjoo
 * Author URI:        https://fiverr.com/junaidzx90/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wcrecorder
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WCRECORDER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wcrecorder-activator.php
 */
function activate_wcrecorder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcrecorder-activator.php';
	Wcrecorder_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wcrecorder-deactivator.php
 */
function deactivate_wcrecorder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcrecorder-deactivator.php';
	Wcrecorder_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wcrecorder' );
register_deactivation_hook( __FILE__, 'deactivate_wcrecorder' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wcrecorder.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wcrecorder() {

	$plugin = new Wcrecorder();
	$plugin->run();

}
run_wcrecorder();
