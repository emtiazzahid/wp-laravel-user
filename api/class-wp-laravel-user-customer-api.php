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
     * Retrieves the contact schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        if ( $this->schema ) {
            return $this->add_additional_fields_schema( $this->schema );
        }

        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'customer',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __( 'Unique identifier for the object.' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name' => [
                    'description' => __( 'Name of the customer.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'phone' => [
                    'description' => __( 'Phone number of the customer.' ),
                    'type'        => 'string',
                    'required'    => true,
                    'context'     => [ 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],  
                'email' => [
                    'description' => __( 'Email of the customer.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'budget' => [
                    'description' => __( 'Budget of the customer.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'message' => [
                    'description' => __( "Message of the customer." ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema( $this->schema );
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
    public function create_customer( $request ) {
        $customer = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $customer ) ) {
            return $customer;
        }

        $customer_id = $this->wp_laravel_user_create_customer( $customer );

        if ( is_wp_error( $customer_id ) ) {
            $customer_id->add_data( [ 'status' => 400 ] );

            return $customer_id;
        }

        $customer_data = $this->get_customer( $customer_id );
        $response = $this->prepare_item_for_response( $customer_data, $request );

        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $customer_id ) ) );

        return rest_ensure_response( $response );
    }

    /**
     * Get the customer, if the ID is valid.
     *
     * @param int $id Supplied ID.
     *
     * @return array|Object|WP_Error
     */
    protected function get_customer( $id ) {
        $customer = get_user_by( 'ID', $id );
        $customer_meta_fields = get_user_meta( $id );

        if ( ! $customer ) {
            return new WP_Error(
                'rest_customer_invalid_id',
                __( 'Invalid customer ID.' ),
                [ 'status' => 404 ]
            );
        }

        return [
            'customer' => $customer,
            'customer_meta_fields' => $customer_meta_fields,
        ];
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

        if ( isset( $request['phone'] ) ) {
            $prepared['phone'] = $request['phone'];
        }

        if ( isset( $request['email'] ) ) {
            $prepared['email'] = $request['email'];
        }

        if ( isset( $request['budget'] ) ) {
            $prepared['budget'] = $request['budget'];
        }

        if ( isset( $request['message'] ) ) {
            $prepared['message'] = $request['message'];
        }

        if ( isset( $request['source'] ) ) {
            $prepared['source'] = $request['source'];
        }

        return $prepared;
    }


    /**
     * Prepares the item for the REST response.
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param \WP_REST_Request $request Request object.
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request ) {
        $customer = $item['customer'];
        $customer_meta_fields = $item['customer_meta_fields'];

        $data   = [];
        $fields = $this->get_fields_for_response( $request );

        if ( in_array( 'id', $fields, true ) ) {
            $data['id'] = (int) $customer->id;
        }

        if ( in_array( 'name', $fields, true ) ) {
            $data['name'] = $customer->display_name;
        }

        if ( in_array( 'phone', $fields, true ) ) {
            $data['phone'] = isset($customer_meta_fields['_subscriber_phone']) ? $customer_meta_fields['_subscriber_phone'][0] : '';
        }

        if ( in_array( 'email', $fields, true ) ) {
            $data['email'] = $customer->user_email;
        }

        if ( in_array( 'budget', $fields, true ) ) {
            $data['budget'] = isset($customer_meta_fields['_subscriber_budget']) ? $customer_meta_fields['_subscriber_budget'][0] : '';
        }

        if ( in_array( 'message', $fields, true ) ) {
            $data['message'] = isset($customer_meta_fields['_subscriber_message']) ? $customer_meta_fields['_subscriber_message'][0] : '';
        }

        if ( in_array( 'source', $fields, true ) ) {
            $data['source'] = isset($customer_meta_fields['_subscriber_source']) ? $customer_meta_fields['_subscriber_source'][0] : '';
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->filter_response_by_context( $data, $context );

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Insert a new customer
     *
     * @param  array  $args
     *
     * @return int|WP_Error
     */
    function wp_laravel_user_create_customer( $args = [] ) {
        if ( empty( $args['name'] ) || empty( $args['email'] ) ) {
            return new \WP_Error( 'no-name-or-email', __( 'Please provide a valid name and email for user.', 'wp-laravel-user' ) );
        }

        $defaults = [
            'name'          => '',
            'phone'         => '',
            'email'         => '',
            'budget'         => '',
            'message'         => '',
            'source'         => ''
        ];

        $data = wp_parse_args( $args, $defaults );
        $username = str_replace(' ','_',strtolower($data['name']));

        $user_id = username_exists($username);
        if ($user_id == null && email_exists($data['email']) == false) {
            $user_id = wp_create_user( $username, wp_generate_password(), $data['email'] );
            update_user_meta( $user_id, "first_name",  $data['name'] ) ;
            unset($data['name']);
            unset($data['email']);
            $this->update_user_meta_list($user_id, $data);

            $user = get_user_by( 'id', $user_id );
            $user->add_role( 'subscriber' );
        } else {
            return new \WP_Error( 'user-already-exist', __( 'Username or email already exists', 'wp-laravel-user' ) );
        }

        return $user_id;
    }

    /**
     * @param $user_id
     * @param $meta_list
     */
    private function update_user_meta_list($user_id, $meta_list) {
        foreach ($meta_list as $key => $item) {
            update_user_meta($user_id, '_subscriber_' . $key, $item );
        }
    }
}