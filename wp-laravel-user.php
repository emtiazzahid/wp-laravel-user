<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/emtiazzahid
 * @since             1.0.0
 * @package           Wp_Laravel_User
 *
 * @wordpress-plugin
 * Plugin Name:       WP Laravel User
 * Plugin URI:        https://github.com/emtiazzahid/wp-laravel-user
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Emtiaz Zahid
 * Author URI:        https://github.com/emtiazzahid
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-laravel-user
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
define( 'WP_LARAVEL_USER_VERSION', '1.0.0' );
define( 'WP_LARAVEL_USER_TEXT_DOMAIN', 'wp-list-table' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-laravel-user-activator.php
 */
function activate_wp_laravel_user() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-laravel-user-activator.php';
	Wp_Laravel_User_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-laravel-user-deactivator.php
 */
function deactivate_wp_laravel_user() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-laravel-user-deactivator.php';
	Wp_Laravel_User_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_laravel_user' );
register_deactivation_hook( __FILE__, 'deactivate_wp_laravel_user' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-laravel-user.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_laravel_user() {

	$plugin = new Wp_Laravel_User();
	$plugin->run();

}
run_wp_laravel_user();
