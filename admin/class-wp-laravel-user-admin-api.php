<?php

/**
 * The api functionality of the plugin.
 *
 * @link       https://github.com/emtiazzahid
 * @since      1.0.0
 *
 * @package    Wp_Laravel_User
 * @subpackage Wp_Laravel_User/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Laravel_User
 * @subpackage Wp_Laravel_User/admin
 * @author     Emtiaz Zahid <emtiazzahid@gmail.com>
 */
class Wp_Laravel_User_Admin_Api {
    function __construct() {
        /**
         * The class responsible for defining all api.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-wp-laravel-user-customer-api.php';

        add_action( 'rest_api_init', [ $this, 'register_api' ] );
    }

    public function register_api() {
        $customer_api = new Wp_Laravel_User_Customer_Api();
        $customer_api->register_routes();
    }
}
