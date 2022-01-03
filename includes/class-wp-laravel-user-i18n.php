<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/emtiazzahid
 * @since      1.0.0
 *
 * @package    Wp_Laravel_User
 * @subpackage Wp_Laravel_User/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Laravel_User
 * @subpackage Wp_Laravel_User/includes
 * @author     Emtiaz Zahid <emtiazzahid@gmail.com>
 */
class Wp_Laravel_User_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-laravel-user',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
