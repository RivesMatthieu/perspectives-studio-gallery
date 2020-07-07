<?php
// Check if the module is actived
$mercanet_activation_key = get_option('mercanet_activation_key');
if (!empty($mercanet_activation_key) && Mercanet_Api::is_allowed(array(
            'NFO'))) {
    add_action('plugins_loaded', 'init_mercanet_gateway_nx_class');
    add_filter('woocommerce_payment_gateways', 'add_mercanet_gateway_nx_class');
}

function init_mercanet_gateway_nx_class() {

    function add_mercanet_gateway_nx_class($methods) {
        $methods[] = 'Mercanet_Gateway_Nx';
        return $methods;
    }

    /**
     * Mercanet Nx Gateway Class
     */
    class Mercanet_Gateway_Nx extends WC_Payment_Gateway {

        const CARDS_MIF = 'CB,VISA,MASTERCARD,AMEX';

        public function __construct() {
            $this->id = 'mercanet_severaltime';
            $this->method_title = __('Mercanet payment in several times', 'mercanet');
            $this->has_fields = true;
            $this->title = __('Payment in several times', 'mercanet');
            $this->order_button_text = __('Pay with Mercanet', 'mercanet');
            if (Mercanet_Api::is_allowed(array(
                        'REM'))) {
                $this->supports = array(
                    'refunds'
                );
            }

            $this->init_settings();
            $this->init_form_fields();
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'save_nx_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array(
                $this,
                'thankyou_page'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'save_title_names'));

            // Payment options
            // Get all languages installed


            $languages_installed = get_available_languages();

            $translations = $this->mercanet_get_translations();

            $languages = array();
            foreach ($languages_installed as $language) {
                if (isset($translations[$language])) {
                    $languages[$language]['language'] = $language;
                    $languages[$language]['native_name'] = $translations[$language]['native_name'];
                }
            }
            // Add english default lang
            if (!in_array('en_US', $languages)) {
                $languages['en_US']['language'] = 'en_US';
                $languages['en_US']['native_name'] = 'English (United States)';
            }
            $this->languages = $languages;
            $this->payment_options = array();

            foreach ($languages as $iso => $language) {
                $this->payment_options[$iso] = get_option('mercanet_nx_payment_options_' . $iso, array(
                    array(
                        'payment_label' => $this->get_option('payment_label'),
                        'payment_min_amount' => $this->get_option('payment_min_amount'),
                        'payment_max_amount' => $this->get_option('payment_max_amount'),
                        'payment_nb_term' => $this->get_option('payment_min_amount'),
                        'payment_periodicy' => $this->get_option('payment_min_amount'),
                        'payment_first_amount' => $this->get_option('payment_min_amount'),
                        'payment_activation' => $this->get_option('payment_activation')
                    )
                        )
                );
            }

            $init_payment_options = get_option('mercanet_init_payment_options', 'yes');
            if ($init_payment_options == 'yes') {
                $this->default_payment_options();
                update_option('mercanet_init_payment_options', 'no');
            }
            if (empty($this->payment_options)) {
                $this->default_payment_options();
            }

            // Gateways name
            $this->nx_title_names = get_option('mercanet_nx_title_names');
            if (empty($this->nx_title_names)) {
                $this->nx_title_names = array(
                    'en_US' => array(
                        'title_name' => 'Card payment in several times secured by Mercanet',
                    ),
                    'fr_FR' => array(
                        'title_name' => 'Paiement sécurisé par carte en plusieurs fois via Mercanet',
                    )
                );
            }

            $locale = Mercanet_Api::get_locale();
            if (!isset($this->nx_title_names[$locale])) {
                $title_locale = $this->nx_title_names['en_US'];
            } else {
                $title_locale = $this->nx_title_names[$locale];
            }
            $this->settings['mercanet_nx_title'] = $title_locale['title_name'];
            $this->title = $this->settings['mercanet_nx_title'];

            add_action('woocommerce_receipt_' . $this->id, array(
                $this,
                'receipt_page'));
            add_action('woocommerce_available_payment_gateways', array(
                $this,
                'check_gateway'));

            add_action('woocommerce_api_' . strtolower(get_class($this)), array(
                $this,
                'check_mercanet_response'));
            include_once plugin_dir_path(__DIR__) . 'settings/mercanet-settings.php';
        }

        public function mercanet_get_translations() {
            if (!defined('WP_INSTALLING') && false !== ($translations = get_site_transient('available_translations'))) {
                return $translations;
            }

            include(ABSPATH . WPINC . '/version.php'); // include an unmodified $wp_version

            $api = $this->mercanet_translations_api('core', array(
                'version' => $wp_version));

            if (is_wp_error($api) || empty($api['translations'])) {
                return array();
            }

            $translations = array();
            // Key the array with the language code for now.
            foreach ($api['translations'] as $translation) {
                $translations[$translation['language']] = $translation;
            }

            if (!defined('WP_INSTALLING')) {
                set_site_transient('available_translations', $translations, 3 * HOUR_IN_SECONDS);
            }

            return $translations;
        }

        public function mercanet_translations_api($type, $args = null) {
            include( ABSPATH . WPINC . '/version.php' ); // include an unmodified $wp_version

            if (!in_array($type, array(
                        'plugins',
                        'themes',
                        'core'))) {
                return new WP_Error('invalid_type', __('Invalid translation type.'));
            }

            /**
             * Allows a plugin to override the WordPress.org Translation Install API entirely.
             *
             * @since 4.0.0
             *
             * @param bool|array  $result The result object. Default false.
             * @param string      $type   The type of translations being requested.
             * @param object      $args   Translation API arguments.
             */
            $res = apply_filters('translations_api', false, $type, $args);

            if (false === $res) {
                $url = $http_url = 'http://api.wordpress.org/translations/' . $type . '/1.0/';
                if ($ssl = wp_http_supports(array(
                    'ssl'))) {
                    $url = set_url_scheme($url, 'https');
                }

                $options = array(
                    'timeout' => 3,
                    'body' => array(
                        'wp_version' => $wp_version,
                        'locale' => get_locale(),
                        'version' => $args['version'], // Version of plugin, theme or core
                    ),
                );

                if ('core' !== $type) {
                    $options['body']['slug'] = $args['slug']; // Plugin or theme slug
                }

                $request = wp_remote_post($url, $options);

                if ($ssl && is_wp_error($request)) {
                    trigger_error(__('An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.') . ' ' . __('(WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)'), headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE );

                    $request = wp_remote_post($http_url, $options);
                }

                if (is_wp_error($request)) {
                    $res = new WP_Error('translations_api_failed', __('An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.'), $request->get_error_message());
                } else {
                    $res = json_decode(wp_remote_retrieve_body($request), true);
                    if (!is_object($res) && !is_array($res)) {
                        $res = new WP_Error('translations_api_failed', __('An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://wordpress.org/support/">support forums</a>.'), wp_remote_retrieve_body($request));
                    }
                }
            }

            /**
             * Filter the Translation Install API response results.
             *
             * @since 4.0.0
             *
             * @param object|WP_Error $res  Response object or WP_Error.
             * @param string          $type The type of translations being requested.
             * @param object          $args Translation API arguments.
             */
            return apply_filters('translations_api_result', $res, $type, $args);
        }

        public function default_payment_options() {
            $this->payment_options['fr_FR'] = array(
                array(
                    'payment_label' => __('Paiement en 2 fois', 'mercanet'),
                    'payment_min_amount' => 0,
                    'payment_max_amount' => 0,
                    'payment_nb_term' => 2,
                    'payment_periodicy' => 29,
                    'payment_first_amount' => 50,
                    'payment_activation' => "checked"
                ),
                array(
                    'payment_label' => __('Paiement en 3 fois', 'mercanet'),
                    'payment_min_amount' => 0,
                    'payment_max_amount' => 0,
                    'payment_nb_term' => 3,
                    'payment_periodicy' => 29,
                    'payment_first_amount' => 33,
                    'payment_activation' => "checked"
                )
            );
            $this->payment_options['en_US'] = array(
                array(
                    'payment_label' => 'Payment 2x',
                    'mercanet',
                    'payment_min_amount' => 0,
                    'payment_max_amount' => 0,
                    'payment_nb_term' => 2,
                    'payment_periodicy' => 29,
                    'payment_first_amount' => 50,
                    'payment_activation' => "checked"
                ),
                array(
                    'payment_label' => 'Payment 3x',
                    'mercanet',
                    'payment_min_amount' => 0,
                    'payment_max_amount' => 0,
                    'payment_nb_term' => 3,
                    'payment_periodicy' => 29,
                    'payment_first_amount' => 33,
                    'payment_activation' => "checked"
                )
            );
            update_option('mercanet_nx_payment_options_fr_FR', $this->payment_options['fr_FR']);
            update_option('mercanet_nx_payment_options_en_US', $this->payment_options['en_US']);
        }

        public function generate_title_name_html() {
            ob_start();
            $languages = get_available_languages();
            $locale = Mercanet_Api::get_locale();
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations();
            $title = $this->nx_title_names['en_US'];
            ?>
            <tr valign="top" class="mercanet_nx_label">
            <p class="description">
                <?php echo __('Wording payment in several times imperative information for all languages of your shop', 'mercanet'); ?>
            </p>
            </tr>
            <tr valign="top" class="mercanet_nx_label">
                <th class="titledesc">English (United States)</th>
                <td class="forminp"><input type="text" style="min-width:500px" value="<?php echo esc_attr($title['title_name']); ?>" name="title_name[en_US]" /></td>
            </tr>
            <?php
            foreach ($languages as $lang) {
                $translation = $translations[$lang];
                $native_name = $translation['native_name'];
                $language = $translation['language'];

                if (isset($this->nx_title_names[$lang])) {
                    $title = $this->nx_title_names[$lang];
                    ?>
                    <tr valign="top" class="mercanet_nx_label">
                        <?php
                        echo '<th class="titledesc">' . esc_attr($native_name) . '</th>' .
                        '<td class="forminp"><input type="text" style="min-width:500px" value="' . esc_attr($title['title_name']) . '" name="title_name[' . $lang . ']" /></td>';
                        ?>
                    </tr>
                <?php } else { ?>
                    <tr valign="top" class="mercanet_nx_label">
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

        public function save_title_names() {
            $locale = get_locale();
            $title_names = $_POST['title_name'];
            $names = array();

            foreach ($title_names as $lang => $name) {
                if ($lang == $locale) {
                    if (empty($name)) {
                        $errors[] = __('You have to choose a name for the payment in several times in the current language.', 'mercanet');
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
                update_option('mercanet_nx_title_names', $names);
            }
        }

        public function load_back_js() {
            $plugin_base_name = explode('/', plugin_basename(__FILE__))[0];
            wp_register_script('mercanet-back', plugins_url("$plugin_base_name/assets/js/back.js"));
            wp_enqueue_script('mercanet-back');
        }

        /**
         * One-time gateway admin form
         */
        public function init_form_fields() {
            $this->form_fields = array(
                array(
                    'title' => __('LABEL PAYMENT IN SEVERAL TIMES', 'mercanet'),
                    'type' => 'title',
                    'description' => ('<hr>'),
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'class' => 'mercanet_nx_label_origin',
                    'description' => __('Label', 'woocommerce'),
                    'default' => __('Payment in several times by Mercanet', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'mercanet_nx_title' => array(
                    'title' => __('*Name of the payment', 'mercanet'),
                    'type' => 'title_name'
                ),
                'enabled' => array(
                    'title' => __('Activation', 'mercanet'),
                    'type' => 'checkbox',
                    'label' => __('Active Several Times Payment', 'mercanet'),
                    'default' => 'yes',
                ),
                'payment_option' => array(
                    'type' => 'payment_option'
                )
            );
            if (get_option('label_translate_on') == 'yes') {
                unset($this->form_fields['title']);
            }
        }

        public function check_payment_option() {
            $iso_lang = get_locale();
            $options = get_option('mercanet_nx_payment_options_' . $iso_lang);
            $order_amount = WC_Payment_Gateway::get_order_total();
            if (empty($options)) {
                return $options = array();
            }
            $nb = count($options);
            for ($i = 0; $i < $nb; $i++) {
                if (( $order_amount < (float) $options[$i]['payment_min_amount'] ) || ( $order_amount > (float) $options[$i]['payment_max_amount'] && $options[$i]['payment_max_amount'] != 0) || $options[$i]['payment_activation'] == null) {
                    unset($options[$i]);
                }
            }
            return $options;
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
                    $options = $this->check_payment_option();
                    if (empty($options)) {
                        unset($gateways[$this->id]);
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

        public function load_front_css() {
            $plugin_base_name = explode('/', plugin_basename(__FILE__))[0];
            wp_register_style('mercanet-front', plugins_url("$plugin_base_name/assets/css/front.css"));
            wp_enqueue_style('mercanet-front');
        }

        public function payment_fields() {
            $this->load_front_css();
            $options = $this->check_payment_option();
            $size_of = count($options);
            $checked = ( $size_of == 1 ) ? 'checked' : '';
            $html_list_payment = <<<HTML
                <input type="hidden" id="payment_option_nx" name="payment_option_nx" />
                <input type="hidden" id="payment_mean_brand_nx" name="payment_mean_brand_nx" />
HTML;
            $display_method = get_option('mercanet_display_card_method');
            switch ($display_method) {
                case 'IFRAME':
                case 'DIRECT':
                case 'DISPLAY_CARDS':
                    $cards_list = get_option('mercanet_card_allowed');
                    if(!is_array($cards_list)){
                        $cards_list = array($cards_list);
                    }
                    if (!empty($cards_list)) {
                        if (in_array('ALL', $cards_list)) {
                            $cards_list = Mercanet_Admin_General::available_cards();
                            $cards_to_disable = explode(',', Mercanet_Api::CARDS_WITHOUT_TRI_TO_DISABLE);
                            $cards_with_ntime = explode(',', Mercanet_Api::CARDS_WITH_N_TIMES);
                            foreach ($cards_to_disable as $card) {
                                if (isset($cards_list[$card])) {
                                    unset($cards_list[$card]);
                                }
                            }
                            foreach ($cards_list as $key_card => $card) {
                                if (!in_array($key_card, $cards_with_ntime))
                                    unset($cards_list[$key_card]);
                            }
                        } else {
                            $cards_with_ntime = explode(',', Mercanet_Api::CARDS_WITH_N_TIMES);
                            foreach ($cards_list as $key_card => $card) {
                                if (!in_array($card, $cards_with_ntime)) {
                                    unset($cards_list[$key_card]);
                                } else {

                                    $cards_list[$card] = $card;
                                    unset($cards_list[$key_card]);
                                }
                            }
                        }
                        // Control NxCB activation and order amount   
                        global $wp;      
                        if (!empty(WC()->session->total)) {
                            $order_total = WC()->session->total;
                        } elseif (!empty($wp->query_vars['order-pay'])) {                            
                            $order_id = absint($wp->query_vars['order-pay']);
                            $order = new WC_Order($order_id);
                            $order_total = $order->get_total();
                        } else {
                            $order_total = WC()->session->cart_totals['total'];
                        }

                        if (!empty($order_total)) {
                            if (!Mercanet_Api::is_allowed(array(
                                        'NCB')) || !is_between($order_total, 100, 3000)) {
                                if (isset($cards_list['NxCB'])) {
                                    unset($cards_list['NxCB']);
                                }
                            }
                        }

                        // control cetelem
                        if (!empty($order_total)) {
                            if (!Mercanet_Api::is_allowed(array(
                                        'FCB')) || !is_between($order_total, 100, 3000)) {
                                if (isset($cards_list['CETELEM_3X'])) {
                                    unset($cards_list['CETELEM_3X']);
                                }
                                if (isset($cards_list['CETELEM_4X'])) {
                                    unset($cards_list['CETELEM_4X']);
                                }
                            }
                        }

                        $html_list_payment .= <<<HTML
                            <div id="mercanet_nx_cards">
HTML;
                        foreach ($options as $key => $option) {
                            $img_mif = $cards_mif = $html_list_card = "";
                            $html_list_payment .= <<<HTML
                                <label for="payment_option_$key">
                                    <input type="radio" $checked name="payment_option"  value="$key" id="payment_option_$key" style="display:none;" />&nbsp;{$option['payment_label']}
                                </label>
HTML;
                            if(!empty($cards_list)) {
                                foreach ($cards_list as $card => $name) {
                                   $icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__DIR__)) . '/assets/img/' . $card . '.png';
                                   if (strpos(self::CARDS_MIF, $card) !== false) {
                                       $img_mif .= "<img style='display:block;' style='float:right;' src='$icon' title='$name' alt='$name'/>";
                                       $cards_mif .= "$card,";
                                       continue;
                                   }
                                   $html_list_card .= <<<HTML
                                       <div class="mercanet-display-card">
                                           <button onClick=check_option($key,'$card','nx') value="$card">
                                               <img style="display:block;" style="float:right;" src="$icon" title="$name" alt="$name"/>
                                               <p>{$option['payment_label']} </p>
                                           </button>
                                       </div>
HTML;
                                }    
                            }       
                            

                            $cards_mif = substr($cards_mif, 0, -1);
                            $html_list_payment .= <<<HTML
                                <div class="mercanet-display-card mercanet-display-card-mif">
                                    <button onClick=check_option($key,'$cards_mif','nx') value="MIF">    
                                        $img_mif
                                        <p>{$option['payment_label']} </p>
                                    </button>
                                </div>
                                $html_list_card
                                <div class="mercanet-clear" style="padding:0"></div>
HTML;
                        }
                        $html_list_payment .= "</div>";
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
                if (!isset($_POST['payment_mean_brand_nx'])) {
                    wc_add_notice(__('Please choose a card to proceed at the payment.', 'mercanet'), 'error');
                } else {
                    WC()->session->set('payment_mean_brand', $_POST['payment_mean_brand_nx']);
                }
            }

            if (!isset($_POST['payment_option_nx'])) {
                wc_add_notice(__('Please choose a payment option to proceed at the payment.', 'mercanet'), 'error');
            } else {
                WC()->session->set('payment_option', $_POST['payment_option_nx']);
            }
        }

        public function receipt_page($order_id) {
            $order = new WC_Order($order_id);
            $user_id = get_current_user_id();

            if (empty($order)) {
                return false;
            }

            $return_url = WC()->api_request_url('Mercanet_Gateway_Nx');
            $gateway = $this->id;
            $is_nx = true;
            $params = Mercanet_Api::get_params($order, $user_id, $return_url, $is_nx);
            $params = $this->get_nx_params($params);
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

        public function get_nx_params($params) {
            $iso_lang = get_locale();
            $option_id = (int) WC()->session->get('payment_option');
            $options = get_option('mercanet_nx_payment_options_' . $iso_lang);
            $nx_payment = $options[$option_id];
            $amount = (float) $params['amount'] / 100;
            $number = (int) $nx_payment['payment_nb_term'];

            $amounts = array();
            $date = new DateTime();
            $references = array();

            if ((int) $nx_payment['payment_first_amount'] == 0) {
                $split_amount = round($amount / $number, 2);
                $last_amount = $amount - ( $split_amount * ( $number - 1 ) );

                for ($i = 1; $i < $number; $i++) {
                    if ($i == 1) {
                        $references[$i] = $params['transactionReference'];
                    } else {
                        $references[$i] = $i . 'schedule' . $params['transactionReference'];
                    }

                    $amounts[$date->format('Ymd')] = ( $i == $number ) ? $last_amount * 100 : $split_amount * 100;
                    $date->add(new DateInterval('P' . (int) $nx_payment['payment_periodicy'] . 'D'));
                }
            } else {
                $first_amount = round($amount * ( (int) $nx_payment['payment_first_amount'] / 100 ), 2);

                $remaining_amount = $amount - $first_amount;

                $split_amount = round($remaining_amount / ( $number - 1 ), 2, PHP_ROUND_HALF_DOWN);

                $last_amount = $split_amount + round($remaining_amount - ( $split_amount * ( $number - 1 ) ), 2);

                for ($i = 1; $i <= $number; $i++) {
                    if ($i == 1) {
                        $amounts[$date->format('Ymd')] = $first_amount * 100;
                        $references[$i] = $params['transactionReference'];
                    } else {
                        $amounts[$date->format('Ymd')] = ( $i == $number ) ? $last_amount * 100 : $split_amount * 100;
                        $references[$i] = $i . 'schedule' . $params['transactionReference'];
                    }

                    $date->add(new DateInterval('P' . (int) $nx_payment['payment_periodicy'] . 'D'));
                }
            }

            $params['instalmentData.amountsList'] = implode(',', $amounts);
            $params['instalmentData.number'] = (int) $number;
            $params['instalmentData.datesList'] = implode(',', array_keys($amounts));
            $params['instalmentData.transactionReferencesList'] = implode(',', $references);
            $params['paymentPattern'] = 'INSTALMENT';

            return $params;
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
            $order = new WC_Order($data['orderId']);
            $transaction_type = Mercanet_Api::PAYMENT;            

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
            
            $amounts_list = preg_split('@;@', $data['instalmentAmountsList'], null, PREG_SPLIT_NO_EMPTY);
            $transaction_id = Mercanet_Transaction::save($data, $raw_data, $transaction_type, $amounts_list[0]);
            
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
                    Mercanet_Schedule::save($data, $raw_data, $transaction_type, $transaction_id);
                    $order->payment_complete($data['transactionReference']);
                    $woocommerce->cart->empty_cart();
                    $order->add_order_note(__('Payment accepted', 'mercanet'));
                } else {
                    $order->update_status('failed');                   
                    $order->add_order_note(__('Mercanet response error', 'mercanet'));
                }           
            }
            // redirect to the parent page
            if ($iframe_redirect) {
                echo $iframe_redirect;
                exit;
            }
            wp_redirect($order->get_checkout_order_received_url());
            exit;
        }

        public function thankyou_page($order_id) {
            $schedules = Mercanet_Schedule::get_by_order_id($order_id);
            $transaction = Mercanet_Transaction::get_by_order_id($order_id);
            if (!empty($transaction)) {
                echo '<ul class="order_details">';
                echo '<li class="method">' . __('Authorisation ID', 'mercanet') . '<strong>' . $transaction[0]->authorization_id . '</strong></li>';
                echo '</ul>';
            }

            if (!empty($schedules)) {
                echo '<h2>' . __('Schedules', 'mercanet') . '</h2>' .
                '<table class="shop_table">' .
                '<tr>
		                <th>Date</th>
		                <th>' . __('Amount', 'mercanet') . '</th>
		            </tr>';
                foreach ($schedules as $schedule) {
                    echo '<tr>' .
                    '<td>' . date_i18n(get_option('date_format'), strtotime($schedule->date_to_capture)) . '</td>' .
                    '<td>' . $schedule->amount . ' &euro;</td>' .
                    '</tr>';
                }
                echo '</table>';
            }
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

        /**
         * Generate payment options table
         */
        public function generate_payment_option_html() {

            ob_start();

            // Get all languages installed
            $languages_installed = get_available_languages();
            $translations = wp_get_available_translations();

            $languages = array();
            foreach ($languages_installed as $language) {
                if (isset($translations[$language])) {
                    $languages[$language]['language'] = $language;
                }
                $languages[$language]['native_name'] = $translations[$language]['native_name'];
            }
            // Add english default lang
            if (!in_array('en_US', $languages)) {
                $languages['en_US']['language'] = 'en_US';
                $languages['en_US']['native_name'] = 'English (United States)';
            }
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php echo __('PAYMENT OPTIONS', 'mercanet'); ?></th>
                <td class="forminp">
                    <?php foreach ($languages as $iso_lang => $language) { ?>

                        <h3><?php echo $language['native_name']; ?></h3>
                        <table id="payment_options_<?php echo $iso_lang ?>" class="widefat wc_input_table sortable" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><?php echo __('Label', 'mercanet'); ?></th>
                                    <th><?php echo __('Minimum amount', 'mercanet'); ?></th>
                                    <th><?php echo __('Maximum amount', 'mercanet'); ?></th>
                                    <th><?php echo __('Number', 'mercanet'); ?></th>
                                    <th><?php echo __('Periodicy', 'mercanet'); ?></th>
                                    <th><?php echo __('First payment', 'mercanet'); ?></th>
                                    <th><?php echo __('Active', 'mercanet'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="options">
                                <?php if ($this->payment_options[$iso_lang]) { ?>
                                    <?php foreach ($this->payment_options[$iso_lang] as $i => $option) { ?>
                                        <?php if (!empty($option['payment_label'])) { ?>
                                            <tr class="option">
                                                <td>
                                                    <input type="hidden" name="payment_option_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" value="payment"/>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_label']); ?>" name="payment_label_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_min_amount']); ?>" name="payment_min_amount_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_max_amount']); ?>" name="payment_max_amount_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_nb_term']); ?>" name="payment_nb_term_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_periodicy']); ?>" name="payment_periodicy_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo esc_attr($option['payment_first_amount']); ?>" name="payment_first_amount_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                                <td>
                                                    <input type="checkbox"  value="payment_activation" <?php echo esc_attr($option['payment_activation']); ?> name="payment_activation_<?php echo $iso_lang; ?>[<?php echo $i; ?>]" />
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>

                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7"><a href="#" onClick='add_row("<?php echo $iso_lang ?>")' class="add button"><?php echo __('+ Add option', 'mercanet'); ?></a> <a href="#" class="remove_rows button"><?php
                                echo __('Remove selected option(s)', 'mercanet');
                                ?></a></th>
                                </tr>
                            </tfoot>

                        </table>

                    <?php } ?>
                </td>

            </tr>


            <script type="text/javascript">

                function add_row(lang) {
                    var size = jQuery('#payment_options_' + lang).find('tbody .option').size();

                    jQuery('<tr class="account">\
                                <td><input type="text" name="payment_label_' + lang + '[' + size + ']" /></td>\
                                <td><input type="text" name="payment_min_amount_' + lang + '[' + size + ']" /></td>\
                                <td><input type="text" name="payment_max_amount_' + lang + '[' + size + ']" /></td>\
                                <td><input type="text" name="payment_nb_term_' + lang + '[' + size + ']" /></td>\
                                <td><input type="text" name="payment_periodicy_' + lang + '[' + size + ']" /></td>\
                            <td><input type="text" name="payment_first_amount_' + lang + '[' + size + ']" /></td>\
                                <td><input type="checkbox" name="payment_activation_' + lang + '[' + size + ']" /></td>\
                            <input type="hidden" name="payment_option_' + lang + '[' + size + ']" />\
                            </tr>').appendTo('#payment_options_' + lang + ' tbody');
                    return false;
                }
                ;
            </script>
            </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        /**
         * Validate and save payment options table
         */
        public function save_nx_options() {

            $payments = array();
            $errors = array();

            foreach ($this->languages as $iso_lang => $language) {
                $payments = array();
                if (!empty($_POST['payment_option_' . $iso_lang])) {
                    $payment_label = array_map('wc_clean', $_POST['payment_label_' . $iso_lang]);
                    $payment_min_amount = array_map('wc_clean', $_POST['payment_min_amount_' . $iso_lang]);
                    $payment_max_amount = array_map('wc_clean', $_POST['payment_max_amount_' . $iso_lang]);
                    $payment_nb_term = array_map('wc_clean', $_POST['payment_nb_term_' . $iso_lang]);
                    $payment_periodicy = array_map('wc_clean', $_POST['payment_periodicy_' . $iso_lang]);
                    $payment_first_amount = array_map('wc_clean', $_POST['payment_first_amount_' . $iso_lang]);
                    $payment_option = array_map('wc_clean', $_POST['payment_option_' . $iso_lang]);
                    if (isset($_POST['payment_activation_' . $iso_lang])) {
                        $payment_activation = is_array($_POST['payment_activation_' . $iso_lang]) ? array_map('wc_clean', $_POST['payment_activation_' . $iso_lang]) : $_POST['payment_activation_' . $iso_lang];
                    }

                    foreach ($payment_option as $i => $name) {

                        if (!isset($payment_label[$i])) {
                            continue;
                        }

                        if (empty($payment_label[$i])) {
                            $errors[] = __('You have to choose a name for the payment option.', 'mercanet');
                        }

                        if (filter_var($payment_min_amount[$i], FILTER_VALIDATE_INT) === true) {
                            $errors[] = __('The minimum amount must contain only numeric.', 'mercanet');
                        }

                        if (filter_var($payment_max_amount[$i], FILTER_VALIDATE_INT) === true) {
                            $errors[] = __('The maximum amount must contain only numeric.', 'mercanet');
                        }

                        if (!empty($payment_min_amount[$i]) && !empty($payment_max_amount[$i])) {
                            if (!is_between($payment_min_amount[$i], 0, $payment_max_amount[$i])) {
                                $errors[] = __('The minimum amount should be greater than 0 and less than the maximum amount.', 'mercanet');
                            } elseif ($payment_min_amount[$i] > $payment_max_amount[$i]) {
                                $errors[] = __('The minimum amount can not be greater than the maximum amount.', 'mercanet');
                            } elseif ($payment_max_amount[$i] < $payment_min_amount[$i]) {
                                $errors[] = __('The maximum amount can not be smaller than the minimum amount.', 'mercanet');
                            }
                        }

                        if (empty($payment_nb_term[$i])) {
                            $errors[] = __('You have to register a term number.', 'mercanet');
                        } elseif (filter_var($payment_nb_term[$i], FILTER_VALIDATE_INT) === true) {
                            $errors[] = __('The number of occurrence must contain only numeric.', 'mercanet');
                        } elseif (!is_between($payment_nb_term[$i], 2, 12)) {
                            $errors[] = __('The number of occurrence must be between 2 and 12.', 'mercanet');
                        }

                        if (empty($payment_periodicy[$i])) {
                            $errors[] = __('You have to register the periodicy.', 'mercanet');
                        } elseif (filter_var($payment_periodicy[$i], FILTER_VALIDATE_INT) === true) {
                            $errors[] = __('The periodicy must contain only numeric.', 'mercanet');
                        } elseif (( (int) $payment_nb_term[$i] * (int) $payment_periodicy[$i] ) >= 90) {
                            $errors[] = __('Payment n times should be less than 90 days duration.', 'mercanet');
                        }


                        if (empty($payment_first_amount[$i])) {
                            $errors[] = __('You have to register the first amount.', 'mercanet');
                        } elseif (filter_var($payment_first_amount[$i], FILTER_VALIDATE_INT) === true) {
                            $errors[] = __('The first amount must contain only numeric.', 'mercanet');
                        } elseif (!is_between($payment_first_amount[$i], 0, 100)) {
                            $errors[] = __('The first payment should be greater than 0 and less than 100.', 'mercanet');
                        }



                        if (empty($errors)) {
                            $payments[] = array(
                                'payment_id' => $i,
                                'payment_label' => $payment_label[$i],
                                'payment_min_amount' => empty($payment_min_amount[$i]) ? 0 : $payment_min_amount[$i],
                                'payment_max_amount' => empty($payment_max_amount[$i]) ? 0 : $payment_max_amount[$i],
                                'payment_nb_term' => $payment_nb_term[$i],
                                'payment_periodicy' => $payment_periodicy[$i],
                                'payment_first_amount' => $payment_first_amount[$i],
                                'payment_activation' => isset($payment_activation[$i]) ? 'checked' : null
                            );
                        } else {
                            $payments[] = $this->payment_options[$iso_lang][$i];
                        }
                    }
                    if (!empty($payments)) {
                        update_option('mercanet_nx_payment_options_' . $iso_lang, $payments);
                    }
                }
            }

            update_option('mercanet_nx_payment_options_' . $iso_lang, $payments);

            if (!empty($errors)) {
                $this->errors = $errors;
                foreach ($errors as $key => $value) {
                    WC_Admin_Settings::add_error($value);
                }
            }
        }

        public static function check_iframe_redirect($order) {
            $display_method = get_option('mercanet_display_card_method');
            $script_redirect_iframe = false;
            if ($display_method == 'IFRAME') {
                $script_redirect_iframe = <<<JS
                <script type="text/javascript">
                    console.log('Ca passe là !');
                    top.location.href='{$order->get_checkout_order_received_url()}';
                </script>
JS;
            }

            return $script_redirect_iframe;
        }

    }

}
