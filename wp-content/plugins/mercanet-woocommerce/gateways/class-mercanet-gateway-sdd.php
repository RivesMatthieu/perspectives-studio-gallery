<?php

// Check if the module is actived
if (!get_option('mercanet_activation_key') == '' && Mercanet_Api::is_allowed(array('SEP'))) {
    add_action('plugins_loaded', 'init_mercanet_gateway_sdd_class');
    add_filter('woocommerce_payment_gateways', 'add_mercanet_gateway_sdd_class');
    add_action('save_post', 'Mercanet_Gateway_Sdd::save_recurring_sdd_payment');
}

function init_mercanet_gateway_sdd_class() {

    function add_mercanet_gateway_sdd_class($methods) {
        $methods[] = 'Mercanet_Gateway_Sdd';
        return $methods;
    }

    /**
     * Mercanet Nx Gateway Class
     */
    class Mercanet_Gateway_Sdd extends Mercanet_Gateway_Recurring {

        const sdd_libelle = "SEPA_DIRECT_DEBIT";

        public $is_abo = false;

        /**
         * Constructor
         */
        public function __construct() {
            $this->id = 'mercanet_sdd';
            $this->method_title = __('Mercanet SDD payment', 'mercanet');
            $this->has_fields = true;
            $this->title = __('SDD Payment', 'mercanet');
            $this->order_button_text = __('Pay with Mercanet', 'mercanet');

            $this->init_settings();
            $this->init_form_fields();
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_title_names'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_filter('woocommerce_after_checkout_billing_form', array($this, 'custom_checkout_field'), 10, 2);
            //add_filter('woocommerce_checkout_fields', array($this, 'check_transaction_actor'), 10, 2);

            // Gateways name
            $this->onetime_title_names = get_option('mercanet_sdd_title_names');
            if (empty($this->onetime_title_names)) {
                $this->onetime_title_names = array(
                    'en_US' => array(
                        'title_name' => 'SDD payment secured by Mercanet',
                    ),
                    'fr_FR' => array(
                        'title_name' => 'Paiement SDD via Mercanet',
                    )
                );
            }

            $locale = Mercanet_Api::get_locale();
            if (!isset($this->onetime_title_names[$locale])) {
                $title_locale = $this->onetime_title_names['en_US'];
            } else {
                $title_locale = $this->onetime_title_names[$locale];
            }
            $this->settings['mercanet_sdd_title'] = $title_locale['title_name'];
            $this->title = $this->settings['mercanet_sdd_title'];

            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_available_payment_gateways', array($this, 'check_gateway'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_mercanet_response'));

            include_once plugin_dir_path(__DIR__) . 'settings/mercanet-settings.php';
        }

        public static function mercanet_sdd_recurrent_is_allow() {
            $is_recurrent_sdd = false;
            $mercanet_sdd_settings = get_option('woocommerce_mercanet_sdd_settings');
            foreach ($mercanet_sdd_settings['mercanet_ssd_mandate_usage'] as $mercanet_ssd_authent_method) {
                if ($mercanet_ssd_authent_method === "RECURRENT") {
                    $is_recurrent_sdd = true;
                }
            }
            return $is_recurrent_sdd;
        }

        public static function add_form_mercenat_sdd_payment_admin() {
            $product = Mercanet_Recurring_Payment::get_recurring_infos(get_the_ID(), true);
            $form_fields = array(
                array(
                    'type' => 'hidden',
                    'id' => 'id_mercanet_payment_recurring_sdd',
                    'name' => 'id_mercanet_payment_recurring_sdd',
                    'value' => (!empty($product[0]->id_mercanet_payment_recurring)) ? $product[0]->id_mercanet_payment_recurring : ''
                ),
                array(
                    'type' => 'hidden',
                    'id' => 'mercanet_type_sdd',
                    'name' => 'mercanet_type_sdd',
                    'value' => '4'
                ),
                array(
                    'label' => __('Periodicity', 'mercanet'),
                    'type' => 'select',
                    'style' => 'width: 95%',
                    'id' => 'mercanet_periodicity_sdd',
                    'name' => 'mercanet_periodicity_sdd',
                    'value' => (!empty($product[0]->periodicity)) ? $product[0]->periodicity : '',
                    'options' => array(
                        'D' => __('Day', 'mercanet'),
                        'M' => __('Month', 'mercanet')
                    )
                ), array(
                    'label' => __('Number of occurrences', 'mercanet'),
                    'type' => 'text',
                    'id' => 'mercanet_number_occurrences_sdd',
                    'name' => 'mercanet_number_occurrences_sdd',
                    'value' => (!empty($product[0]->number_occurences)) ? $product[0]->number_occurences : ''
                ), array(
                    'label' => __('Recurring amount', 'mercanet'),
                    'type' => 'text',
                    'id' => 'mercanet_recurring_amount_sdd',
                    'name' => 'mercanet_recurring_amount_sdd',
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

            if (!empty($_POST['mercanet_number_occurrences_sdd']) && !empty($_POST['mercanet_recurring_amount_sdd'])) {

                $nb_occurences = $_POST['mercanet_number_occurrences_sdd'];
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

        public static function save_recurring_sdd_payment() {
            self::validate_recurring_admin();
            if (!empty($_POST['mercanet_type_sdd']) &&
                    !empty($_POST['mercanet_periodicity_sdd']) &&
                    !empty($_POST['mercanet_number_occurrences_sdd']) &&
                    !empty($_POST['mercanet_recurring_amount_sdd'])) {


                global $wpdb;
                if (!empty($_POST['id_mercanet_payment_recurring_sdd'])) {
                    $wpdb->update($wpdb->prefix . 'mercanet_payment_recurring', array(
                        'type' => $_POST['mercanet_type_sdd'],
                        'periodicity' => $_POST['mercanet_periodicity_sdd'],
                        'number_occurences' => $_POST['mercanet_number_occurrences_sdd'],
                        'recurring_amount' => $_POST['mercanet_recurring_amount_sdd']
                            ), array(
                        'id_mercanet_payment_recurring' => $_POST['id_mercanet_payment_recurring_sdd']
                            )
                    );
                } else {
                    $wpdb->insert($wpdb->prefix . 'mercanet_payment_recurring', array(
                        'id_product' => $_POST['post_ID'],
                        'type' => $_POST['mercanet_type_sdd'],
                        'periodicity' => $_POST['mercanet_periodicity_sdd'],
                        'number_occurences' => $_POST['mercanet_number_occurrences_sdd'],
                        'recurring_amount' => $_POST['mercanet_recurring_amount_sdd']
                            )
                    );
                }
            }
        }

        /**
         * Check if the gatteway is allowed for the order amount
         *
         * @param array
         * @return array
         */
        public function check_gateway($gateways) {
            global $woocommerce, $wp;

            if (isset($gateways[$this->id])) {
                $is_abo = false;
                if (is_object($woocommerce->cart) && sizeof($woocommerce->cart->get_cart()) > 0) {
                    $items = $woocommerce->cart->get_cart();
                    foreach ($items as $item) {
                        $infos = Mercanet_Recurring_Payment::get_recurring_infos($item['product_id'], true);
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
                                Mercanet_Recurring_Payment::get_recurring_infos($item->get_product_id(), true) :
                                Mercanet_Recurring_Payment::get_recurring_infos($item['item_meta']['_product_id'][0], true);

                        if (!empty($infos)) {
                            $is_abo = true;
                        }
                    }
                }

                $this->is_abo = $is_abo;
            }
            return $gateways;
        }

        /**
         * One-time gateway admin form
         * 
         * @return void
         */
        public function init_form_fields() {
            $this->form_fields = array(
                array(
                    'title' => __('LABEL SDD PAYMENT', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'class' => 'mercanet_sdd_label_origin',
                    'description' => __('Label', 'woocommerce'),
                    'default' => __('SDD Payment by Mercanet', 'mercanet'),
                    'desc_tip' => true,
                ),
                'mercanet_sdd_title' => array(
                    'title' => __('*Name of the payment', 'mercanet'),
                    'type' => 'title_name'
                ),
                'enabled' => array(
                    'title' => __('Activation', 'mercanet'),
                    'type' => 'checkbox',
                    'label' => __('Active SDD Payment', 'mercanet'),
                    'default' => 'yes',
                ),
                array(
                    'title' => __('SDD options', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'mercanet_ssd_mandate_usage' => array(
                    'title' => __('Mandate type', 'mercanet'),
                    'description' => __('Select mandate type to allow', 'mercanet'),
                    'type' => 'select',
                    'css' => 'width: 500px;',
                    'default' => "RECURRENT",
                    'options' => array(
                        'ONE_OFF' => __('Unitary mandate', 'mercanet'),
                        'RECURRENT' => __('Recurring mandate', 'mercanet')
                    ),
                    'id' => 'mercanet_ssd_mandate_usage'
                ),
                'mercanet_ssd_authent_method' => array(
                    'title' => __('Validation mandate type', 'mercanet'),
                    'description' => __('Select validation mandate type to allow', 'mercanet'),
                    'type' => 'select',
                    'css' => 'width: 500px;',
                    'default' => "3D_SECURE",
                    'options' => array(
                        '3D_SECURE' => __('Electronic validattion by 3D secure', 'mercanet'),
                        'SMS_OTP' => __('Electronic validattion by SMS', 'mercanet'),
                        'MAIL_OTP' => __('Electronic validattion by mail', 'mercanet')
                    ),
                    'id' => 'mercanet_ssd_authent_method'
                ),
                'mercanet_sdd_transaction_actors' => array(
//                    'title' => __('Transaction actors', 'mercanet'),
//                    'description' => __('Select mandate transaction type (by default BTOC)', 'mercanet'),
                    'type' => 'hidden',
//                    'type' => 'select',
//                    'css' => 'width: 500px;',
                    'value' => "BTOC",
                    'default' => "BTOC",
//                    'options' => array(
//                        'BTOC' => __('Core mandate - BTOC', 'mercanet'),
//                        'BTOB' => __('B2B mandate - BTOB', 'mercanet'),
//                        'BTOF' => __('Core company mandate - BTOF', 'mercanet')
//                    ),
                    'id' => 'mercanet_sdd_transaction_actors'
                ),
                'mercanet_ssd_mandate_certificationtype' => array(
                    'type' => 'hidden',
                    'default' => "E_BASIC",
                    'id' => 'mercanet_ssd_mandate_certificationtype',
                )
            );
            if (get_option('label_translate_on') == 'yes') {
                unset($this->form_fields['title']);
            }
        }

        /**
         * MAJ titre module
         * 
         * @return void
         */
        public function save_title_names() {
            $locale = get_locale();
            $title_names = $_POST['title_name'];
            $names = array();

            foreach ($title_names as $lang => $name) {
                if ($lang == $locale) {
                    if (empty($name)) {
                        $errors[] = __('You have to choose a name for the SDD payment in the current language.', 'mercanet');
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
                update_option('mercanet_sdd_title_names', $names);
            }
        }

        /**
         * Valide les inputs
         * 
         * @return void
         */
        public function validate_fields() {
            $display_method = get_option('mercanet_display_card_method');
            if (!isset($_POST['payment_mean_brand_sdd'])) {
                wc_add_notice(__('Please choose a card to proceed at the payment.', 'mercanet'), 'error');
            } else {
                WC()->session->set('payment_mean_brand_sdd', $_POST['payment_mean_brand_sdd']);
            }
        }

        /**
         * Génère la redirection vers mercanet
         * 
         * @param string $order_id
         * @return boolean
         */
        public function receipt_page($order_id) {
            $order = new WC_Order($order_id);
            $user_id = get_current_user_id();

            if (empty($order)) {
                return false;
            }

            $return_url = WC()->api_request_url('Mercanet_Gateway_Sdd');
            $gateway = $this->id;
            $is_sdd = true;
            $params = Mercanet_Api::get_params($order, $user_id, $return_url, $is_sdd);
            $params = $this->get_sdd_params($params, $order);
            $data = Mercanet_Api::get_raw_data($params);
            $seal = Mercanet_Api::generate_seal($data);
            $url = Mercanet_Api::get_payment_url();
            $interface_version = get_option('MERCANET_PAYMENT_PAGE_INTERFACE_VERSION');
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
                    echo $this->generate_direct_payment($seal, $data, $interface_version, $url);
                    break;
            }
        }

        public static function check_mercanet_response($is_sdd = true) {
            parent::check_mercanet_response(true);
        }

        /**
         * Ajoute les params spécifique au mode de paiement SDD
         * 
         * @param array $params
         * @param object $order
         * @return array
         */
        public function get_sdd_params($params, $order) {
            $payment_mean_brand = (is_object(WC()->session)) ? WC()->session->get('payment_mean_brand_sdd') : "";
            $params['paymentMeanBrandList'] = self::sdd_libelle;
            $params['paymentMeanData.sdd.mandateAuthentMethod'] = Mercanet_Api::check_regexp_data("ANS", $this->settings['mercanet_ssd_authent_method'], 20);
            $params['paymentMeanData.sdd.mandateUsage'] = $payment_mean_brand;
            $params['paymentMeanData.sdd.mandateCertificationType'] = Mercanet_Api::check_regexp_data("ANS", $this->settings['mercanet_ssd_mandate_certificationtype'], 20);
            $params['customerContact.email'] = Mercanet_Api::check_regexp_data("EMAIL", $order->billing_email, 128);
            $params['customerContact.firstname'] = Mercanet_Api::check_regexp_data("ANU-R", $order->billing_first_name, 50);
            $params['customerContact.gender'] = "M";
            $params['customerContact.lastname'] = Mercanet_Api::check_regexp_data("ANU-R", $order->billing_last_name, 50);
            $params['customerAddress.street'] = Mercanet_Api::check_regexp_data("ANU-R", $order->billing_address_1, 50);
            $params['customerAddress.streetNumber'] = "";
            $params['customerAddress.city'] = Mercanet_Api::check_regexp_data("ANU-R", $order->billing_city, 50);
            $params['customerAddress.zipCode'] = Mercanet_Api::check_regexp_data("AN-R", $order->billing_postcode, 10);
            $iso_code = 'FRA';
            $iso_convert = Mercanet_Api::getAvailableCountries();
            if (key_exists($order->billing_country, $iso_convert)) {
                $iso_code = $iso_convert[$order->billing_country]['id'];
            }
            $params['customerAddress.country'] = $iso_code;

            if ($params['paymentMeanData.sdd.mandateAuthentMethod'] === "SMS_OTP") {
                $mobile_phone = Mercanet_Api::check_regexp_data("MOBILE", array('iso_code' => $order->billing_country, "phone" => $_SESSION['sdd_mobile_phone']), 50);
                unset($_SESSION['sdd_mobile_phone']);
                if ($mobile_phone !== "") {
                    $params['customerContact.mobile'] = $mobile_phone;
                }
            }

            $params['transactionActors'] = "BTOC";
//            $params['transactionActors'] = Mercanet_Api::check_regexp_data("ANS", $this->settings['mercanet_sdd_transaction_actors'], 20);
//            if ($params['transactionActors'] == "BTOF" || $params['transactionActors'] == "BTOB") {
//                $params['customerContact.legalId'] = Mercanet_Api::check_regexp_data("ANS", "", 14);
//                $params['customerContact.positionOccupied'] = "";
//                $params['customerAddress.company'] = Mercanet_Api::check_regexp_data("ANS", $order->billing_company, 50);
//                $params['customerAddress.businessName'] = Mercanet_Api::check_regexp_data("ANS", $order->billing_company, 50);
//            }

            return $params;
        }

        public function payment_fields() {
            $this->load_front_css_js();
            $html_list_payment = <<<HTML
                <input type="hidden" id="payment_mean_brand_sdd" name="payment_mean_brand_sdd" value="{$this->settings['mercanet_ssd_mandate_usage']}"/>
HTML;
            $display_method = get_option('mercanet_display_card_method');
            switch ($display_method) {
                case 'IFRAME':
                    $this->has_fields = false;
                    break;
                case 'DIRECT':
                case 'DISPLAY_CARDS':
                    break;
                default:
                    $this->has_fields = false;
                    break;
            }
            echo $html_list_payment;
        }

        public function check_transaction_actor($checkout_fields) {
            $transaction_actor = Mercanet_Api::check_regexp_data("ANS", $this->settings['mercanet_sdd_transaction_actors'], 20);
            if ($transaction_actor == "BTOF" || $transaction_actor == "BTOB") {
                $checkout_fields['billing']['billing_company']['required'] = true;
            }
            return $checkout_fields;
        }

        public function custom_checkout_field($checkout) {
            woocommerce_form_field('sdd_mobile_phone', array(
                'type' => 'text',
                'class' =>  array(
                    'form-row-wide validate-phone'
                ),
                'required' => ($this->settings['mercanet_ssd_authent_method'] === "SMS_OTP") ? true : false,
                'label' => __('Mobile number (required for electronic mandate)', 'mercanet'),
                'description' => __('A valid mobile number is required for electronic mandate', 'mercanet'),
            ),$checkout->get_value('sdd_mobile_phone'));            
        }
    }
}

add_action('woocommerce_checkout_process', 'check_sdd_field_process');
function check_sdd_field_process() {
    if ($_POST['payment_method'] === "mercanet_sdd") {
        $mercanet_sdd_settings = get_option('woocommerce_mercanet_sdd_settings');
//        if (($mercanet_sdd_settings['mercanet_sdd_transaction_actors'] == "BTOF" || $mercanet_sdd_settings['mercanet_sdd_transaction_actors'] == "BTOB") && empty($_POST['billing_company'])) {
//            wc_add_notice(__('Company name must be filled', 'mercanet'), 'error');
//        }
        
        if ($mercanet_sdd_settings['mercanet_ssd_authent_method'] === "SMS_OTP" && !preg_match('/^0[67][0-9]{8}$/', $_POST['sdd_mobile_phone'])) {
            wc_add_notice(__('Mobile number must not contain spaces, points or hyphens', 'mercanet') . "</pre>", 'error');
            wc_add_notice(__('Mobile number must begin with 06 or 07', 'mercanet') . "</pre>", 'error');
            if (strlen($_POST['billing_phone']) !== 10) {
                wc_add_notice(__('Mobile number is not the right size (10 characters)', 'mercanet'), 'error');
            }
        } else {
            session_start(); 
            $_SESSION['sdd_mobile_phone'] = $_POST['sdd_mobile_phone'];
        }
    }
}
