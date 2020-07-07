<?php

class Mercanet_Admin_Credentials {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public function __construct() {
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_settings_mercanet_credentials', array($this, 'settings_tab'));
        add_action('woocommerce_update_options_settings_mercanet_credentials', array($this, 'update_settings'));
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs['settings_mercanet_credentials'] = __('Mercanet credentials', 'mercanet');

        if (Mercanet_Api::is_allowed(array('ABO'))) {
            $settings_tabs['settings_mercanet_recurring_payment_list'] = __('Mercanet recurring payment list', 'mercanet');
        }

        return $settings_tabs;
    }

    /**
     * Output settings
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public function settings_tab() {
        $mercanet_activation_key = get_option('mercanet_activation_key');
        if (empty($mercanet_activation_key)) {
            woocommerce_admin_fields($this->get_setting());
        } else {
            woocommerce_admin_fields($this->get_settings());
        }
        // Add button & Hide Save Button
        submit_button(__('Save Settings', 'mercanet'), 'button-primary', 'save');
        global $hide_save_button;
        $hide_save_button = true;
    }

    /**
     * Save settings
     */
    public function update_settings() {
        $mercanet_activation_key = get_option('mercanet_activation_key');
        if (empty($mercanet_activation_key)) {
            if (empty($_POST['mercanet_activation_key'])) {
                WC_Admin_Settings::add_error(__('You have to register your Mercanet activation key', 'mercanet'));
            } else {
                if (Mercanet_Api::decrypt_activation_key($_POST['mercanet_activation_key']) === true) {
                    woocommerce_update_options($this->get_setting());
                } else {
                    WC_Admin_Settings::add_error(__('The digital signature of your activation key could not be validated.', 'mercanet'));
                }
            }
        } else {
            $errors = $this->validate_settings();
            if (count($errors) > 0) {
                return false;
            }
            if (strcmp($_POST['mercanet_activation_key'], get_option('mercanet_activation_key')) != 0) {
                update_option('mercanet_first_activation', 'yes');
            }
            $is_first_activation = get_option('mercanet_first_activation', 'yes');
            if ($is_first_activation == 'yes') {
                $this->init_general_settings();
                update_option('mercanet_first_activation', 'no');
            }
            woocommerce_update_options($this->get_settings());
        }
    }

    /**
     * Save the general option, for the first activation
     */
    public function init_general_settings() {

        update_option('mercanet_default_payment_page_language', 'FR');
        update_option('mercanet_allowed_countries_check', 'no');
        update_option('mercanet_allowed_countries', 'ALL');
        update_option('mercanet_capture_day', 0);
        update_option('mercanet_payment_validation', 'AUTHOR_CAPTURE');
        update_option('mercanet_card_allowed', 'ALL');
        update_option('mercanet_currencies_allowed', 'EUR');
        update_option('mercanet_display_card_method', 'DIRECT');
        update_option('mercanet_redirect_payment', 'no');
        update_option('mercanet_notify_customer', 'yes');
        update_option('mercanet_activation_one', 'yes');
        update_option('mercanet_anti_fraud_control_3ds', 'yes');
        update_option('mercanet_anti_fraud_control_pec', 'yes');
        update_option('mercanet_anti_fraud_control_pip', 'yes');
        update_option('mercanet_anti_fraud_control_scp', 'yes');
        update_option('mercanet_anti_fraud_control_a3d', 'yes');
        update_option('mercanet_anti_fraud_control_cco', 'yes');
        update_option('mercanet_anti_fraud_control_cvi', 'yes');
        update_option('mercanet_anti_fraud_control_lnc', 'yes');
        update_option('mercanet_anti_fraud_control_amt', 'yes');
        update_option('mercanet_anti_fraud_control_ecc', 'yes');
        update_option('mercanet_anti_fraud_control_eci', 'yes');

        //Config defaut pour afficher le onetime dès l'installation
        $default_config_onetime = array(
            "mercanet_onetime_title" => "",
            "enabled" => "yes",
            "mercanet_onetime_min_amount" => "",
            "mercanet_onetime_max_amount" => "",
        );
        update_option('woocommerce_mercanet_onetime_settings', $default_config_onetime);
    }

    public function validate_activation_key($key) {
        if (Mercanet_Api::decrypt_activation_key($_POST['mercanet_activation_key']) === true) {
            return true;
        } else {
            WC_Admin_Settings::add_error(__('The digital signature of your activation key could not be validated.', 'mercanet'));
        }
    }

    public function validate_settings() {
        $errors = array();

        if (empty($_POST['mercanet_activation_key'])) {
            $errors[] = __('You have to register your Mercanet activation key', 'mercanet');
        } elseif (Mercanet_Api::decrypt_activation_key($_POST['mercanet_activation_key']) !== true) {
            $errors[] = __('The digital signature of your activation key could not be validated.', 'mercanet');
        }

        // Merchant ID
        $merchant_id = trim($_POST['mercanet_merchant_id']);
        if (empty($merchant_id)) {
            $errors[] = __('You have to register a Merchant ID.', 'mercanet');
        } elseif (filter_var($merchant_id, FILTER_VALIDATE_FLOAT) === false) {
            $errors[] = __('The Merchant ID must contain only numeric.', 'mercanet');
        } elseif (strlen($merchant_id) != 15) {
            $errors[] = __('The Merchant ID must contain 15 characters.', 'mercanet');
        }

        // Secret Key
        $mercanet_secret_key = trim($_POST['mercanet_secret_key']);
        if (empty($mercanet_secret_key)) {
            $errors[] = __('You have to register your secret key.', 'mercanet');
        }

        // Key Version
        $version_key = trim($_POST['mercanet_version_key']);
        if (empty($version_key)) {
            $errors[] = __('You have to register a key version.', 'mercanet');
        } elseif (filter_var($version_key, FILTER_VALIDATE_INT) === false) {
            $errors[] = __('The key version must contain only numeric.', 'mercanet');
        } elseif (strlen($version_key) >= 10) {
            $errors[] = __('The key version can not contain more than 10 characters.', 'mercanet');
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                WC_Admin_Settings::add_error($error);
            }
        }

        return $errors;
    }

    /**
     * Get all the settings
     *
     * @return array
     */
    public function get_settings() {
        $settings = array(
            'mercanet_general' => array(
                'title' => __('Mercanet credentials', 'mercanet'),
                'type' => 'title'
            ),
            'test_mode' => array(
                'title' => __('*Enabled test mode', 'mercanet'),
                'type' => 'checkbox',
                'desc' => __('Enabled or disabled test mode', 'mercanet'),
                'id' => 'mercanet_test_mode'
            ),
            'activation_key' => array(
                'title' => __('*Enter your activation key', 'mercanet'),
                'type' => 'textarea',
                'desc_tip' => true,
                'desc' => __('This key is provided by BNP Paribas', 'mercanet'),
                'css' => 'min-width: 800px;',
                'id' => 'mercanet_activation_key',
                'required'
            ),
            'merchant_id' => array(
                'title' => __('*Enter your Merchant ID', 'mercanet'),
                'type' => 'text',
                'desc' => __('This ID is provided by BNP Paribas', 'mercanet'),
                'id' => 'mercanet_merchant_id'
            ),
            'secret_key' => array(
                'title' => __('*Enter your Secret Key', 'mercanet'),
                'type' => 'password',
                'css' => 'min-width: 300px;',
                'desc' => __('This secret key is provided by BNP Paribas', 'mercanet'),
                'id' => 'mercanet_secret_key'
            ),
            'version_key' => array(
                'title' => __('*Enter your key version', 'mercanet'),
                'type' => 'text',
                'desc' => __('This version number is provided by BNP Paribas', 'mercanet'),
                'id' => 'mercanet_version_key'
            ),
            'section_end' => array(
                'type' => 'sectionend',
            )
        );

        return apply_filters('wc_settings_mercanet_credentials', $settings);
    }

    /**
     * Get the activation key setting, for the first activation
     *
     * @return array
     */
    public function get_setting() {

        $settings = array(
            'mercanet_general' => array(
                'title' => __('Mercanet credentials', 'mercanet'),
                'type' => 'title',
                'id' => 'mercanet_general_title'
            ),
            'activation_key' => array(
                'title' => __('*Enter your activation key', 'mercanet'),
                'type' => 'textarea',
                'css' => 'min-width: 800px;',
                'desc_tip' => true,
                'desc' => __('This key is provided by BNP Paribas', 'mercanet'),
                'id' => 'mercanet_activation_key'
            ),
            'section_end' => array(
                'type' => 'sectionend',
            )
        );
        return apply_filters('wc_settings_mercanet_credential', $settings);
    }
}

new Mercanet_Admin_Credentials();
