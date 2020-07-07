<?php

class Mercanet_Refund
{
    // Services
    const WS_REFUND = 'cashManagement/refund';
    const WS_CANCEL = 'cashManagement/cancel';
    const WS_DIAGNOSTIC = 'diagnostic/getTransactionData';
    // Interface Version
    const WS_IV_REFUND = 'CR_WS_2.6';
    const WS_IV_DIAGNOSTIC = 'DR_WS_2.3';
    // Variables
    const WS_STATUS_TO_CAPTURE = 'TO_CAPTURE';

    public static function get_diagnostic_params( $transaction ) {
        $params = array();
        $params['interfaceVersion'] = self::WS_IV_DIAGNOSTIC;
        $params['merchantId'] = get_option( 'mercanet_merchant_id');
        $params['keyVersion'] = get_option( 'mercanet_version_key' );
        $params['transactionReference'] = $transaction->transaction_reference;
        ksort($params);
        $params['seal'] = Mercanet_Api::generate_seal($params, true);

        return $params;
    }

    public static function refund( $order, $amount ) {
        $error = new WP_Error();

        // Transaction
        $transaction = Mercanet_Transaction::get_by_order_complete( $order->id );
        if ( empty( $transaction ) ) {
            return false;
        }

        // Diagnostic
        $diagnostic_params = self::get_diagnostic_params( $transaction );
        $diagnostic_result = Mercanet_Refund::submit_webservice( self::WS_DIAGNOSTIC, $diagnostic_params );
        if ( empty( $diagnostic_result ) ) {
            return false;
        }

        if ( $diagnostic_result->responseCode  != '00' || ! isset( $diagnostic_result->transactionStatus ) ) {
            $msg_code = Mercanet_Api::get_message_response_code( $diagnostic_result->responseCode );
            $error->add( 'mercanet_refund', $msg_code->rc_message );
            return $error;
        }

        if ($diagnostic_result->transactionStatus == self::WS_STATUS_TO_CAPTURE) {
            $service = self::WS_CANCEL;
            $operation_type = Mercanet_Api::ANTICIPATE_REFUND;
        } else {
            $service = self::WS_REFUND;
            $operation_type = Mercanet_Api::REFUND;
        }

        $params = Mercanet_Api::get_refund_params( $order, $amount, $transaction->transaction_reference );
        $result = self::submit_webservice( $service, $params );


        if ( Mercanet_Api::check_seal( $result, ( isset( $result->seal ) ) ? $result->seal : null, true ) != true) {
            $error->add( 'mercanet_refund', __( 'Refund Seal Error', 'mercanet ') );
            return $error;
        }

        if ( isset( $result->responseCode ) && $result->responseCode == '00' ) {
            self::save( $transaction, $order, $result, $amount, $operation_type );

            $schedules = Mercanet_Schedule::get_by_order_id( $order->id );

            if ( ! empty( $schedules ) ) {
                foreach ( $schedules as $schedule ) {
                    $cancel_params = Mercanet_Api::get_refund_params( $schedule->order_id, $schedule->amount, $schedule->transaction_reference );
                    $cancel_result = Mercanet_Refund::submit_webservice( self::WS_CANCEL, $cancel_params );

                    if ( Mercanet_Api::check_seal( $cancel_result, ( isset( $cancel_result->seal ) ) ? $cancel_result->seal : null, true ) != true) {
                        $error->add( 'mercanet_refund', __( 'Refund Seal Error', 'mercanet ') );
                        return $error;
                    }
                    if ( isset( $cancel_result->responseCode ) && $cancel_result->responseCode == '00' ) {
                        Mercanet_Schedule::update_schedule( $schedule->mercanet_schedule_id, 'status', 'Cancelled' );
                    }
                }
            }
            return true;
        }
        $msg_code = Mercanet_Api::get_message_response_code( $result->responseCode );
        $error->add( 'mercanet_refund', $msg_code->rc_message );
        return $error;

    }

    public static function submit_webservice( $service, $data ) {
        if ( get_option( 'mercanet_test_mode' ) == 'yes' ) {
            $url_ws_test = get_option( 'MERCANET_WS_URL_TEST' );
            $url_webservice = $url_ws_test . $service;
        } else {
            $url_ws = get_option( 'MERCANET_WS_URL' );
            $url_webservice = $url_ws . $service;
        }
        ksort( $data );
        $data_encoded = json_encode( $data );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_webservice);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept:application/json'));
        curl_setopt($ch, CURLOPT_PORT, 443);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        //curl_setopt($ch, CURLOPT_CAINFO,  "c:/wamp/cacert.pem");

        $result = curl_exec( $ch );
        $info = curl_getinfo( $ch );

        if ( $result == false || $info['http_code'] != 200)  {
            if ( curl_error( $ch ) ) {
               $result .= "\n".curl_error( $ch );
            }
        }

        curl_close( $ch );

        return json_decode( $result );
    }

    public static function save( $transaction, $order, $result, $amount, $transaction_type ) {
        global $wpdb;

        $order_id = $order->id;
        $transaction_date = isset( $result->operationDateTime ) ? $result->operationDateTime : null;
        $authorization_id = isset( $result->authorisationId ) ? $result->authorisationId : null;
        $transaction_reference = $transaction->transaction_reference;
        $masked_pan = $transaction->masked_pan;
        $payment_mean_brand = $transaction->payment_mean_brand;
        $payment_mean_type = $transaction->payment_mean_type;
        $complementary_info = isset( $result->complementaryInfo ) ? $result->complementaryInfo : null;
        $response_code = $result->responseCode;
        $acquirer_response_code = isset( $result->acquirerResponseCode ) ? $result->acquirerResponseCode : null;
        $complementary_code = isset( $result->complementaryCode ) ? $result->complementaryCode : null;

        $message = '';
        foreach ($result as $key => $value) {
            $message .= $key . ': ' . $value . '<br />';
        }
        $raw_data = $message;

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
    }
}
