<?php

/**
 * Provide a customer list view
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/emtiazzahid
 * @since      1.0.0
 *
 * @package    Wp_Laravel_User
 * @subpackage Wp_Laravel_User/admin/partials
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
* Class WLT_list_Table
*/
class Wp_Laravel_User_Admin_Customer_list extends WP_List_Table {

    /**
    * Prepares the list of items for displaying.
    */
    public function prepare_items() {
        $order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
        $order = isset( $_GET['order'] ) ? $_GET['order'] : '';
        $search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';

        $this->items = $this->wlt_list_table_data( $order_by, $order, $search_term );

        $wlt_columns = $this->get_columns();
        $wlt_hidden = $this->get_hidden_columns();
        $ldul_sortable = $this->get_sortable_columns();

        $this->_column_headers = [ $wlt_columns, $wlt_hidden, $ldul_sortable ];
    }

    /**
    * Customers row actions
    */
    public function handle_row_actions( $item, $column_name, $primary ) {
        if( $primary !== $column_name ) {
            return '';
        }
        $action = [];
        $action['view'] = '<a>'.__( 'View', WP_LARAVEL_USER_TEXT_DOMAIN ).'</a>';

        return $this->row_actions( $action );
    }

    /**
    * Display columns data
    */
    public function wlt_list_table_data( $order_by = '', $order = '', $search_term = '' ) {
        ?><section style="margin: 30px 0 0 0; ">
            <h2><?php _e( 'Customers :', WP_LARAVEL_USER_TEXT_DOMAIN ); ?></h2>
        <?php
        $data_array = [];

        $customers = get_users( array( 'role__in' => array( 'subscriber' ) ) );
        if( $customers ) {

            foreach( $customers as $customer ) {

                $author_id = get_post_field( 'post_author', $post_id );
                $author_name = get_the_author_meta( 'display_name', $author_id );

                $content_post = get_post( $post_id );
                $content = $content_post->post_content;

                $post_name = get_the_title( $post_id );

                $data_array[] = [
                    'wlt_id'				=> '<a data-post-name="'.$post_name.'" data-post-content="'.$content.'" data-post-id="'.$post_id.'" href="'.get_edit_post_link( $post_id ).'"> '.$post_id.' </a>',
                    'wlt_title'				=> '<a href="'.get_edit_post_link( $post_id ).'"> '.get_the_title( $post_id ).' </a>',
                    'wlt_publish_data'		=> get_the_date( 'l F j, Y', $post_id ),
                    'wlt_post_type'			=> get_post_type( $post_id ),
                    'wlt_post_author'		=> '<a href="'.get_edit_profile_url( $author_id ).'"> '.$author_name.' </a>',
                ];
            }
        }

        ?></section><?php
        return $data_array;

    }

    /**
     * Gets a list of all, hidden and sortable columns
     */
    public function get_hidden_columns() {
        return [];
    }

    /**
     * Gets a list of columns.
     */
    public function get_columns() {

        $columns = array(
            'cb'				=> '<input type="checkbox" class="wlt-selected" />',
            'wlt_id'			=> __( 'ID', WP_LARAVEL_USER_TEXT_DOMAIN ),
            'wlt_title'			=> __( 'Title', WP_LARAVEL_USER_TEXT_DOMAIN ),
            'wlt_publish_data'	=> __( 'Publish Date', WP_LARAVEL_USER_TEXT_DOMAIN ),
            'wlt_post_type'		=> __( 'Post Type', WP_LARAVEL_USER_TEXT_DOMAIN ),
            'wlt_post_author'	=> __( 'Post Author', WP_LARAVEL_USER_TEXT_DOMAIN ),
        );
        return $columns;
    }

    /**
     * Return column value
     */
    public function column_default( $item, $column_name ) {

        switch ($column_name) {
            case 'wlt_id':
            case 'wlt_title':
            case 'wlt_publish_data':
            case 'wlt_post_type':
            case 'wlt_post_author':
                return $item[$column_name];
            default:
                return 'no list found';
        }
    }

    /**
     * Rows check box
     */
    public function column_cb( $items ) {

        $top_checkbox = '<input type="checkbox" class="wlt-selected" />';
        return $top_checkbox;
    }
}

$object = new Wp_Laravel_User_Admin_Customer_list();
$object->prepare_items();
$object->display();