<?php
namespace Appsero\Helper;

use WP_REST_Request;
use WP_REST_Response;

class Native_License {

    /**
     * Create native license
     */
    public function create( WP_REST_Request $request ) {
        $requested = $request->get_params();

        global $wpdb;
        $table_name = $wpdb->prefix . 'appsero_licenses';

        $license_data = [
            'source_id'        => $requested['id'],
            'key'              => $requested['key'],
            'activation_limit' => $requested['activation_limit'],
            'expire_date'      => $requested['expire_date'],
            'store_type'       => 'fastspring',
            'user_id'          => $this->customer_first_or_create( $requested['customer'] ),
            'meta'             => json_encode( [
                'product_name'   => $requested['product_name'],
                'variation_name' => $requested['variation_name'],
            ] ),
        ];

        $wpdb->insert( $table_name, $license_data );

        return new WP_REST_Response( [
            'success' => true,
        ] );
    }

    /**
     * Format common license data
     */
    public static function format_common_license_data( $license, $orderData ) {

        return [
            'key'              => $license['key'],
            'status'           => $license['status'],
            'activation_limit' => $license['activation_limit'],
            'expire_date'      => $license['expire_date'],
            'variation_id'     => $orderData['variation_id'] ? $orderData['variation_id'] : null,
            'order_id'         => $orderData['id'],
            'user_id'          => $orderData['customer']['id'],
        ];
    }

    /**
     * Get user id or create new
     */
    private function customer_first_or_create( $customer ) {
        $exists = email_exists( $customer['email'] );

        if ( $exists ) {
            return $exists;
        }

        $name            = appsero_split_name( $customer['name'] );
        $random_password = wp_generate_password( 12, false );

        $userdata = [
            'user_pass'     => $random_password,
            'display_name'  => $name[0],
            'user_nicename' => $name[0],
            'first_name'    => $name[0],
            'last_name'     => $name[1],
            'user_login'    => $customer['email'],
            'user_email'    => $customer['email'],
            'role'          => 'subscriber',
        ];

        $user_id = wp_insert_user( $userdata );

        wp_send_new_user_notifications( $user_id );

        return $user_id;
    }

}
