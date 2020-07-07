<?php

/**
 * 1961-2016 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2016 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Mercanet_Webservice {

    // Services Refund
    const WS_REFUND = 'cashManagement/refund';
    const WS_CANCEL = 'cashManagement/cancel';
    const WS_DIAGNOSTIC = 'diagnostic/getTransactionData';
    // Service Duplicate
    const WS_SERVICE_DUPLICATE = 'cashManagement/duplicate';
    // Interface Version
    const WS_IV_REFUND = 'CR_WS_2.6';
    const WS_IV_DIAGNOSTIC = 'DR_WS_2.3';
    const WS_IV_DUPLICATE = 'CR_WS_2.3';
    // Variables
    const WS_STATUS_TO_CAPTURE = 'TO_CAPTURE';

    public function __construct() {
        
    }

    /**
     * Send Schedules
     */
    public static function send_recurring_schedules() {
        $enabled = false;
        $check_enabled = get_option('woocommerce_mercanet_recurring_settings', null);
        if (!empty($check_enabled) && $check_enabled['enabled'] == "yes") {
            $enabled = true;
        } 

        if (!Mercanet_Api::is_allowed(array('ABO')) && $enabled) {
            return;
        }
        // Get Schedules
        $schedules = Mercanet_Recurring_Payment::get_schedules_to_capture();
        if (!empty($schedules)) {
            foreach ($schedules as $schedule) {
                if ($schedule->number_occurences <= 0) {
                    continue;
                }

                $last_schedule_substr = substr($schedule->last_schedule, 0, 10);
                $today = date("Y-m-d");
                $diff = abs(strtotime($last_schedule_substr) - strtotime($today));
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor($diff / (60 * 60 * 24));
                
                // Notif marchand si non paiement depuis 12 mois
                if ($years > 1 || $months > 12 || $days > 365) {
                    $schedule->late = true;
                }

                $occurence = intval($schedule->number_occurences);
                $nb_fail = ($schedule->periodicity == 'D') ?
                        floor(intval($days) / $occurence) :
                        floor(intval($months) / $occurence);

                $schedule->nb_fail = $nb_fail;
                if($nb_fail > 0) {                    
                    $schedule->current_specific_price = floatval($schedule->current_specific_price) * $nb_fail;
                }
                $schedule->current_occurence = intval($schedule->current_occurence) + intval($nb_fail);
                $time = strtotime(date($schedule->last_schedule));
                $last_occurence = $occurence * $nb_fail;
                $next_occurence = $occurence * ($nb_fail + 1);

                $schedule->last_schedule = ($schedule->periodicity == 'D') ?
                        date("Y-m-d h:i:s", strtotime("+$last_occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$last_occurence month", $time));
                $schedule->next_schedule = ($schedule->periodicity == 'D') ?
                        date("Y-m-d h:i:s", strtotime("+$next_occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$next_occurence month", $time));
            }
            

            foreach ($schedules as $schedule) {
                $infos = Mercanet_Recurring_Payment::get_mercanet_customer_payment_recurring($schedule->id_mercanet_customer_payment_recurring);
                $params = self::get_recurringparams($schedule);
                $order = new WC_Order($params['orderId']);
                // send result   
                $result = self::submit_web_service(self::WS_SERVICE_DUPLICATE, $params);
                $data = json_decode(json_encode($result), True);
                $data['amount'] = floatval($order->get_total()) * 100;
                $data['transactionReference'] = $params['transactionReference'];
                $data['orderId'] = $params['orderId'];
                
                // insert transact
                // Treat Result
                if (isset($result->seal) && Mercanet_Api::check_seal($result, $result->seal, true) == true) {
                    $return_url = WC()->api_request_url('Mercanet_Gateway_Recurring');
                    $raw_data_params = Mercanet_Api::get_params($order, $schedule->id_customer, $return_url, false);
                    $raw_data = Mercanet_Api::get_raw_data(array_merge($data, $raw_data_params));
                    Mercanet_Transaction::save($data, base64_decode($raw_data), "RECURRING PAYMENT");
                    if (!empty($schedule)) {
                        unset($schedule->late);
                        date_default_timezone_set('europe/paris');
                        $schedule->current_occurence = intval($schedule->current_occurence) + 1;
                        $schedule->last_schedule = date('Y-m-d h:i:s');
                        $occurence = $schedule->number_occurences;
                        $time = strtotime(date($schedule->last_schedule));
                        $schedule->next_schedule = ($schedule->periodicity == 'D') ?
                                date("Y-m-d h:i:s", strtotime("+$occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$occurence month", $time));
                        $schedule->status = 1;
                        if (isset($result->responseCode) && $result->responseCode != "00") {
                            $schedule->status = 2;
                        } else {
                            $order = new WC_Order($data['orderId']);
                            $order->update_status("wc-processing");
                        }

                        $schedule->current_specific_price = $infos[0]->current_specific_price;
                        Mercanet_Gateway_Recurring::update_mercanet_customer_payment_recurring($schedule->id_mercanet_customer_payment_recurring, $schedule);
                    }
                } else {
                    
                    $customer = get_user_by('ID', $schedule->id_customer);
                    if(isset($schedule->late)) {
                        
                        $merchant_mail = get_option("admin_email");      
                        $message = <<<TXT
                            Client : {$customer->user_firstname} {$customer->user_lastname} \r\n
                            Commande : #{$data['orderId']}
TXT;
                        $return = wp_mail($merchant_mail, "Retard paiement supérieur à 12 mois", $message);
                        if(!$return) {
                            Mercanet_Logger::log("[WEBSERVICE][RECURRING] Problème lors de l'envoi du mail de retard");
                        }
                    }
                    
                    $message = '[WEBSERVICE][RECURRING] Customer: ' . $schedule->id_customer . ' ' . $customer->get('display_name') . ' || Params: ';
                    $message .= implode(
                            ', ', array_map(
                                    function ($v, $k) {
                                return $k . '=' . $v;
                            }, $params, array_keys($params)
                            )
                    );
                    Mercanet_Logger::log($message);
                }
            }
        }
        return true;
    }

    /**
     * Return the params for the duplicate
     */
    public static function get_recurringparams($schedule) {
        $params = array();

        // Reference
        $order = new WC_Order($schedule->id_order);
        $transac = Mercanet_Transaction::get_by_order_id($schedule->id_order);
        $infos = ($transac[0]->payment_mean_brand == Mercanet_Gateway_Sdd::sdd_libelle) ? 
                Mercanet_Recurring_Payment::get_recurring_infos($schedule->id_product, true) :
                Mercanet_Recurring_Payment::get_recurring_infos($schedule->id_product);
        $reference_product = new WC_Product($schedule->id_product);
        $reference_product->set_price($infos[0]->recurring_amount);
        $payment = new Mercanet_Gateway_Recurring();

        $reference = wc_create_order();
        if (!empty($schedule->nb_fail)) {
            $reference->add_product($reference_product, $schedule->nb_fail);
        } else {
            $reference->add_product($reference_product);
        }
        $shipping_rate_order = reset($order->get_shipping_methods());
        $shipping_rate_array = (is_array($shipping_rate_order['taxes'])) ? $shipping_rate_order['taxes'] : unserialize($shipping_rate_order['taxes']);
        $shipping_rate = new WC_Shipping_Rate(
                $shipping_rate_order['method_id'], $shipping_rate_order['name'], $shipping_rate_order['cost'], $shipping_rate_array, $shipping_rate_order['method_id']
        );
        $reference->set_address($order->get_address());
        $reference->set_address($order->get_address(), 'shipping');
        $reference->set_payment_method($payment);
        for ($i = $schedule->nb_fail; $i > 0; $i--) {
            $reference->add_shipping($shipping_rate);
        }
        unset($schedule->nb_fail);
        $reference->calculate_shipping();
        $reference->calculate_taxes();
        $reference->calculate_totals();
        $raw_data = Mercanet_Api::get_params($reference, $schedule->id_customer, WC()->api_request_url('Mercanet_Gateway_Recurring'), false);        
        $raw_data['transactionReference'] = $transac[0]->transaction_reference;
        // Mandatory        
        $params['amount'] = (float) trim($raw_data['amount']);
        $params['captureDay'] = trim($raw_data['captureDay']);
        $params['captureMode'] = trim($raw_data['captureMode']);
        $params['currencyCode'] = trim(Mercanet_Api::get_mercanet_currency());
        $params['customerEmail'] = trim($raw_data['customerContact.email']);
        $params['customerId'] = $schedule->id_customer;
        $params['customerIpAddress'] = (isset($raw_data['customerIpAddress'])) ? trim($raw_data['customerIpAddress']) : null;
        $params['fromMerchantId'] = trim($raw_data['merchantId']);
        $params['fromTransactionReference'] = trim($raw_data['transactionReference']);
        $params['interfaceVersion'] = self::WS_IV_DUPLICATE;
        $params['keyVersion'] = get_option('mercanet_version_key');
        $params['merchantId'] = get_option('mercanet_merchant_id');
        $params['orderChannel'] = (isset($raw_data['orderChannel'])) ? trim($raw_data['orderChannel']) : null;
        $params['orderId'] = $reference->get_order_number();
        $params['returnContext'] = 'id_cart=' . (int) $schedule->id_order;
        $params['returnContext'] .= ',is_recurring_payment=true';
        $params['returnContext'] .= ',id_schedule=' . (int) $schedule->id_mercanet_customer_payment_recurring;
        $params['transactionReference'] = Mercanet_Api::generate_reference($schedule->id_order);
        if (get_option('mercanet_test_mode') == 'yes') {
            $params['merchantId'] = get_option('mercanet_merchant_id');
            $params['keyVersion'] = get_option('mercanet_version_key');
        }        
        
        ksort($params);
        $params['seal'] = Mercanet_Api::generate_seal($params, true, true);
        return $params;
    }

    public static function submit_web_service($service, array $data, $sealed = true) {
        // Take the WS url
        if (get_option('mercanet_test_mode') == 'yes') {
            $url_webservice = get_option('MERCANET_WS_URL_TEST') . $service;
        } else {
            $url_webservice = get_option('MERCANET_WS_URL') . $service;
        }

        // Add the seal
        ksort($data);

        $data_encoded = json_encode($data);

        // Open cURL session and data are sent to server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_webservice);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept:application/json'));
        curl_setopt($ch, CURLOPT_PORT, 443);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        // Manage errors
        if ($result == false || $info['http_code'] != 200) {
            //echo "Data receive ko : ".$result;
            if (curl_error($ch)) {
                $result .= "\n" . curl_error($ch);
            }
        }

        // Close cURL session
        curl_close($ch);

        return json_decode($result);
    }

}

new Mercanet_Webservice();
