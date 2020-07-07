<?php

class Mercanet_Transaction
{
    public static function save( $data, $raw_data, $transaction_type, $schedule_amount = null ) {
        global $wpdb;
        $transaction_date = $data['transactionDateTime'];
        $order_id = $data['orderId'];
        $authorization_id = isset( $data['authorisationId'] ) ? $data['authorisationId'] : null;
        $transaction_reference = $data['transactionReference'];
        $masked_pan = isset( $data['maskedPan'] ) ? $data['maskedPan'] : null;
        $tmp_amount = ( $schedule_amount == null || $schedule_amount == "null" ) ?  $data['amount'] : $schedule_amount;
        $amount = round( $tmp_amount / 100, 2);
        $payment_mean_brand = isset( $data['paymentMeanBrand'] ) ? $data['paymentMeanBrand'] : null;
        $payment_mean_type = isset( $data['paymentMeanType'] ) ? $data['paymentMeanType'] : null;
        $response_code = isset( $data['responseCode'] ) ? $data['responseCode'] : null;
        $acquirer_response_code = isset( $data['acquirerResponseCode'] ) ? $data['acquirerResponseCode'] : null;
        $complementary_code = isset( $data['complementaryCode'] ) ? $data['complementaryCode'] : null;
        $complementary_info = isset( $data['complementaryInfo'] ) ? $data['complementaryInfo'] : null;

        $exist_transaction = self::check_ifexist_transaction($transaction_date, $order_id, $authorization_id, $transaction_reference);

        if(!$exist_transaction){
        $wpdb->insert("{$wpdb->prefix}mercanet_transaction",
            array(
                'transaction_date' => $transaction_date,
                'order_id' => $order_id,
                'authorization_id' => $authorization_id,
                'transaction_reference' => $transaction_reference,
                'masked_pan' => $masked_pan,
                'amount' => $amount,
                'transaction_type' => $transaction_type,
                'payment_mean_brand' => $payment_mean_brand,
                'payment_mean_type' => $payment_mean_type,
                'response_code' => $response_code,
                'acquirer_response_code' => $acquirer_response_code,
                'complementary_code' => $complementary_code,
                'complementary_info' => $complementary_info,
                'raw_data' => $raw_data
            )
        );
        return $wpdb->insert_id;
    }else{
    	return false;
    }

    }

        public static function check_ifexist_transaction( $transaction_date, $order_id, $authorization_id, $transaction_reference  ) {
        global $wpdb;
        return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mercanet_transaction
                                WHERE transaction_date = '$transaction_date' AND order_id = '$order_id' AND authorization_id = '$authorization_id' AND transaction_reference = '$transaction_reference'" );
    }

    public static function get_by_order_id( $order_id ) {
        global $wpdb;
        $locale = Mercanet_Api::get_locale();
        return $wpdb->get_results( "SELECT mt.*, rc_message AS response_code_message, cc_message AS complementary_code_message, arc_message AS acquirer_response_code_message
                                    FROM {$wpdb->prefix}mercanet_transaction mt
                                    LEFT JOIN {$wpdb->prefix}mercanet_response_code rc ON (mt.response_code = rc.response_code AND rc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_complementary_code cc ON (mt.complementary_code = cc.complementary_code AND cc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_acquirer_response_code arc ON (mt.acquirer_response_code = arc.acquirer_response_code AND arc_locale = '{$locale}')
                                    WHERE order_id = '{$order_id}'
                                    ORDER BY transaction_date DESC" );
    }

    public static function get_by_order_complete( $order_id ) {
        global $wpdb;
        return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mercanet_transaction
                                WHERE order_id = '$order_id' AND response_code = '00'" );
    }

    public static function get_by_id( $transaction_id ) {
        global $wpdb;
        $result = $wpdb->get_row("
            SELECT *
            FROM {$wpdb->prefix}mercanet_transaction
            WHERE transaction_id = '$transaction_id'
        ");

        if (!empty($result)) {
            $row_datas = explode('|', $result->raw_data);

            if (!empty($row_datas)) {
                $result->raw_data = array();

                foreach ($row_datas as $key_data => $datas) {
                    // Explode the data, ex: ResponseCode:45
                    $data = explode('=', $datas);

                    if (!empty($data)) {
                        if (count($data) <= 2 && count($data) > 1) {
                            $label = (isset($data[0])) ? $data[0] : $key_data;
                            $value = (isset($data[1])) ? $data[1] : 'Not Found';
                        } elseif (count($data) > 1) {
                            $label = (isset($data[0])) ? $data[0] : $key_data;
                            $value = '';

                            // Create the value
                            foreach ($data as $key_raw => $raw_data) {
                                if ($key_raw == 0) {
                                    continue;
                                }

                                if ($key_raw == 1) {
                                    $value .= $raw_data;
                                } else {
                                    $value .= '='.$raw_data;
                                }
                            }
                            $value = htmlspecialchars((string) $value);

                        }

                        // Add the result to the raw_data
                        $result->raw_data[$label] = $value;
                    }
                }
            }
        }

        return $result;
    }

    public static function get_by_reference( $reference ) {
        global $wpdb;
        return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mercanet_transaction
                                WHERE transaction_reference = '$reference'" );
    }

    public static function output() {
        global $post;
        $transactions = self::get_by_order_id( $post->ID );

        if ( ! empty( $transactions ) ) { ?>
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr>
                        <th scope="col"> <?php echo __( 'Payment brand', 'mercanet' ) ?></th>
                        <th>Date</th>
                        <th scope="col"> <?php echo __( 'N° Order', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'N° Authorization', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'N° Transaction', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'N° Card', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'Amount', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'Operation type', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'Message response', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'Acquirer response', 'mercanet' ) ?></th>
                        <th scope="col"> <?php echo __( 'Error message', 'mercanet' ) ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach( $transactions as $transact ) { ?>
                    <tr id="<?php echo $transact->transaction_id; ?>">
                        <td>
                            <?php if ( ! empty( $transact->payment_mean_brand ) ) { ?>
                                <img alt="<?php echo $transact->payment_mean_brand; ?>" src="<?php echo WP_PLUGIN_URL . "/" . plugin_basename( dirname( __DIR__ ) ) . '/assets/img/' . $transact->payment_mean_brand . '.png'; ?>" heigth="32px" width="32px">
                            <?php } ?>
                        </td>
                        <td><?php echo date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $transact->transaction_date ) ); ?></td>
                        <td><?php echo $transact->order_id; ?></td>
                        <td><?php echo $transact->authorization_id; ?></td>
                        <td><?php echo $transact->transaction_reference; ?></td>
                        <td><?php echo $transact->masked_pan; ?></td>
                        <td><?php echo $transact->amount; ?></td>
                        <td><?php echo $transact->transaction_type; ?></td>
                        <td><?php echo $transact->response_code_message; ?></td>
                        <td><?php echo $transact->acquirer_response_code_message; ?></td>
                        <td><?php echo $transact->complementary_code_message; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else {
            echo '<p>' . __( 'No transactions for this order.', 'mercanet' ) . '<p>';
        }
    }

    public static function get_all() {
        global $wpdb;
        $locale = Mercanet_Api::get_locale();
        return $wpdb->get_results("SELECT mt.*, rc_message AS response_code_message, cc_message AS complementary_code_message, arc_message AS acquirer_response_code_message
                                    FROM {$wpdb->prefix}mercanet_transaction mt
                                    LEFT JOIN {$wpdb->prefix}mercanet_response_code rc ON (mt.response_code = rc.response_code AND rc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_complementary_code cc ON (mt.complementary_code = cc.complementary_code AND cc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_acquirer_response_code arc ON (mt.acquirer_response_code = arc.acquirer_response_code AND arc_locale = '{$locale}')
                                    ORDER BY transaction_id DESC");
    }


    public static function get_all_limit( $first_entry, $transactions_pages ) {
        global $wpdb;
        $locale = Mercanet_Api::get_locale();
        return $wpdb->get_results("SELECT mt.*, rc_message AS response_code_message, cc_message AS complementary_code_message, arc_message AS acquirer_response_code_message
                                    FROM {$wpdb->prefix}mercanet_transaction mt
                                    LEFT JOIN {$wpdb->prefix}mercanet_response_code rc ON (mt.response_code = rc.response_code AND rc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_complementary_code cc ON (mt.complementary_code = cc.complementary_code AND cc_locale = '{$locale}')
                                    LEFT JOIN {$wpdb->prefix}mercanet_acquirer_response_code arc ON (mt.acquirer_response_code = arc.acquirer_response_code AND arc_locale = '{$locale}')
                                    ORDER BY transaction_id DESC
                                    LIMIT {$first_entry}, {$transactions_pages}");
    }
}
