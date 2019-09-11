<?php
namespace Appsero\Helper\Edd;

class MyAccountPage {

    /**
     * Constructor of EDD MyAccountPage class
     */
    public function __construct() {
        add_action( 'edd_purchase_history_header_after', [ $this, 'table_header_row' ] );

        add_action( 'edd_purchase_history_row_end', [ $this, 'table_body_rows' ], 10, 2 );

        add_filter( 'edd_allow_template_part_history_purchases', [ $this, 'history_purchases_template' ] );
    }

    /**
     * Inside the tr of thead
     */
    public function table_header_row() {
        ?>
        <th class="appsero_licenses_col"><?php _e( 'License', 'appsero-helper' ); ?></th>
        <?php
    }

    /**
     * Inside the tr of tbody
     */
    public function table_body_rows( $payment_id, $payment_meta ) {
        global $wpdb;
        $table = $wpdb->prefix . 'appsero_licenses';
        $license = $wpdb->get_row( "
                        SELECT * FROM {$table} WHERE
                        `order_id` = {$payment_id}
                        LIMIT 1;
                    " );
        echo '<td class="appsero_licenses_col">';

        if ( $license ) {
            $license_url = esc_url( add_query_arg( [ 'license' => 'appsero', 'order_id' => $payment_id ] ) );
            echo '<a href="' . $license_url . '">View Licenses</a>';
        } else {
            echo '-';
        }

        echo '</td>';
    }

    /**
     * Output of licnese details
     */
    public function history_purchases_template() {
        if ( isset( $_GET['license'], $_GET['order_id'] ) && $_GET['license'] == 'appsero' ) {
            $back_url = esc_url( remove_query_arg( [ 'license', 'order_id' ] ) );

            echo '<p><a href="'. $back_url .'" class="edd-manage-license-back edd-submit button gray">Go back</a></p>';

            require_once ASHP_ROOT_PATH . 'includes/Renderer/LicensesRenderer.php';

            $renderer = new \Appsero\Helper\Renderer\LicensesRenderer();

            echo $renderer->show( intval( $_GET['order_id'] ) );

            return false;
        }

        return true;
    }

}
