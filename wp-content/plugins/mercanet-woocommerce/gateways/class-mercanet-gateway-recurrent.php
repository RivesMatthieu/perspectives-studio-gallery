<?php

// Check if the module is actived
if (!get_option('mercanet_activation_key') == '') {
    add_action('plugins_loaded', 'init_mercanet_gateway_recurring_class');
    add_filter('woocommerce_payment_gateways', 'add_mercanet_gateway_recurring_class');
    add_action('save_post', 'Mercanet_Gateway_Recurring::save_recurring_payment');
}

function init_mercanet_gateway_recurring_class() {

    function add_mercanet_gateway_recurring_class($methods) {
        $methods[] = 'Mercanet_Gateway_Recurring';
        return $methods;
    }

    /**
     * Mercanet Recurrent Payement Gateway Class
     */
    class Mercanet_Gateway_Recurring extends Mercanet_Gateway_Onetime {

        const ID_STATUS_ACTIVE = 1;
        const ID_STATUS_PAUSE = 2;
        const ID_STATUS_EXPIRED = 3;

        private $abo_enabled = false;

        public function __construct() {
            $this->id = 'mercanet_recurring';
            $this->method_title = __('Mercanet recurring payment', 'mercanet');
            $this->has_fields = false;
            $this->title = __('Recurring Payment', 'mercanet');
            $this->order_button_text = __('Pay with Mercanet', 'mercanet');

            // Gateway name           
            $this->init_settings();
            $this->init_form_fields();

            $check_enabled = get_option('woocommerce_mercanet_recurring_settings', null);
            if (!empty($check_enabled) && $check_enabled['enabled'] == "yes")
                $this->abo_enabled = true;

            $this->onetime_title_names = get_option('mercanet_recurring_title_names');
            if (empty($this->onetime_title_names)) {
                $this->onetime_title_names = array(
                    'en_US' => array(
                        'title_name' => 'Card recurring payment secured by Mercanet',
                    ),
                    'fr_FR' => array(
                        'title_name' => 'Paiement par abonnement sécurisé par carte via Mercanet',
                    )
                );
            }
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_mercanet_response'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_title_names'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_action('before_woocommerce_pay', array($this, 'check_order'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_available_payment_gateways', array($this, 'check_gateway'));
            $locale = Mercanet_Api::get_locale();
            if (!isset($this->onetime_title_names[$locale])) {
                $title_locale = $this->onetime_title_names['en_US'];
            } else {
                $title_locale = $this->onetime_title_names[$locale];
            }

            $this->settings['mercanet_recurring_title'] = $title_locale['title_name'];
            $this->title = $this->settings['mercanet_recurring_title'];
        }

        public function check_gateway($gateways) {
            global $woocommerce, $wp;

            if (isset($gateways[$this->id])) {
                $is_abo = false;
                if (is_object($woocommerce->cart) && sizeof($woocommerce->cart->get_cart()) > 0) {
                    $items = $woocommerce->cart->get_cart();
                    foreach ($items as $item) {
                        $infos = Mercanet_Recurring_Payment::get_recurring_infos($item['product_id']);
                        if (!empty($infos)) {
                            $is_abo = true;
                        }
                    }
                } else if ($wp->query_vars['order-pay'] != "") {
                    $order_id = absint($wp->query_vars['order-pay']);
                    $order = new WC_Order($order_id);
                    $order_items = $order->get_items();
                    foreach ($order_items as $item) {
                        $infos = (is_object($item)) ?
                                Mercanet_Recurring_Payment::get_recurring_infos($item->get_product_id()) :
                                Mercanet_Recurring_Payment::get_recurring_infos($item['item_meta']['_product_id'][0]);

                        if (!empty($infos)) {
                            $is_abo = true;
                        }
                    }
                }

                if ($is_abo) {
                    $tmp_gateway = $gateways[$this->id];
                    $gateways = array();
                    $gateways[$this->id] = $tmp_gateway;
                } else {
                    unset($gateways[$this->id]);
                }
            }
            return $gateways;
        }

        public function init_form_fields() {
            $this->form_fields = array(
                array(
                    'title' => __('LABEL RECURRING PAYMENT', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Label', 'woocommerce'),
                    'default' => __('Recurring Payment by Mercanet', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'mercanet_recurring_title' => array(
                    'type' => 'title_name'
                ),
                'enabled' => array(
                    'title' => __('Activation', 'mercanet'),
                    'type' => 'checkbox',
                    'label' => __('Active Recurring Payment', 'mercanet'),
                    'default' => 'no'
                )
            );

            if (get_option('label_translate_on') == 'yes') {
                unset($this->form_fields['title']);
            } else {
                unset($this->form_fields['mercanet_recurring_title']);
            }
        }

        public function thankyou_page($order_id) {
            $transaction = Mercanet_Transaction::get_by_order_id($order_id);
            if (!empty($transaction[0])) {
                echo '<ul class="order_details">';
                echo '<li class="method">' . __('Authorisation ID', 'mercanet') . '<strong>' . $transaction[0]->authorization_id . ' </strong></li>';
                echo '</ul>';
            }
        }

        public function save_title_names() {
            $locale = get_locale();
            $title_names = $_POST['title_name'];
            $names = array();

            foreach ($title_names as $lang => $name) {
                if ($lang == $locale) {
                    if (empty(trim($name))) {
                        $errors[] = __('You have to choose a name for the recurring payment in the current language.', 'mercanet');
                    }
                }
                $tmp = array(
                    $lang => array(
                        'title_name' => $name
                    )
                );
                $names = array_merge($names, $tmp);
            }
            if (!empty($errors)) {
                $this->errors = $errors;
                foreach ($errors as $key => $value) {
                    WC_Admin_Settings::add_error($value);
                }
            } else {
                update_option('mercanet_recurring_title_names', $names);
            }
        }

        public static function add_form_mercenat_recurring_payment_admin() {
            $product = Mercanet_Recurring_Payment::get_recurring_infos(get_the_ID());
            $form_fields = array(
                array(
                    'type' => 'hidden',
                    'id' => 'id_mercanet_payment_recurring',
                    'name' => 'id_mercanet_payment_recurring',
                    'value' => (!empty($product[0]->id_mercanet_payment_recurring)) ? $product[0]->id_mercanet_payment_recurring : ''
                ),
                array(
                    'label' => 'Type',
                    'type' => 'select',
                    'style' => 'width: 95%',
                    'id' => 'mercanet_type',
                    'name' => 'mercanet_type',
                    'value' => (!empty($product[0]->type)) ? $product[0]->type : '',
                    'options' => array(
                        '1' => __('Simple Payment', 'mercanet'),
                        '2' => __('Recurring Payment', 'mercanet')
                    )
                ), array(
                    'label' => __('Periodicity', 'mercanet'),
                    'type' => 'select',
                    'style' => 'width: 95%',
                    'id' => 'mercanet_periodicity',
                    'name' => 'mercanet_periodicity',
                    'value' => (!empty($product[0]->periodicity)) ? $product[0]->periodicity : '',
                    'options' => array(
                        'D' => __('Day', 'mercanet'),
                        'M' => __('Month', 'mercanet')
                    )
                ), array(
                    'label' => __('Number of occurrences', 'mercanet'),
                    'type' => 'text',
                    'id' => 'mercanet_number_occurrences',
                    'name' => 'mercanet_number_occurrences',
                    'value' => (!empty($product[0]->number_occurences)) ? $product[0]->number_occurences : ''
                ), array(
                    'label' => __('Recurring amount', 'mercanet'),
                    'type' => 'text',
                    'id' => 'mercanet_recurring_amount',
                    'name' => 'mercanet_recurring_amount',
                    'value' => (!empty($product[0]->recurring_amount)) ? $product[0]->recurring_amount : ''
                )
            );

            foreach ($form_fields as $field) {
                switch ($field['type']) {
                    case 'hidden' : woocommerce_wp_hidden_input($field);
                        break;
                    case 'text' : woocommerce_wp_text_input($field);
                        break;
                    case 'select' : woocommerce_wp_select($field);
                        break;
                }
            }
        }

        public static function validate_recurring_admin() {
            $errors = array();

            if (!empty($_POST['mercanet_number_occurrences']) && !empty($_POST['mercanet_recurring_amount'])) {

                $nb_occurences = $_POST['mercanet_number_occurrences'];
                $amount = $_POST['mercanet_recurring_amount'];

                if (filter_var($nb_occurences, FILTER_VALIDATE_INT) === false) {
                    $errors[] = __('The occurence number must contain only numeric.', 'mercanet');
                }
                if (filter_var($amount, FILTER_VALIDATE_INT) === false) {
                    $errors[] = __('The amount must contain only numeric.', 'mercanet');
                }
            }

            if (!empty($errors)) {
                foreach ($errors as $key => $value) {
                    WC_Admin_Settings::add_error($value);
                }
            }
        }

        public static function save_recurring_payment() {
            self::validate_recurring_admin();
            if (!empty($_POST['mercanet_type']) &&
                    !empty($_POST['mercanet_periodicity']) &&
                    !empty($_POST['mercanet_number_occurrences']) &&
                    !empty($_POST['mercanet_recurring_amount'])) {

                global $wpdb;
                $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_payment_recurring WHERE id_mercanet_payment_recurring = '{$_POST['id_mercanet_payment_recurring']}'");

                if (!empty($_POST['id_mercanet_payment_recurring'])) {
                    $wpdb->update($wpdb->prefix . 'mercanet_payment_recurring', array(
                        'type' => $_POST['mercanet_type'],
                        'periodicity' => $_POST['mercanet_periodicity'],
                        'number_occurences' => $_POST['mercanet_number_occurrences'],
                        'recurring_amount' => $_POST['mercanet_recurring_amount']
                            ), array(
                        'id_mercanet_payment_recurring' => $_POST['id_mercanet_payment_recurring']
                            )
                    );
                } else {
                    $wpdb->insert($wpdb->prefix . 'mercanet_payment_recurring', array(
                        'id_product' => $_POST['post_ID'],
                        'type' => $_POST['mercanet_type'],
                        'periodicity' => $_POST['mercanet_periodicity'],
                        'number_occurences' => $_POST['mercanet_number_occurrences'],
                        'recurring_amount' => $_POST['mercanet_recurring_amount']
                            )
                    );
                }
            }
        }

        public static function add_mercanet_customer_payment_recurring($order_id, $product_id, $pos_quantity, $is_sdd = false) {
            global $wpdb;
            $transaction = Mercanet_Transaction::get_by_order_id($order_id);
            $id_customer = get_post_meta($order_id, '_customer_user', true);
            $infos_recurring = Mercanet_Recurring_Payment::get_recurring_infos($product_id, $is_sdd);
            $occurence = $infos_recurring[0]->number_occurences;
            $product = new WC_Product($product_id);
            $product->set_price($infos_recurring[0]->recurring_amount);
            $time = strtotime(date("Y/m/d h:i:s"));

            $next_schedule = ($infos_recurring[0]->periodicity == 'D') ?
                    date("Y-m-d h:i:s", strtotime("+$occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$occurence month", $time));
            $wpdb->insert($wpdb->prefix . 'mercanet_customer_payment_recurring', array(
                'id_product' => $product_id,
                'id_tax_rules_group' => $product->get_tax_class(),
                'id_order' => $transaction[0]->order_id,
                'id_customer' => $id_customer,
                'id_mercanet_transaction' => $transaction[0]->transaction_id,
                'status' => ($transaction[0]->response_code == '00') ? self::ID_STATUS_ACTIVE : self::ID_STATUS_PAUSE,
                'periodicity' => $infos_recurring[0]->periodicity,
                'number_occurences' => $occurence,
                'current_occurence' => '0',
                'date_add' => date('Y-m-d h:i:s'),
                'last_schedule' => date('Y-m-d h:i:s'),
                'next_schedule' => $next_schedule,
                'current_specific_price' => $product->get_price_including_tax(),
                'id_cart_paused_currency' => $pos_quantity
                    )
            );
            return (empty($wpdb->last_error)) ? $wpdb->insert_id : false;
        }

        public static function update_mercanet_customer_payment_recurring($id, $params) {
            global $wpdb;
            if (is_object($params))
                $params = json_decode(json_encode($params), True);


            $wpdb->update(
                    $wpdb->prefix . 'mercanet_customer_payment_recurring', $params, array('id_mercanet_customer_payment_recurring' => $id)
            );

            return (empty($wpdb->last_error)) ? true : false;
        }

        public static function remove_mercanet_customer_payment_recurring($order_id, $user_id) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'mercanet_customer_payment_recurring', array('id_order' => $order_id, 'id_customer' => $user_id));
            return (empty($wpdb->last_error)) ? true : false;
        }

        public function check_order() {
            global $wp;
            session_start();
            $order_id = $wp->query_vars['order-pay'];
            $order = wc_get_order($order_id);
            if (isset($_GET['pay_for_order'])) {
                $_SESSION['pay_for_order'] = $order_id;
            }

            if (isset($_SESSION['pay_for_order']) && $_SESSION['pay_for_order'] != $order_id && $order->get_total() != WC()->cart->total) {
                $order->remove_order_items('line_item');
                $order->remove_order_items('tax');
                foreach (WC()->cart->cart_contents as $item) {
                    $order->add_product($item['data']);
                }
                $tax_total = reset(WC()->cart->get_tax_totals());
                $order->add_tax($tax_total->tax_rate_id, WC()->cart->get_taxes_total());
                $order->set_total(WC()->cart->total, 'total');
            }
        }

        public function receipt_page($order_id) {
            $order = wc_get_order($order_id);
            $user_id = get_current_user_id();
            if (empty($order)) {
                return false;
            }
            $return_url = WC()->api_request_url('Mercanet_Gateway_Recurring');
            $is_nx = false;
            $params = Mercanet_Api::get_params($order, $user_id, $return_url, $is_nx);
            // LOG
            if (get_option('mercanet_log_active') == 'yes') {
                $current_user = wp_get_current_user();
                $message = 'Customer: ' . $user_id . ' ' . $current_user->user_firstname . ' ' . $current_user->user_lastname . ' ' . $current_user->display_name;
                $message .= ' || ';
                $message .= ' Params: ';
                $message .= implode(', ', array_map(function ($v, $k) {
                            return $k . '=' . $v;
                        }, $params, array_keys($params)));
                Mercanet_Logger::log($message, Mercanet_Logger::LOG_DEBUG, Mercanet_Logger::FILE_DEBUG);
            }

            $data = Mercanet_Api::get_raw_data($params);
            $seal = Mercanet_Api::generate_seal($data);

            $url = Mercanet_Api::get_payment_url();
            $interface_version = get_option('MERCANET_PAYMENT_PAGE_INTERFACE_VERSION');

            $this->seal = $seal;
            $this->data = $data;
            $this->interface_version = $interface_version;
            $this->url = $url;

            $display_method = get_option('mercanet_display_card_method');
            switch ($display_method) {
                case 'DIRECT':
                case 'DISPLAY_CARDS':
                    echo Mercanet_Payment::generate_direct_payment($seal, $data, $interface_version, $url);
                    break;
                case 'IFRAME':
                    echo Mercanet_Payment::generate_iframe_payment($seal, $data, $interface_version, $url);
                    break;
                default:
                    echo Mercanet_Payment::generate_direct_payment($seal, $data, $interface_version, $url);
                    break;
            }
        }

        public function validate_fields() {
            $display_method = get_option('mercanet_display_card_method');
            if ($display_method == 'DISPLAY_CARDS') {
                WC()->session->set('payment_mean_brand', $_POST['payment_mean_brand']);
            }
        }

        public static function check_mercanet_response($is_sdd = false) {
            global $woocommerce;
            global $wpdb;
            $params = $_POST;

            if (!isset($params['Data'])) {
                wc_add_notice('Problème lors de la récupération des paramètres, veuillez réessayer ultérieurement');
                wp_redirect(wc_get_page_permalink('cart'));
            }

            if ($params['Encode'] == 'base64') {
                $raw_data = base64_decode($params['Data']);
            } else {
                $raw_data = $params['Data'];
            }

            $is_sealed = Mercanet_Api::check_seal($params['Data'], $params['Seal']);
            $data = Mercanet_Api::get_data_from_raw_data($raw_data);
            $transaction_type = Mercanet_Api::PAYMENT;

            $order = new WC_Order($data['orderId']);
            if (!isset($params['Data'])) {
                $order->update_status('failed');
                $order->add_order_note(__('Mercanet response error', 'mercanet'));
            }

            $transact = Mercanet_Transaction::get_by_reference($data['transactionReference']);
            $iframe_redirect = self::check_iframe_redirect($order);

            if (!empty($transact)) {
                $items = $woocommerce->cart->get_cart();

                // TMP : à supprimer lors de la remise en place de l'abo SDD
                if (!$is_sdd) {
                    foreach ($items as $item) {
                        for ($i = intval($item['quantity']); $i > 0; $i--) {
                            $infos = Mercanet_Recurring_Payment::get_recurring_infos($item['product_id'], $is_sdd);
                            $result = $wpdb->get_results(<<<SQL
                        SELECT * FROM {$wpdb->prefix}mercanet_customer_payment_recurring 
                        WHERE id_mercanet_transaction = '{$transact->transaction_id}' AND id_product = '{$item['product_id']}' AND id_cart_paused_currency = '$i'
SQL
                            );
                            if ($item['change_payment_card'] == "1" && !empty($infos)) {
                                $recurring_infos_order = Mercanet_Recurring_Payment::get_mercanet_customer_payment_recurring(
                                                array(
                                                    "id_order" => $item['rf_order_id'],
                                                    "id_product" => $item['product_id'],
                                                    "id_cart_paused_currency" => $i
                                                )
                                );
                                $time = strtotime(date($recurring_infos_order[0]->next_schedule));
                                $occurence = intval($infos[0]->number_occurences);
                                $next_schedule = ($infos[0]->periodicity == 'D') ?
                                        date("Y-m-d h:i:s", strtotime("+$occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$occurence month", $time));

                                self::update_mercanet_customer_payment_recurring(
                                        $recurring_infos_order[0]->id_mercanet_customer_payment_recurring, array(
                                    "next_schedule" => $next_schedule,
                                    "current_occurence" => intval($recurring_infos_order[0]->current_occurence) + 1,
                                    "id_mercanet_transaction" => $transact->transaction_id,
                                    "id_order" => $data['orderId'],
                                    "status" => ($transact->response_code == "00") ? "1" : "2"
                                        )
                                );
                            } else if (!empty($infos) && empty($result)) {
                                $return = self::add_mercanet_customer_payment_recurring($data['orderId'], $item['product_id'], $i, $is_sdd);
                                Mercanet_Schedule::save($data, $raw_data, $transaction_type, $transact->transaction_id);
                                if ($return && isset($item['rf_order_id'])) {
                                    self::remove_mercanet_customer_payment_recurring($item['rf_order_id'], get_current_user_id());
                                    $time = strtotime(date("Y/m/d h:i:s"));
                                    $occurence = $infos[0]->number_occurences;
                                    $next_schedule = ($infos[0]->periodicity == 'D') ?
                                            date("Y-m-d h:i:s", strtotime("+$occurence day", $time)) : date("Y-m-d h:i:s", strtotime("+$occurence month", $time));
                                    self::update_mercanet_customer_payment_recurring($return, array("next_schedule" => $next_schedule));
                                }
                            }
                        }
                    }
                }
                if ($iframe_redirect) {
                    echo $iframe_redirect;
                    exit;
                }

                if ($transact->transaction_reference == $data['transactionReference']) {
                    wp_redirect($order->get_checkout_order_received_url());
                    exit;
                }
            }
            $transaction_id = Mercanet_Transaction::save($data, $raw_data, $transaction_type);
            if($transaction_id){
                if ($data['responseCode'] == '00' && $order->get_total() == floatval($data['amount'] / 100) && $is_sealed) {
                    $order->payment_complete($data['transactionReference']);
                    $woocommerce->cart->empty_cart();
                    $order->add_order_note(__('Payment accepted', 'mercanet'));
                } else {
                    $order->update_status('failed');
                    if ($data['responseCode'] != '00') {
                        $order->add_order_note('responseCode error =' . $data['responseCode']);
                    }

                    if (floatval($order->get_total()) != floatval($data['amount'])) {
                        $order->add_order_note('amount error =' . $data['amount'] . ' compute =' . $order->get_total());
                    }

                    if (!$is_sealed) {
                        $order->add_order_note('seal error =' . $params['Seal'] . ' Data=' . $raw_data);
                    }

                    $order->add_order_note(__('Mercanet response error', 'mercanet'));
                }
            }
            if ($iframe_redirect) {
                echo $iframe_redirect;
                exit;
            }

            wp_redirect($order->get_checkout_order_received_url());
            exit;
        }

    }

}
