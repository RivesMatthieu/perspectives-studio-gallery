<?php
// Check if the module is actived
if (!get_option('mercanet_activation_key') == '') {
    add_action('plugins_loaded', 'init_mercanet_gateway_onetime_class');
    add_filter('woocommerce_payment_gateways', 'add_mercanet_gateway_onetime_class');
}

function init_mercanet_gateway_onetime_class() {

    function add_mercanet_gateway_onetime_class($methods) {
        $methods[] = 'Mercanet_Gateway_Onetime';
        return $methods;
    }

    /**
     * Mercanet One-time Gateway Class
     */
    class Mercanet_Gateway_Onetime extends WC_Payment_Gateway {

        const CARDS_MIF = 'CB,VISA,MASTERCARD,AMEX';

        private static $CARDS_MODIF = array(
            "CARTE AURORE" => "AURORE,AURORE_LECLERC"
        );

        public function __construct() {
            $this->id = 'mercanet_onetime';
            $this->method_title = __('Mercanet one-time payment', 'mercanet');
            $this->has_fields = true;
            $this->title = __('One-time Payment', 'mercanet');
            $this->order_button_text = __('Pay with Mercanet', 'mercanet');
            if (Mercanet_Api::is_allowed(array(
                        'REM'))) {
                $this->supports = array(
                    'refunds'
                );
            }
            $this->init_settings();
            $this->init_form_fields();

            add_action('woocommerce_api_' . strtolower(get_class($this)), array(
                $this,
                'check_mercanet_response'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'validate_admin_onetime'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'save_title_names'));
            add_action('woocommerce_thankyou_' . $this->id, array(
                $this,
                'thankyou_page'));
            add_action('woocommerce_receipt_' . $this->id, array(
                $this,
                'receipt_page'));
            add_action('woocommerce_available_payment_gateways', array(
                $this,
                'check_gateway'));

            // Gateway name
            $this->onetime_title_names = get_option('mercanet_onetime_title_names');
            if (empty($this->onetime_title_names)) {
                $this->onetime_title_names = array(
                    'en_US' => array(
                        'title_name' => 'Card payment secured by Mercanet',
                    ),
                    'fr_FR' => array(
                        'title_name' => 'Paiement sécurisé par carte via Mercanet',
                    )
                );
            }

            $locale = Mercanet_Api::get_locale();
            if (!isset($this->onetime_title_names[$locale])) {
                $title_locale = $this->onetime_title_names['en_US'];
            } else {
                $title_locale = $this->onetime_title_names[$locale];
            }

            $this->settings['mercanet_onetime_title'] = $title_locale['title_name'];
            $this->title = $this->settings['mercanet_onetime_title'];
        }

        /**
         * Save the gateways names by languages
         */
        public function save_title_names() {
            $locale = get_locale();
            $title_names = $_POST['title_name'];
            $names = array();

            foreach ($title_names as $lang => $name) {
                if ($lang == $locale) {
                    if (empty(trim($name))) {
                        $errors[] = __('You have to choose a name for the onetime payment in the current language.', 'mercanet');
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
                update_option('mercanet_onetime_title_names', $names);
            }
        }

        public function load_front_css_js() {
            $plugin_base_name = explode('/', plugin_basename(__FILE__))[0];
            wp_register_style('mercanet-front', plugins_url("$plugin_base_name/assets/css/front.css"));
            wp_enqueue_style('mercanet-front');
            wp_register_script('mercanet-front', plugins_url("$plugin_base_name/assets/js/front.js"));
            wp_enqueue_script('mercanet-front');
        }

        public function thankyou_page($order_id) {
            $transaction = Mercanet_Transaction::get_by_order_id($order_id);
            if (!empty($transaction)) {
                echo '<ul class="order_details">';
                echo '<li class="method">' . __('Authorisation ID', 'mercanet') . '<strong>' . $transaction[0]->authorization_id . '</strong></li>';
                echo '</ul>';
            }
        }

        public function validate_admin_onetime() {
            $errors = array();

            if (isset($_POST['woocommerce_mercanet_onetime_mercanet_onetime_min_amount']) && isset($_POST['woocommerce_mercanet_onetime_mercanet_onetime_max_amount'])) {
                $min_amount = $_POST['woocommerce_mercanet_onetime_mercanet_onetime_min_amount'];
                $max_amount = $_POST['woocommerce_mercanet_onetime_mercanet_onetime_max_amount'];

                if (!empty($min_amount) && ( filter_var($min_amount, FILTER_VALIDATE_INT) === false || filter_var($min_amount, FILTER_VALIDATE_FLOAT) === false )) {
                    $errors[] = __('The minimum amount must contain only numeric.', 'mercanet');
                }
                if (!empty($max_amount) && ( filter_var($max_amount, FILTER_VALIDATE_INT) === false || filter_var($max_amount, FILTER_VALIDATE_FLOAT) === false )) {
                    $errors[] = __('The maximum amount must contain only numeric.', 'mercanet');
                }
                if (!empty($min_amount) && !empty($max_amount)) {
                    if ($min_amount >= $max_amount) {
                        $errors[] = __('The minimum amount can not be greater or equal than the maximum amount.', 'mercanet');
                    }
                    if ($max_amount <= $min_amount) {
                        $errors[] = __('The maximum amount can not be smaller or equal than the minimum amount.', 'mercanet');
                    }
                }
            }

            if (!empty($errors)) {
                $this->errors = $errors;
                foreach ($errors as $key => $value) {
                    WC_Admin_Settings::add_error($value);
                }
            }
        }

        /**
         * Generate gateway names form
         */
        public function generate_title_name_html() {
            ob_start();
            $languages = get_available_languages();
            $locale = Mercanet_Api::get_locale();
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations();
            $title = $this->onetime_title_names['en_US'];
            ?>
            <tr valign="top">
            <p class="description">
            <?php echo __('Wording payment in one time', 'mercanet'); ?>
            </p>
            </tr>
            <tr valign="top">
                <th class="titledesc">English (United States)</th>
                <td class="forminp"><input type="text" style="min-width:500px" value="<?php echo esc_attr($title['title_name']); ?>" name="title_name[en_US]" /></td>
            </tr>
            <?php
            foreach ($languages as $lang) {
                $translation = $translations[$lang];
                $native_name = $translation['native_name'];
                $language = $translation['language'];

                if (isset($this->onetime_title_names[$lang])) {
                    $title = $this->onetime_title_names[$lang];
                    ?>
                    <tr valign="top">
                        <?php
                        echo '<th class="titledesc">' . esc_attr($native_name) . '</th>' .
                        '<td class="forminp"><input type="text" style="min-width:500px" value="' . esc_attr($title['title_name']) . '" name="title_name[' . $lang . ']" /></td>';
                        ?>
                    </tr>
                    <?php } else { ?>
                    <tr valign="top">
                        <?php
                        echo '<th class="titledesc">' . esc_attr($native_name) . '</th>' .
                        '<td class="forminp"><input type="text" style="min-width:500px" name="title_name[' . $lang . ']" /></td>';
                        ?>
                    </tr>
                    <?php
                }
            }
            return ob_get_clean();
        }

        /**
         * One-time gateway admin form
         */
        public function init_form_fields() {
            $this->form_fields = array(
                array(
                    'title' => __('LABEL ONE-TIME PAYMENT', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Label', 'woocommerce'),
                    'default' => __('One-Time Payment by Mercanet', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'mercanet_onetime_title' => array(
                    'type' => 'title_name'
                ),
                'enabled' => array(
                    'title' => __('Activation', 'mercanet'),
                    'type' => 'checkbox',
                    'label' => __('Active One Time Payment', 'mercanet'),
                    'default' => 'yes'
                ),
                array(
                    'title' => __('RESTRICTION ON THE AMOUNTS', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'mercanet_onetime_min_amount' => array(
                    'title' => __('Minimum amount', 'mercanet'),
                    'type' => 'text',
                    'description' => __('Minimum amount for which this payment method is available', 'mercanet'),
                ),
                'mercanet_onetime_max_amount' => array(
                    'title' => __('Maximum amount', 'mercanet'),
                    'type' => 'text',
                    'description' => __('Maximum amount for which this payment method is available', 'mercanet'),
                ),
            );

            if (get_option('label_translate_on') == 'yes') {
                unset($this->form_fields['title']);
            } else {
                unset($this->form_fields['mercanet_onetime_title']);
            }
        }

        /**
         * Check if the gatteway is allowed for the order amount
         *
         * @param array
         * @return array
         */
        public function check_gateway($gateways) {
            if (isset($gateways[$this->id])) {
                if ($gateways[$this->id]->id == $this->id) {
                    $order_amount = WC_Payment_Gateway::get_order_total();
                    $min_amount = floatval($this->settings['mercanet_onetime_min_amount']);
                    $max_amount = floatval($this->settings['mercanet_onetime_max_amount']);
                    if (!empty($min_amount)) {
                        if (!( $order_amount > $min_amount )) {
                            unset($gateways[$this->id]);
                        }
                    }
                    if (!empty($max_amount)) {
                        if (!( $order_amount < $max_amount )) {
                            unset($gateways[$this->id]);
                        }
                    }
                }
            }
            return $gateways;
        }

        public function process_payment($order_id) {
            $order = new WC_Order($order_id);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        public function payment_fields() {
            global $wp;
            $this->load_front_css_js();
            $display_method = get_option('mercanet_display_card_method');
            $html_list_payment = <<<HTML
                <input type="hidden" id="payment_option_one" name="payment_option_one" />
                <input type="hidden" id="payment_mean_brand_one" name="payment_mean_brand_one" />
HTML;
            switch ($display_method) {
                case 'DIRECT':
                case 'IFRAME':
                    $this->has_fields = false;
                    break;
                case 'DISPLAY_CARDS':
                    $cards_list = get_option('mercanet_card_allowed');
                    if (!empty($cards_list)) {
                        if (in_array('ALL', $cards_list)) {
                            $cards_list = Mercanet_Admin_General::available_cards();
                            $cards_to_disable = explode(',', Mercanet_Api::CARDS_WITHOUT_TRI_TO_DISABLE);
                            foreach ($cards_to_disable as $card) {
                                if (isset($cards_list[$card])) {
                                    unset($cards_list[$card]);
                                }
                            }
                        } else {
                            foreach ($cards_list as $key => $card) {
                                $cards_list[$card] = $card;
                                unset($cards_list[$key]);
                            }
                        }

                        if (!empty(WC()->session->total)) {
                            $order_total = WC()->session->total;
                        } else {
                            $order_id = absint($wp->query_vars['order-pay']);
                            $order = new WC_Order($order_id);
                            $order_total = $order->get_total();
                        }

                        if (!empty($order_total)) {
                            if (!Mercanet_Api::is_allowed(array('NCB')) || !is_between($order_total, 100, 3000)) {
                                if (isset($cards_list['NxCB'])) {
                                    unset($cards_list['NxCB']);
                                }
                            }
                            // control cetelem
                            if (!Mercanet_Api::is_allowed(array('FCB')) || !is_between($order_total, 100, 3000)) {
                                if (isset($cards_list['CETELEM_3X'])) {
                                    unset($cards_list['CETELEM_3X']);
                                }
                                if (isset($cards_list['CETELEM_4X'])) {
                                    unset($cards_list['CETELEM_4X']);
                                }
                            }
                        }
                        $html_list_payment .= <<<HTML
                            <div id="mercanet_one_time_cards">
HTML;
                        $html_list_card = "";
                        $img_mif = $cards_mif = "";
                        foreach ($cards_list as $card => $name) {
                            $icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__DIR__)) . '/assets/img/' . strtoupper($card) . '.png';
                            if (strpos(self::CARDS_MIF, $card) !== false) {
                                $img_mif .= "<img style='display:block;' style='float:right;' src='$icon' title='$name' alt='$name'/>";
                                $cards_mif .= "$card,";
                                continue;
                            }
                            if (!empty(self::$CARDS_MODIF[$card])) {
                                $card = self::$CARDS_MODIF[$card];
                            }
                            $html_list_card .= <<<HTML
                                <div class="mercanet-display-card">
                                    <button onClick=check_option(null,'$card','one') value="$card">
                                        <img style="display:block;" style="float:right;" src="$icon" title="$name" alt="$name"/>
                                        <p> Paiement par $name </p>
                                    </button>
                                </div>
HTML;
                        }
                        $cards_mif = substr($cards_mif, 0, -1);
                        $html_list_payment .= <<<HTML
                            <div class="mercanet-display-card mercanet-display-card-mif">
                                <button onClick=check_option(null,'$cards_mif','one') value="MIF">    
                                    $img_mif
                                    <p> Paiement par carte bancaire </p>
                                </button>
                            </div>
                            $html_list_card
                            <div class="mercanet-clear" style="padding:0">
                        </div>
                    </div>
HTML;
                    }
                    break;
                default:
                    $this->has_fields = false;
                    break;
            }
            echo $html_list_payment;
        }

        public function validate_fields() {
            $display_method = get_option('mercanet_display_card_method');
            if ($display_method == 'DISPLAY_CARDS') {
                if (!isset($_POST['payment_mean_brand_one'])) {
                    wc_add_notice(__('Please choose a card to proceed at the payment.', 'mercanet'), 'error');
                } else {
                    WC()->session->set('payment_mean_brand', $_POST['payment_mean_brand_one']);
                }
            }
        }

        public function receipt_page($order_id) {
            $order = new WC_Order($order_id);
            $user_id = get_current_user_id();

            if (empty($order)) {
                return false;
            }
            $return_url = WC()->api_request_url('Mercanet_Gateway_Onetime');
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

            $order->calculate_shipping();

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

        /**
         * Check the Mercanet response, save the transaction, complete the order and redirect
         */
        public static function check_mercanet_response() {
            global $woocommerce;
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
            $transact = Mercanet_Transaction::get_by_reference($data['transactionReference']);
            $iframe_redirect = self::check_iframe_redirect($order);
            if (!empty($transact)) {
                if ($transact->transaction_reference == $data['transactionReference']) {
                    if ($iframe_redirect) {
                        echo $iframe_redirect;
                        exit;
                    }
                    // redirect to the parent page
                    wp_redirect($order->get_checkout_order_received_url());
                    exit;
                }
            }

            $transaction_id = Mercanet_Transaction::save($data, $raw_data, $transaction_type);

             // check pour delai passé avec commande deja crée
            $already_create = false;
            if ($data['responseCode'] == '97') {
                $result = Mercanet_Transaction::get_by_order_complete($data['orderId']);
                if ($result) {
                    $already_create = true;
                }
            }
            if($transaction_id && !$already_create){
                if ($data['responseCode'] == '00' && $order->get_total() == floatval($data['amount'] / 100) && $is_sealed) {
                    $order->payment_complete($data['transactionReference']);
                    $woocommerce->cart->empty_cart();
                    $order->add_order_note(__('Payment accepted', 'mercanet'));
                } else {
                    $order->update_status('failed');
                    if ($data['responseCode'] != '00') {
                        $order->add_order_note('responseCode error = ' . $data['responseCode']);
                    }

                    if (floatval($order->get_total()) != floatval($data['amount']) / 100) {
                        $order->add_order_note('amount error =' . $data['amount'] . ' compute = ' . $order->get_total());
                    }

                    if (!$is_sealed) {
                        $order->add_order_note('seal error = ' . $params['Seal'] . ' Data=' . $raw_data);
                    }

                    $order->add_order_note(__('Mercanet response error', 'mercanet'));
                }
            }
            if ($iframe_redirect) {
                echo $iframe_redirect;
                exit;
            }
            // redirect to the parent page
            wp_redirect($order->get_checkout_order_received_url());
            exit;
        }

        public function process_refund($order_id, $amount = null, $reason = '') {
            if (!Mercanet_Api::is_allowed(array(
                        'REM'))) {
                $error = new WP_Error();
                $error->add('mercanet_refund', __('You can not use the refund with your merchant options', 'mercanet'));
            }

            $order = wc_get_order($order_id);

            if (empty($order)) {
                return false;
            }

            include_once plugin_dir_path(__DIR__) . 'includes/class-mercanet-refund.php';

            // Diagnostic
            $refund = Mercanet_Refund::refund($order, $amount);

            if (empty($refund)) {
                return false;
            }

            return $refund;
        }

        public static function check_iframe_redirect($order) {
            $display_method = get_option('mercanet_display_card_method');
            $script_redirect_iframe = false;
            if ($display_method == 'IFRAME') {
                $script_redirect_iframe = <<<JS
                <script type="text/javascript">
                    top.location.href='{$order->get_checkout_order_received_url()}';
                </script>
JS;
            }

            return $script_redirect_iframe;
        }

    }

}
