<?php

class Mercanet_Admin_General {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public function __construct() {
        if (!get_option('mercanet_activation_key') == '') {
            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
            add_action('woocommerce_settings_tabs_settings_mercanet', array($this, 'settings_tab'));
            add_action('woocommerce_update_options_settings_mercanet', array($this, 'update_settings'));
            include_once plugin_dir_path(__DIR__) . 'settings/mercanet-settings.php';
            $this->get_settings();
        }
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array
     *
     * @param array
     * @return array
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs['settings_mercanet'] = __('Mercanet General', 'mercanet');
        return $settings_tabs;
    }

    public function validate_settings() {
        $errors = array();

        // mercanet_default_payment_page_language
        if (isset($_POST['mercanet_default_payment_page_language']) && empty($_POST['mercanet_default_payment_page_language'])) {
            $errors[] = __(' Yoo have to choose a default language.', 'mercanet');
        }

        // mercanet_card_allowed
        if (!isset($_POST['mercanet_card_allowed']) || empty($_POST['mercanet_card_allowed'])) {
            $errors[] = __(' Yoo have to choose a payment card.', 'mercanet');
        }
        
        // mercanet_allowed_countries                
        if (isset($_POST['mercanet_allowed_countries']) && empty($_POST['mercanet_allowed_countries'])) {
            $errors[] = __(' Yoo have to choose an allowed country.', 'mercanet');
        }

        // mercanet_currencies_allowed
        if (isset($_POST['mercanet_currencies_allowed']) && empty($_POST['mercanet_currencies_allowed'])) {
            $errors[] = __(' Yoo have to choose a currency.', 'mercanet');
        }

        // mercanet_capture_day
        $capture_day = trim($_POST['mercanet_capture_day']);
        if (!empty($capture_day)) {
            if (filter_var($capture_day, FILTER_VALIDATE_INT) === false) {
                $errors[] = __('The bank deposit time limit must contain only numeric.', 'mercanet');
            } elseif (!is_between($capture_day, 0, 99)) {
                $errors[] = __('The bank deposit time limit must be between 0 and 99.', 'mercanet');
            }
        }

        // mercanet_min_amount_3ds
        if (isset($_POST['mercanet_min_amount_3ds'])) {
            $amount_3ds = trim($_POST['mercanet_min_amount_3ds']);
            if (!empty($amount_3ds)) {
                if (filter_var($capture_day, FILTER_VALIDATE_INT) === false) {
                    $errors[] = __('The 3DS minimum amount must contain only numeric.', 'mercanet');
                } elseif ((int) $amount_3ds <= 0) {
                    $errors[] = __('The 3DS minimum amount must be superior to 0.', 'mercanet');
                }
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                WC_Admin_Settings::add_error($error);
            }
        }

        return $errors;
    }

    /**
     * Output settings
     */
    public function settings_tab() {
        woocommerce_admin_fields($this->get_settings());
        // Add button & Hide Save Button
        submit_button(__('Save Settings', 'mercanet'), 'button-primary', 'save');
        global $hide_save_button;
        $hide_save_button = true;
    }

    /**
     * Save settings
     */
    public function update_settings() {
        $errors = $this->validate_settings();
        if (count($errors) > 0) {
            return false;
        }
        woocommerce_update_options($this->get_settings());
    }

    /**
     * Return the available currencies
     *
     * @return array
     */
    public function available_currencies() {
        $data = get_available_currencies();
        $options = array();
        foreach ($data as $key => $value) {
            $options[$key] = $value['name'];
        }
        return $options;
    }

    /**
     * Return the available languages
     *
     * @return array
     */
    public function available_languages() {
        $data = get_languages_available();
        $options = array();
        foreach ($data as $key => $value) {
            $options[$key] = $value['name'];
        }
        return $options;
    }

    /**
     * Return the available countries
     *
     * @return array
     */
    public function available_countries() {
        $data = get_available_countries();
        $options = array();
        foreach ($data as $key => $value) {
            $options[$key] = $value['name'];
        }
        return $options;
    }

    /**
     * Return the available cards
     *
     * @param bool
     * @return array
     */
    public static function available_cards($all = false) {

        $trigs = get_restricted_cards();
        $options = Mercanet_Api::allowed_options();
        $allowedCards = array();
        $cards = array();
        $available_cards = get_available_cards();

        foreach ($trigs as $id => $name) {
            if (in_array($id, $options)) {
                if ($id == 'FCB') {
                    $allowedCards[] = 'CETELEM_3X';
                    $allowedCards[] = 'CETELEM_4X';
                } else {
                    $allowedCards[] = $name;
                }
            }
        }

        if ($all == true) {
            $cards['ALL'] = __('ALL', 'mercanet');
        }

        foreach ($available_cards as $keyCard => $valueCard) {
            if (!in_array($keyCard, $trigs)) {
                $cards[$keyCard] = $valueCard['name'];
            }
            if (!empty($allowedCards)) {
                if (in_array($keyCard, $allowedCards)) {
                    $cards[$keyCard] = $valueCard['name'];
                }
            }
        }
        return $cards;
    }

    /**
     * Get all the settings
     *
     * @return array
     */
    public function get_settings() {

        $settings[] = array(
            'title' => __('Mercanet general', 'mercanet'),
            'type' => 'title',
            'id' => 'general_options'
        );
        $settings[] = array(
            'title' => __('*Cards allowed', 'mercanet'),
            'desc' => __('List of card authorized to make a payment', 'mercanet'),
            'type' => 'multiselect',
            'class' => 'wc-enhanced-select',
            'css' => 'width: 450px;',
            'default' => 'ALL',
            'options' => $this->available_cards(true),
            'id' => 'mercanet_card_allowed'
        );
        $settings[] = array(
            'title' => __('*Default payment page language', 'mercanet'),
            'type' => 'select',
            'class' => 'wc-enhanced-select',
            'desc' => __('Used if the current WordPress language is not available on Mercanet', 'mercanet'),
            'default' => 'FR',
            'options' => $this->available_languages(),
            'id' => 'mercanet_default_payment_page_language',
        );
        if (!Mercanet_Api::is_allowed(array('STA'))) {                       
            $settings[] = array(
                'title' => __('*Countries allowed', 'mercanet'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'desc' => __('Liste of countries authorized to make a payment', 'mercanet'),
                'default' => 'FRA',
                'options' => $this->available_countries(),
                'id' => 'mercanet_allowed_countries',
            ); 
            
            $settings[] = array(
                'title' => __('*Activate control of allowed countries', 'mercanet'),
                'type' => 'radio',
                'default' => 'no',
                'desc_tip' => true,
                'desc' => __('The option will activate control of allowed countries', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_allowed_countries_check'
            );
        }
        $settings[] = array(
            'title' => __('Bank deposit time limit', 'mercanet'),
            'type' => 'text',
            'desc' => __('0 for D-Day. The number of days before the bank deposit', 'mercanet'),
            'default' => '0',
            'id' => 'mercanet_capture_day',
        );
        if (Mercanet_Api::is_allowed(array('MUL'))) {
            $settings[] = array(
                'title' => __('*Currencies allowed', 'mercanet'),
                'desc' => __('List of currencies authorized to make a payment, others currencies will be send in EURO', 'mercanet'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'css' => 'width: 450px;',
                'default' => 'EUR',
                'options' => $this->available_currencies(),
                'id' => 'mercanet_currencies_allowed'
            );
        }

        $settings[] = array(
            'title' => __('*Bank remittance', 'mercanet'),
            'type' => 'select',
            'class' => 'wc-enhanced-select',
            'css' => 'min-width: 300px;',
            'desc' => __('In manual mode, you must confirm payment within the back office of your shop', 'mercanet'),
            'default' => 'AUTHOR_CAPTURE',
            'options' => array(
                'AUTHOR_CAPTURE' => __('Automatic re-authorization and payment (by default)', 'mercanet'),
                'VALIDATION' => __('Hand over payment after validation by the Merchant', 'mercanet')
            ),
            'id' => 'mercanet_payment_validation'
        );
        $settings[] = array(
            'title' => __('Card data input method', 'mercanet'),
            'type' => 'select',
            'class' => 'wc-enhanced-select',
            'css' => 'min-width: 650px;',
            'default' => 'DIRECT',
            'options' => array(
                'DIRECT' => __('Choice of payment and data cards on Mercanet', 'mercanet'),
                'DISPLAY_CARDS' => __('Choice of payment card on WooCommerce and data cards on Mercanet', 'mercanet'),
                'IFRAME' => __('Choice of payment and data cards on WooCommerce (IFRAME)', 'mercanet')
            ),
            'id' => 'mercanet_display_card_method'
        );
        $settings[] = array(
            'title' => __('Redirection to the shop after the payment', 'mercanet'),
            'type' => 'radio',
            'default' => 'yes',
            'desc_tip' => true,
            'desc' => __('The option will redirect the customer directly at your shop after the payment', 'mercanet'),
            'options' => array(
                'yes' => __('Yes', 'mercanet'),
                'no' => __('No', 'mercanet')
            ),
            'id' => 'mercanet_redirect_payment'
        );
        $settings[] = array(
            'title' => __('Customer confirmation ticket', 'mercanet'),
            'type' => 'radio',
            'default' => 'yes',
            'desc_tip' => true,
            'desc' => __('The result of the transaction (confirmation / rejection) will be automatically emailed to the customer', 'mercanet'),
            'options' => array(
                'yes' => __('Yes', 'mercanet'),
                'no' => __('No', 'mercanet')
            ),
            'id' => 'mercanet_notify_customer'
        );
        $settings[] = array(
            'title' => __('Activate the logger', 'mercanet'),
            'type' => 'radio',
            'default' => 'yes',
            'desc_tip' => true,
            'desc' => __('Log files are stored in the log folder of the module', 'mercanet'),
            'options' => array(
                'yes' => __('Yes', 'mercanet'),
                'no' => __('No', 'mercanet')
            ),
            'id' => 'mercanet_log_active'
        );
        $settings[] = array('type' => 'sectionend', 'id' => 'general_options');

        $settings[] = array(
            'title' => __('CUSTOMIZING THE PAYMENT PAGE', 'mercanet'),
            'type' => 'title',
            'id' => 'customize_page'
        );
        $settings[] = array(
            'title' => __('Theme configuration', 'mercanet'),
            'type' => 'text',
            'desc' => __('The theme configuration to customize the Mercanet payment page (css)', 'mercanet'),
            'id' => 'mercanet_theme_configuration',
        );
        $settings[] = array('type' => 'sectionend', 'id' => 'customize_page');

        if (Mercanet_Api::is_allowed(array('3DS'))) {
            $settings[] = array(
                'title' => __('3D-SECURE', 'mercanet'),
                'type' => 'title',
                'id' => '3d_secure'
            );
            $settings[] = array(
                'title' => __('*Activate 3D-Secure', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the 3DS Secure control based on the 3DS authentication', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_3ds'
            );
            $settings[] = array(
                'title' => __('Minimum amount for which activate 3D-Secure', 'mercanet'),
                'type' => 'text',
                'desc' => __('Minimum amount to add the 3DS', 'mercanet'),
                'default' => 1,
                'id' => 'mercanet_min_amount_3ds'
            );
        }
        $settings[] = array('type' => 'sectionend', 'id' => '3d_secure');

        $settings[] = array(
            'title' => __('SECURITY TOOLS', 'mercanet'),
            'type' => 'title',
            'id' => 'security_tools'
        );
        if (Mercanet_Api::is_allowed(array('PEC'))) {
            $settings[] = array(
                'title' => __('*Activate the control on Host Country Card', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on Host Country Card', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_pec'
            );
        }
        if (Mercanet_Api::is_allowed(array('PIP'))) {
            $settings[] = array(
                'title' => __('*Activate the control on Country IP Address', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on Country IP Address', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_pip'
            );
        }
        if (Mercanet_Api::is_allowed(array('SCP'))) {
            $settings[] = array(
                'title' => __('*Activate the control on similitude of Country and Card IP Address', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on similitude of Country and Card IP Address', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_scp'
            );
        }
        if (Mercanet_Api::is_allowed(array('A3D'))) {
            $settings[] = array(
                'title' => __('*Activate the 3D-Secure Authentification', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the 3D-Secure Authentification', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_a3d'
            );
        }
        if (Mercanet_Api::is_allowed(array('CCO'))) {
            $settings[] = array(
                'title' => __('*Activate the control on the eCard and the Country of the Card', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on the eCard and the Country of the Card', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_cco'
            );
        }
        if (Mercanet_Api::is_allowed(array('CVI'))) {
            $settings[] = array(
                'title' => __('*Activate the control on eCard', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on eCard', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_cvi'
            );
        }
        if (Mercanet_Api::is_allowed(array('LNC'))) {
            $settings[] = array(
                'title' => __('*Activate the control on Black List of Card Number', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on Black List of Card Number', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_lnc'
            );
        }
        if (Mercanet_Api::is_allowed(array('AMT'))) {
            $settings[] = array(
                'title' => __('*Activate the control on the Transaction Amount', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on the Transaction Amount', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_amt'
            );
        }
        if (Mercanet_Api::is_allowed(array('ECC'))) {
            $settings[] = array(
                'title' => __('*Activate the control on the current Card', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on the current Card', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_ecc'
            );
        }
        if (Mercanet_Api::is_allowed(array('ECI'))) {
            $settings[] = array(
                'title' => __('*Activate the control on the current IP Address', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Add the anti fraud control on the current IP Address', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_anti_fraud_control_eci'
            );
        }
        $settings[] = array('type' => 'sectionend', 'id' => 'security_tools');

        if (Mercanet_Api::is_allowed(array('ONE'))) {
            $settings[] = array(
                'title' => __('ONE CLICK PAYMENT', 'mercanet'),
                'type' => 'title',
                'id' => 'oneclick_payment'
            );
            $settings[] = array(
                'title' => __('*Activate One Click', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Activate the possibility to pay in one click', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_activation_one'
            );
            $settings[] = array('type' => 'sectionend', 'id' => 'oneclick_payment');
        }
                
        if (Mercanet_Api::is_allowed(array('PRE'))) {
            $settings[] = array(
                'title' => __('PRESTO', 'mercanet'),
                'type' => 'title',
                'id' => 'preto_payment'
            );
            $settings[] = array(
                'title' => __('*Activate CCH product', 'mercanet'),
                'type' => 'radio',
                'default' => 'yes',
                'desc_tip' => true,
                'desc' => __('Activate the possibility to get CCH product for PRESTO', 'mercanet'),
                'options' => array(
                    'yes' => __('Yes', 'mercanet'),
                    'no' => __('No', 'mercanet')
                ),
                'id' => 'mercanet_activation_presto'
            );
            $settings[] = array('type' => 'sectionend', 'id' => 'preto_payment');
        }
        
        $settings[] = array(
            'title' => __('Translation Label Management', 'mercanet'),
            'type' => 'title',
            'id' => 'label_translate'
        );
        $settings[] = array(
            'title' => __('Translate with mercanet', 'mercanet'),
            'type' => 'radio',
            'default' => 'yes',
            'desc_tip' => true,
            'desc' => __('Manage label payment translation with mercanet module', 'mercanet'),
            'options' => array(
                'yes' => __('Yes', 'mercanet'),
                'no' => __('No', 'mercanet')
            ),
            'id' => 'label_translate_on'
        );
        $settings[] = array('type' => 'sectionend', 'id' => 'label_translate');
        return $settings;
    }

}

new Mercanet_Admin_General();
