<?php

class Wp_Laravel_User_Customer_Api extends WP_REST_Controller {
    function __construct() {
        $this->namespace = 'wlu/v1';
        $this->rest_base = 'customers';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_customer' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
     * Checks if a given request has access to read contacts.
     *
     * @param  \WP_REST_Request $request
     *
     * @return boolean
     */
    public function get_items_permissions_check( $request ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a given request has access to create items.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }

    /**
     * Creates one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function create_item( $request ) {
        $contact = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $contact ) ) {
            return $contact;
        }

        $contact_id = $this->wp_laravel_user_create_customer( $contact );

        if ( is_wp_error( $contact_id ) ) {
            $contact_id->add_data( [ 'status' => 400 ] );

            return $contact_id;
        }

        $contact = $this->get_customer( $contact_id );
        $response = $this->prepare_item_for_response( $contact, $request );

        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $contact_id ) ) );

        return rest_ensure_response( $response );
    }

    /**
     * Get the address, if the ID is valid.
     *
     * @param int $id Supplied ID.
     *
     * @return Object|\WP_Error
     */
    protected function get_customer( $id ) {
        $contact = wd_ac_get_address( $id );

        if ( ! $contact ) {
            return new WP_Error(
                'rest_contact_invalid_id',
                __( 'Invalid contact ID.' ),
                [ 'status' => 404 ]
            );
        }

        return $contact;
    }

    /**
     * Prepares one item for create or update operation.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|object
     */
    protected function prepare_item_for_database( $request ) {
        $prepared = [];

        if ( isset( $request['name'] ) ) {
            $prepared['name'] = $request['name'];
        }

        if ( isset( $request['address'] ) ) {
            $prepared['address'] = $request['address'];
        }

        if ( isset( $request['phone'] ) ) {
            $prepared['phone'] = $request['phone'];
        }

        return $prepared;
    }

    /**
     * Insert a new customer
     *
     * @param  array  $args
     *
     * @return int|WP_Error
     */
    function wp_laravel_user_create_customer( $args = [] ) {
        global $wpdb;

        if ( empty( $args['name'] ) ) {
            return new \WP_Error( 'no-name', __( 'You must provide a name.', 'wedevs-academy' ) );
        }

        $defaults = [
            'name'       => '',
            'address'    => '',
            'phone'      => '',
            'created_by' => get_current_user_id(),
            'created_at' => current_time( 'mysql' ),
        ];

        $data = wp_parse_args( $args, $defaults );

        if ( isset( $data['id'] ) ) {

            $id = $data['id'];
            unset( $data['id'] );

            $updated = $wpdb->update(
                $wpdb->prefix . 'ac_addresses',
                $data,
                [ 'id' => $id ],
                [
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'
                ],
                [ '%d' ]
            );

            wd_ac_address_purge_cache( $id );

            return $updated;

        } else {

            $inserted = $wpdb->insert(
                $wpdb->prefix . 'ac_addresses',
                $data,
                [
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'
                ]
            );

            if ( ! $inserted ) {
                return new \WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'wedevs-academy' ) );
            }

            wd_ac_address_purge_cache();

            return $wpdb->insert_id;
        }
    }
}