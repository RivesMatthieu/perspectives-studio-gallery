<?php

class Mercanet_Api {

    const PAYMENT = 'PAYMENT';
    const REFUND = 'REFUND';
    const CANCEL = 'CANCEL';
    const ANTICIPATE_REFUND = 'ANTICIPATE_REFUND';
//    const CARDS_WITHOUT_TRI_TO_DISABLE = 'BCMC,iDeal,ELV';
    const CARDS_WITHOUT_TRI_TO_DISABLE = '';
    const CARDS_WITH_N_TIMES = 'CB,VISA,MASTERCARD,AMEX';

    /**
     * Get the payment URL
     *
     * @return string
     */
    public static function get_payment_url() {
        if (get_option('mercanet_test_mode') == 'yes') {
            $url = get_option('MERCANET_PAYMENT_PAGE_URL_TEST');
        } else {
            $url = get_option('MERCANET_PAYMENT_PAGE_URL ');
        }
        return $url;
    }

    /**
     * Decrypt Activation key
     */
    public static function decrypt_activation_key($key) {
        $datas = (explode("\n", $key));
        $data = trim($datas[0]);

        $public_key_res = openssl_pkey_get_public(file_get_contents(plugin_dir_path(__DIR__) . 'tools/rsa.pub'));
        $signature = trim(substr($key, strpos($key, "\n") + 1));
        $signature_decode = base64_decode($signature);

        if (function_exists('openssl_verify')) {
            $result = openssl_verify($data, $signature_decode, $public_key_res);
            if ($result == 1) {
                return true;
            } elseif ($result == 0) {
                return false;
            }
        }
        return false;
    }

    /**
     * Get options list from the activation key
     *
     * @return array
     */
    public static function allowed_options() {
        $key = get_option('mercanet_activation_key');
        $options = explode(';', $key);
        array_pop($options);
        return $options;
    }

    /**
     * Check if the option is allowed
     *
     * @param array
     * @return booloean
     */
    public static function is_allowed($options) {
        $i = 0;
        foreach ($options as $option) {
            if (in_array($option, self::allowed_options())) {
                $i++;
            }
        }

        if ($i == count($options)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the current currency
     *
     * @return array
     */
    public static function get_mercanet_currency() {
        $customer_currency = get_woocommerce_currency();
        is_array(get_option('mercanet_currencies_allowed')) ? $allowed_currencies = get_option('mercanet_currencies_allowed') : $allowed_currencies[] = get_option('mercanet_currencies_allowed');
        $currencies_list = get_available_currencies();

        if (empty($allowed_currencies)) {
            $allowed_currencies = array(
                '978');
        }


        if (in_array($customer_currency, $allowed_currencies)) {
            $data = $currencies_list;
            $options = array();
            foreach ($data as $key => $value) {
                $options[$key] = $value['id'];
            }
            $currency = array_search($customer_currency, $options);

            return $currencies_list[$currency]['iso'];
        } else {
            return $currencies_list['EUR']['iso'];
        }
    }

    /**
     * Get the payment params
     *
     * @param string, int, string
     * @return array
     */
    public static function get_params($order, $user_id = null, $return_url, $is_nx = false) {
        include_once plugin_dir_path(__DIR__) . 'settings/mercanet-settings.php';

        $transaction_amount = $order->order_total * 100;
        $display_method = get_option('mercanet_display_card_method');

        $params['amount'] = $transaction_amount;
        $currencyCode = self::get_mercanet_currency();
        $params['currencyCode'] = $currencyCode;
        $params['merchantId'] = get_option('mercanet_merchant_id');
        $params['normalReturnUrl'] = $return_url;
        $params['automaticResponseUrl'] = $return_url;
        $params['transactionReference'] = self::generate_reference($order->id);
        $params['keyVersion'] = get_option('mercanet_version_key');
        $params['orderId'] = $order->id;

        // Payment validation
        $params['captureMode'] = get_option('mercanet_payment_validation');

        // Anti-fraud control: foreign bin card
        if (get_option('mercanet_anti_fraud_control_pec') == 'no' && Mercanet_Api::is_allowed(array(
                    'PEC'))) {
            $fraudDataByPassCtrlList[] = 'ForeignBinCard';
        }

        // Anti-fraud control: IP country
        if (get_option('mercanet_anti_fraud_control_pip') == 'no' && Mercanet_Api::is_allowed(array(
                    'PIP'))) {
            $fraudDataByPassCtrlList[] = 'IpCountry';
        }

        // Anti-fraud control: simility ip card
        if (get_option('mercanet_anti_fraud_control_scp') == 'no' && Mercanet_Api::is_allowed(array(
                    'SCP'))) {
            $fraudDataByPassCtrlList[] = 'SimilityIpCard';
        }

        // Anti-fraud control: 3D-Secure status
        if (get_option('mercanet_anti_fraud_control_a3d') == 'no' && Mercanet_Api::is_allowed(array(
                    'A3D'))) {
            $fraudDataByPassCtrlList[] = '3DSStatus';
        }

        // Anti-fraud control: corporate card
        if (get_option('mercanet_anti_fraud_control_cco') == 'no' && Mercanet_Api::is_allowed(array(
                    'CCO'))) {
            $fraudDataByPassCtrlList[] = 'CorporateCard';
        }

        // Anti-fraud control: ecard
        if (get_option('mercanet_anti_fraud_control_cvi') == 'no' && Mercanet_Api::is_allowed(array(
                    'CVI'))) {
            $fraudDataByPassCtrlList[] = 'ECard';
        }

        // Anti-fraud control: black card list
        if (get_option('mercanet_anti_fraud_control_lnc') == 'no' && Mercanet_Api::is_allowed(array(
                    'LNC'))) {
            $fraudDataByPassCtrlList[] = 'BlackCard';
        }

        // Anti-fraud control: transaction amount
        if (get_option('mercanet_anti_fraud_control_amt') == 'no' && Mercanet_Api::is_allowed(array(
                    'AMT'))) {
            $fraudDataByPassCtrlList[] = 'CapCollarAmount';
        }

        // Anti-fraud control: velocity card
        if (get_option('mercanet_anti_fraud_control_ecc') == 'no' && Mercanet_Api::is_allowed(array(
                    'ECC'))) {
            $fraudDataByPassCtrlList[] = 'VelocityCard';
        }

        // Anti-fraud control: velocity IP
        if (get_option('mercanet_anti_fraud_control_eci') == 'no' && Mercanet_Api::is_allowed(array(
                    'ECI'))) {
            $fraudDataByPassCtrlList[] = 'VelocityIp';
        }

        if (!empty($fraudDataByPassCtrlList) && isset($fraudDataByPassCtrlList)) {
            $params['fraudData.byPassCtrlList'] = implode(',', $fraudDataByPassCtrlList);
        }

        // Anti-fraud control: 3-D Secure
        if (get_option('mercanet_anti_fraud_control_3ds') == 'yes' && ((float) $order->order_total < (float) get_option('mercanet_min_amount_3ds'))) {
            $params['fraudData.bypass3DS'] = 'ALL';
        }

        // Redirection
        if (get_option('mercanet_redirect_payment') == 'yes') {
            $params['paypageData.bypassReceiptPage'] = 'Y';
        }

        // Notification customer
        if (get_option('mercanet_notify_customer') == 'yes') {
            $params['customerContact.email'] = self::check_regexp_data("EMAIL", $order->billing_email, 128);
        }

        // Allowed countries
        $countries_list = get_option('mercanet_allowed_countries');

        if (get_option('mercanet_allowed_countries_check') == 'no') {
            unset($params['fraudData.allowedCardCountryList']);            
        } else if (!empty($countries_list)) {
            $params['fraudData.allowedCardCountryList'] = is_array($countries_list) ? implode(',', $countries_list) : $countries_list;
        } else {
            $params['fraudData.allowedCardCountryList'] = 'FRA';
        }

        // Available languages
        $languages_list = get_languages_available();
        $current_language = strtoupper(substr(get_bloginfo('language'), 0, 2));
        if (array_key_exists($current_language, $languages_list)) {
            $params['customerLanguage'] = $current_language;
        } else {
            $params['customerLanguage'] = $languages_list[get_option('mercanet_default_payment_page_language')]['id'];
        }

        // Payment brand        
        $payment_mean_brand = (is_object(WC()->session)) ?  WC()->session->get('payment_mean_brand') : "";
        $mercanet_card_allowed = get_option('mercanet_card_allowed');
        if (!empty($payment_mean_brand) && $display_method == 'DISPLAY_CARDS') {
            $array_card = array();
            $params['paymentMeanBrandList'] = WC()->session->get('payment_mean_brand');
        } elseif (!empty($mercanet_card_allowed)) {
            is_array(get_option('mercanet_card_allowed')) ? $array_card = get_option('mercanet_card_allowed') : $array_card[] = get_option('mercanet_card_allowed');
            if (!in_array('ALL', $array_card)) {
                $params['paymentMeanBrandList'] = implode(',', get_option('mercanet_card_allowed'));
            }
        }
        
        // nx restrictions
        if ($is_nx) {
            $cards_with_ntime = explode(',', Mercanet_Api::CARDS_WITH_N_TIMES);
            if (!in_array('ALL', $array_card)) {
                $first = true;            
                foreach ($array_card as $card) {
                    if (in_array($card, $cards_with_ntime)) {
                        if ($first) {
                            $params['paymentMeanBrandList'] = $card;
                            $first = false;
                        } else {
                            $params['paymentMeanBrandList'] .= ',' . $card;
                        }
                    }
                }
            } else {
                $params['paymentMeanBrandList'] = Mercanet_Api::CARDS_WITH_N_TIMES;
            }
        }
        // Theme configuration
        if (get_option('mercanet_theme_configuration')) {
            $params['templateName'] = get_option('mercanet_theme_configuration');
        }

        // Capture day
        $capture_day = get_option('mercanet_capture_day');
        if ($capture_day && $capture_day > 0) {
            $params['captureDay'] = $capture_day;
        }

        // One-clik payment
        if (get_option('mercanet_activation_one') == 'yes' && self::is_allowed(array(
                    'ONE')) && $user_id) {
            $params['merchantWalletId'] = Mercanet_Wallet::get_wallet_by_user($user_id);
        }

        // NxCB
        is_array(get_option('mercanet_card_allowed')) ? $array_card = get_option('mercanet_card_allowed') : $array_card[] = get_option('mercanet_card_allowed');

        // cetelem
        if (self::is_allowed(array(
                    'FCB')) && !$is_nx) {
            $iso_convert = self::getAvailableCountries();

            $params['captureMode'] = 'IMMEDIATE';
            $params['captureDay'] = 0;
            $params['paymentPattern'] = 'ONE_SHOT';
            $gender = 'M';
            $params['holderContact.title'] = $gender;
            $params['holderContact.firstname'] = self::check_regexp_data("A", $order->billing_first_name, 50);
            $params['holderContact.lastname'] = self::check_regexp_data("A", $order->billing_last_name, 50);
            $params['holderContact.phone'] = self::check_regexp_data("PHONE", $order->billing_phone);
            $params['holderContact.email'] = self::check_regexp_data("EMAIL", $order->billing_email, 128);

            $params['billingContact.firstname'] = self::check_regexp_data("A", $order->billing_first_name, 50);
            $params['billingContact.lastname'] = self::check_regexp_data("A", $order->billing_last_name, 50);
            $params['billingAddress.street'] = self::check_regexp_data("ANU-R", $order->billing_address_1, 50);
            if ($order->billing_address_2) {
                $params['billingAddress.addressAdditional1'] = self::check_regexp_data("ANU-R", $order->billing_address_2, 50);
            }
            $params['billingAddress.zipCode'] = self::check_regexp_data("AN-R", $order->billing_postcode, 10);
            $params['billingAddress.city'] = self::check_regexp_data("ANU-R", $order->billing_city, 50);
            $iso_code = 'FRA';
            if (key_exists($order->billing_country, $iso_convert)) {
                $iso_code = $iso_convert[$order->billing_country]['id'];
            }
            $params['billingAddress.country'] = $iso_code;

            $params['deliveryContact.firstname'] = self::check_regexp_data("A", $order->shipping_first_name, 50);
            $params['deliveryContact.lastname'] = self::check_regexp_data("A", $order->shipping_last_name, 50);
            $params['deliveryAddress.street'] = self::check_regexp_data("ANU-R", $order->shipping_address_1, 50);
            if ($order->shipping_address_2) {
                $params['deliveryAddress.addressAdditional1'] = self::check_regexp_data("ANU-R", $order->shipping_address_2, 50);
            }
            $params['deliveryAddress.zipCode'] = self::check_regexp_data("AN-R", $order->shipping_postcode, 10);
            $params['deliveryAddress.city'] = self::check_regexp_data("ANU-R", $order->shipping_city, 50);
            $iso_code = 'FRA';
            if (key_exists($order->shipping_country, $iso_convert)) {
                $iso_code = $iso_convert[$order->shipping_country]['id'];
            }

            $params['deliveryAddress.country'] = $iso_code;
        }

        if (self::is_allowed(array('PRE')) && !$is_nx && $user_id) {
            $params['captureMode'] = 'IMMEDIATE';
            $params['captureDay'] = 0;
            $params['paymentPattern'] = 'ONE_SHOT';
            $params['paymentMeanData.presto.paymentMeanCustomerId'] = $user_id;
            $params['paymentMeanData.presto.financialProduct'] = 'CLA';
            $params['paymentMeanData.presto.prestoCardType'] = '';
//            if (get_option('mercanet_activation_presto') == "yes") {
//                $presto_amount = floatval($transaction_amount) / 100;
//                if (!($presto_amount >= 1500.01 && $presto_amount < 150.00)) {
//                    $params['paymentMeanData.presto.financialProduct'] = 'CCH';
//                    $params['paymentMeanData.presto.prestoCardType'] = 'A';
//                }
//            }
            $customer = get_userdata($user_id);
            $params['shoppingCartDetail.mainProduct'] = '320';
            $params['customerContact.firstname'] = self::check_regexp_data("A", $customer->first_name, 50);
            $params['customerContact.lastname'] = self::check_regexp_data("A", $customer->last_name, 50);
            if ($customer->billing_phone) {
                $params['customerContact.phone'] = self::check_regexp_data("PHONE", $customer->billing_phone);
            }

            $params['customerAddress.addressAdditional1'] = self::check_regexp_data("ANU-R", $customer->billing_address_1, 50);
            if ($customer->billing_address_2) {
                $params['customerAddress.addressAdditional2'] = self::check_regexp_data("ANU-R", $customer->billing_address_2, 50);
            }
            $params['customerAddress.zipCode'] = self::check_regexp_data("AN-R", $customer->billing_postcode, 10);
            $params['customerAddress.city'] = self::check_regexp_data("ANU-R", $customer->billing_city, 50);
        }

        return $params;
    }

    /**
     * Get available Countries with key config
     * @return type
     */
    public static function getAvailableCountries() {
        return array(
            'FR' => array(
                'id' => 'FRA',
                'name' => 'France',
                'phone_code' => '+33'
            ),
            'AW' => array(
                'id' => 'ABW',
                'name' => 'Aruba',
            ),
            'AF' => array(
                'id' => 'AFG',
                'name' => 'Afghanistan',
            ),
            'AO' => array(
                'id' => 'AGO',
                'name' => 'Angola',
            ),
            'AI' => array(
                'id' => 'AIA',
                'name' => 'Anguilla',
            ),
            'AX' => array(
                'id' => 'ALA',
                'name' => 'Ãland îles,',
            ),
            'AL' => array(
                'id' => 'ALB',
                'name' => 'Albanie',
            ),
            'AD' => array(
                'id' => 'AND',
                'name' => 'Andorre',
                'phone_code' => '+376'
            ),
            'AE' => array(
                'id' => 'ARE',
                'name' => 'Émirats Arabes Unis',
            ),
            'AR' => array(
                'id' => 'ARG',
                'name' => 'Argentine',
            ),
            'AM' => array(
                'id' => 'ARM',
                'name' => 'Arménie',
            ),
            'AS' => array(
                'id' => 'ASM',
                'name' => 'Samoa américaines',
            ),
            'AQ' => array(
                'id' => 'ATA',
                'name' => 'Antarctique',
            ),
            'TF' => array(
                'id' => 'ATF',
                'name' => 'Terres Autrales française',
            ),
            'AG' => array(
                'id' => 'ATG',
                'name' => 'Antigua-Et-Barbuda',
            ),
            'AU' => array(
                'id' => 'AUS',
                'name' => 'Australie',
            ),
            'AT' => array(
                'id' => 'AUT',
                'name' => 'Autriche',
                'phone_code' => '+43'
            ),
            'AZ' => array(
                'id' => 'AZE',
                'name' => 'Azerbaïdjan',
            ),
            'BI' => array(
                'id' => 'BDI',
                'name' => 'Burundi',
            ),
            'BE' => array(
                'id' => 'BEL',
                'name' => 'Belgique',
                'phone_code' => '+32'
            ),
            'BJ' => array(
                'id' => 'BEN',
                'name' => 'Bénin',
            ),
            'BES' => array(
                'id' => 'BES',
                'name' => 'Bonaire, Saint-Eustache et Saba',
            ),
            'BF' => array(
                'id' => 'BFA',
                'name' => 'Burkina Faso',
            ),
            'BD' => array(
                'id' => 'BGD',
                'name' => 'Bangladesh',
            ),
            'BG' => array(
                'id' => 'BGR',
                'name' => 'Bulgarie',
                'phone_code' => '+359'
            ),
            'BH' => array(
                'id' => 'BHR',
                'name' => 'Bahreïn',
            ),
            'BS' => array(
                'id' => 'BHS',
                'name' => 'Bahamas',
            ),
            'BA' => array(
                'id' => 'BIH',
                'name' => 'Bosnie-Herzégovine',
            ),
            'KN' => array(
                'id' => 'BLM',
                'name' => 'Saint-Kitts-Et-Nevis',
            ),
            'BY' => array(
                'id' => 'BLR',
                'name' => 'Bélarus',
            ),
            'BZ' => array(
                'id' => 'BLZ',
                'name' => 'Belize',
            ),
            'BM' => array(
                'id' => 'BMU',
                'name' => 'Bermudes',
            ),
            'BO' => array(
                'id' => 'BOL',
                'name' => 'Bolivie',
            ),
            'BR' => array(
                'id' => 'BRA',
                'name' => 'Brésil',
            ),
            'BB' => array(
                'id' => 'BRB',
                'name' => 'Barbade',
            ),
            'BN' => array(
                'id' => 'BRN',
                'name' => 'Brunei Darussalam',
            ),
            'BT' => array(
                'id' => 'BTN',
                'name' => 'Bhoutan',
            ),
            'BV' => array(
                'id' => 'BVT',
                'name' => 'Bouvet, île',
            ),
            'BW' => array(
                'id' => 'BWA',
                'name' => 'Botswana',
            ),
            'CF' => array(
                'id' => 'CAF',
                'name' => 'Centrafricaine, république',
            ),
            'CA' => array(
                'id' => 'CAN',
                'name' => 'Canada',
            ),
            'CC' => array(
                'id' => 'CCK',
                'name' => 'Cocos (Keeling), îles',
            ),
            'CH' => array(
                'id' => 'CHE',
                'name' => 'Suisse',
                'phone_code' => '+41'
            ),
            'CL' => array(
                'id' => 'CHL',
                'name' => 'Chili',
            ),
            'CN' => array(
                'id' => 'CHN',
                'name' => 'Chine',
            ),
            'CI' => array(
                'id' => 'CIV',
                'name' => 'Côte d\'ivoire',
            ),
            'CM' => array(
                'id' => 'CMR',
                'name' => 'Cameroun',
            ),
            'CD' => array(
                'id' => 'COD',
                'name' => 'Congo, la république démocratique',
            ),
            'CG' => array(
                'id' => 'COG',
                'name' => 'Congo',
            ),
            'CK' => array(
                'id' => 'COK',
                'name' => 'Cook, îles',
            ),
            'CO' => array(
                'id' => 'COL',
                'name' => 'Colombie',
            ),
            'KM' => array(
                'id' => 'COM',
                'name' => 'Comores',
                'phone_code' => '+269'
            ),
            'CV' => array(
                'id' => 'CPV',
                'name' => 'Cap-vert',
            ),
            'CR' => array(
                'id' => 'CRI',
                'name' => 'Costa Rica',
            ),
            'CU' => array(
                'id' => 'CUB',
                'name' => 'Cuba',
            ),
            'CUW' => array(
                'id' => 'CUW',
                'name' => 'Curaçao ',
            ),
            'CX' => array(
                'id' => 'CXR',
                'name' => 'Christmas, îles',
            ),
            'KY' => array(
                'id' => 'CYM',
                'name' => 'Caïmans, îles',
            ),
            'CY' => array(
                'id' => 'CYP',
                'name' => 'Chypre',
                'phone_code' => '+357'
            ),
            'CZ' => array(
                'id' => 'CZE',
                'name' => 'Tchèque, république',
                'phone_code' => '+420'
            ),
            'DE' => array(
                'id' => 'DEU',
                'name' => 'Allemagne',
                'phone_code' => '+49'
            ),
            'DJ' => array(
                'id' => 'DJI',
                'name' => 'Djibouti',
            ),
            'DM' => array(
                'id' => 'DMA',
                'name' => 'Dominique',
            ),
            'DK' => array(
                'id' => 'DNK',
                'name' => 'Danemark',
                'phone_code' => '+45'
            ),
            'DO' => array(
                'id' => 'DOM',
                'name' => 'Dominicaine, république',
            ),
            'DZ' => array(
                'id' => 'DZA',
                'name' => 'Algérie',
            ),
            'EC' => array(
                'id' => 'ECU',
                'name' => 'Équateur',
            ),
            'EG' => array(
                'id' => 'EGY',
                'name' => 'Égypte',
            ),
            'ER' => array(
                'id' => 'ERI',
                'name' => 'Érythrée',
            ),
            'EH' => array(
                'id' => 'ESH',
                'name' => 'Sahara Occidental',
            ),
            'ES' => array(
                'id' => 'ESP',
                'name' => 'Espagne',
                'phone_code' => '+34'
            ),
            'EE' => array(
                'id' => 'EST',
                'name' => 'Estonie',
                'phone_code' => '+372'
            ),
            'ET' => array(
                'id' => 'ETH',
                'name' => 'Éthiopie',
            ),
            'FI' => array(
                'id' => 'FIN',
                'name' => 'Finlande',
                'phone_code' => '+358'
            ),
            'FJ' => array(
                'id' => 'FJI',
                'name' => 'Fidji',
            ),
            'FK' => array(
                'id' => 'FLK',
                'name' => 'Falkland, îles (Malvinas)',
            ),
            'FO' => array(
                'id' => 'FRO',
                'name' => 'Féroé, îles',
                'phone_code' => '+298'
            ),
            'FM' => array(
                'id' => 'FSM',
                'name' => 'Micronésie, état fédérés',
            ),
            'GA' => array(
                'id' => 'GAB',
                'name' => 'Gabon',
            ),
            'GB' => array(
                'id' => 'GBR',
                'name' => 'Royaume-Uni',
                'phone_code' => '+44'
            ),
            'GE' => array(
                'id' => 'GEO',
                'name' => 'Géorgie',
            ),
            'GG' => array(
                'id' => 'GGY',
                'name' => 'Guernesey',
            ),
            'GH' => array(
                'id' => 'GHA',
                'name' => 'Ghana',
            ),
            'GI' => array(
                'id' => 'GIB',
                'name' => 'Gibraltar',
                'phone_code' => '+350'
            ),
            'GN' => array(
                'id' => 'GIN',
                'name' => 'Guinée',
            ),
            'GP' => array(
                'id' => 'GLP',
                'name' => 'Guadeloupe',
                'phone_code' => '+590'
            ),
            'GM' => array(
                'id' => 'GMB',
                'name' => 'Gambie',
            ),
            'GW' => array(
                'id' => 'GNB',
                'name' => 'Guinée-bissau',
            ),
            'GQ' => array(
                'id' => 'GNQ',
                'name' => 'Guinée équatoriale',
            ),
            'GR' => array(
                'id' => 'GRC',
                'name' => 'Grèce',
                'phone_code' => '+30'
            ),
            'GD' => array(
                'id' => 'GRD',
                'name' => 'Grenade',
            ),
            'GL' => array(
                'id' => 'GRL',
                'name' => 'Groenland',
            ),
            'GT' => array(
                'id' => 'GTM',
                'name' => 'Guatemala',
            ),
            'GF' => array(
                'id' => 'GUF',
                'name' => 'Guyane française',
                'phone_code' => '+594'
            ),
            'GU' => array(
                'id' => 'GUM',
                'name' => 'Guam',
            ),
            'GY' => array(
                'id' => 'GUY',
                'name' => 'Guyana',
            ),
            'HK' => array(
                'id' => 'HKG',
                'name' => 'Hong Kong',
            ),
            'HM' => array(
                'id' => 'HMD',
                'name' => 'Heard-et-Îles Macdonald',
            ),
            'HN' => array(
                'id' => 'HND',
                'name' => 'Honduras',
            ),
            'HR' => array(
                'id' => 'HRV',
                'name' => 'Croatie',
                'phone_code' => '+386'
            ),
            'HT' => array(
                'id' => 'HTI',
                'name' => 'Haïti ',
            ),
            'HU' => array(
                'id' => 'HUN',
                'name' => 'Hongrie',
                'phone_code' => '+36'
            ),
            'ID' => array(
                'id' => 'IDN',
                'name' => 'Indonésie ',
            ),
            'IM' => array(
                'id' => 'IMN',
                'name' => 'Île de Man',
            ),
            'IN' => array(
                'id' => 'IND',
                'name' => 'Inde',
            ),
            'IO' => array(
                'id' => 'IOT',
                'name' => 'Océan Indien, Territoire Britannique',
            ),
            'IE' => array(
                'id' => 'IRL',
                'name' => 'Irlande',
                'phone_code' => '+353'
            ),
            'IR' => array(
                'id' => 'IRN',
                'name' => 'Iran, république Islamique',
            ),
            'IQ' => array(
                'id' => 'IRQ',
                'name' => 'Iraq',
            ),
            'IS' => array(
                'id' => 'ISL',
                'name' => 'Islande',
                'phone_code' => '+354'
            ),
            'IL' => array(
                'id' => 'ISR',
                'name' => 'Israël',
            ),
            'IT' => array(
                'id' => 'ITA',
                'name' => 'Italie',
                'phone_code' => '+39'
            ),
            'JM' => array(
                'id' => 'JAM',
                'name' => 'Jamaïque',
            ),
            'JE' => array(
                'id' => 'JEY',
                'name' => 'Jersey',
            ),
            'JO' => array(
                'id' => 'JOR',
                'name' => 'Jordanie',
            ),
            'JP' => array(
                'id' => 'JPN',
                'name' => 'Japon',
            ),
            'KZ' => array(
                'id' => 'KAZ',
                'name' => 'Kazakhstan',
            ),
            'KE' => array(
                'id' => 'KEN',
                'name' => 'Kenya',
            ),
            'KG' => array(
                'id' => 'KGZ',
                'name' => 'Kirghizistan',
            ),
            'KH' => array(
                'id' => 'KHM',
                'name' => 'Cambodge',
            ),
            'KI' => array(
                'id' => 'KIR',
                'name' => 'Kiribati',
            ),
            'BL' => array(
                'id' => 'KNA',
                'name' => 'Saint-barthélemy',
            ),
            'KR' => array(
                'id' => 'KOR',
                'name' => 'Corée',
            ),
            'KW' => array(
                'id' => 'KWT',
                'name' => 'Koweït',
            ),
            'LA' => array(
                'id' => 'LAO',
                'name' => 'Lao, république démocratique populaire',
            ),
            'LB' => array(
                'id' => 'LBN',
                'name' => 'Liban',
            ),
            'LR' => array(
                'id' => 'LBR',
                'name' => 'Libéria',
            ),
            'LY' => array(
                'id' => 'LBY',
                'name' => 'Libye',
            ),
            'LCA' => array(
                'id' => 'LCA',
                'name' => 'Sainte-hélène, Ascension et Tritan Da Cunha',
            ),
            'LI' => array(
                'id' => 'LIE',
                'name' => 'Liechtenstein',
                'phone_code' => '+423'
            ),
            'LK' => array(
                'id' => 'LKA',
                'name' => 'Sri Lanka',
            ),
            'LS' => array(
                'id' => 'LSO',
                'name' => 'Lesotho',
            ),
            'LT' => array(
                'id' => 'LTU',
                'name' => 'Lituanie',
                'phone_code' => '+370'
            ),
            'LU' => array(
                'id' => 'LUX',
                'name' => 'Luxembourg',
                'phone_code' => '+352'
            ),
            'LV' => array(
                'id' => 'LVA',
                'name' => 'Lettonie',
                'phone_code' => '+371'
            ),
            'MO' => array(
                'id' => 'MAC',
                'name' => 'Macao',
            ),
            'MF' => array(
                'id' => 'MAF',
                'name' => 'Saint-Martin(partie française)',
            ),
            'MA' => array(
                'id' => 'MAR',
                'name' => 'Maroc',
            ),
            'MC' => array(
                'id' => 'MCO',
                'name' => 'Monaco',
                'phone_code' => '+377'
            ),
            'MD' => array(
                'id' => 'MDA',
                'name' => 'Moldova',
            ),
            'MG' => array(
                'id' => 'MDG',
                'name' => 'Madagascar',
            ),
            'MV' => array(
                'id' => 'MDV',
                'name' => 'Maldives',
            ),
            'MX' => array(
                'id' => 'MEX',
                'name' => 'Mexique',
            ),
            'MH' => array(
                'id' => 'MHL',
                'name' => 'Marshall, îles',
            ),
            'MK' => array(
                'id' => 'MKD',
                'name' => 'Macédoine',
            ),
            'ML' => array(
                'id' => 'MLI',
                'name' => 'Mali',
            ),
            'MT' => array(
                'id' => 'MLT',
                'name' => 'Malte',
                'phone_code' => '+356'
            ),
            'MM' => array(
                'id' => 'MMR',
                'name' => 'Myanmar',
            ),
            'ME' => array(
                'id' => 'MNE',
                'name' => 'Monténégro',
            ),
            'MN' => array(
                'id' => 'MNG',
                'name' => 'Mongolie',
            ),
            'MP' => array(
                'id' => 'MNP',
                'name' => 'Mariannes du Nord',
            ),
            'MZ' => array(
                'id' => 'MOZ',
                'name' => 'Mozambique',
            ),
            'MR' => array(
                'id' => 'MRT',
                'name' => 'Mauritanie',
            ),
            'MS' => array(
                'id' => 'MSR',
                'name' => 'Montserrat',
            ),
            'MQ' => array(
                'id' => 'MTQ',
                'name' => 'Martinique',
                'phone_code' => '+596'
            ),
            'MU' => array(
                'id' => 'MUS',
                'name' => 'Maurice',
            ),
            'MW' => array(
                'id' => 'MWI',
                'name' => 'Malawi',
            ),
            'MY' => array(
                'id' => 'MYS',
                'name' => 'Malaisie',
            ),
            'YT' => array(
                'id' => 'MYT',
                'name' => 'Mayotte',
            ),
            'NA' => array(
                'id' => 'NAM',
                'name' => 'Namibie',
            ),
            'NC' => array(
                'id' => 'NCL',
                'name' => 'Nouvelle-Calédonie ',
            ),
            'NE' => array(
                'id' => 'NER',
                'name' => 'Niger',
            ),
            'NF' => array(
                'id' => 'NFK',
                'name' => 'Norfolk',
            ),
            'NG' => array(
                'id' => 'NGA',
                'name' => 'Nigéria',
            ),
            'NI' => array(
                'id' => 'NIC',
                'name' => 'Nicaragua',
            ),
            'NU' => array(
                'id' => 'NIU',
                'name' => 'Niué',
            ),
            'NL' => array(
                'id' => 'NLD',
                'name' => 'Pays-bas',
                'phone_code' => '+31'
            ),
            'NO' => array(
                'id' => 'NOR',
                'name' => 'Norvège',
                'phone_code' => '+47'
            ),
            'NP' => array(
                'id' => 'NPL',
                'name' => 'Népal',
            ),
            'NR' => array(
                'id' => 'NRU',
                'name' => 'Nauru ',
            ),
            'NZ' => array(
                'id' => 'NZL',
                'name' => 'Nouvelle-Zélande ',
            ),
            'OM' => array(
                'id' => 'OMN',
                'name' => 'Oman',
            ),
            'PK' => array(
                'id' => 'PAK',
                'name' => 'Pakistan',
            ),
            'PA' => array(
                'id' => 'PAN',
                'name' => 'Panama',
            ),
            'PN' => array(
                'id' => 'PCN',
                'name' => 'Pitcairn',
            ),
            'PE' => array(
                'id' => 'PER',
                'name' => 'Pérou',
            ),
            'PH' => array(
                'id' => 'PHL',
                'name' => 'Philippines',
            ),
            'PW' => array(
                'id' => 'PLW',
                'name' => 'Palaos',
            ),
            'PG' => array(
                'id' => 'PNG',
                'name' => 'Papouasie-Nouvelle-Guinée',
            ),
            'PL' => array(
                'id' => 'POL',
                'name' => 'Pologne',
                'phone_code' => '+48'
            ),
            'PR' => array(
                'id' => 'PRI',
                'name' => 'Porto Rico',
            ),
            'KP' => array(
                'id' => 'PRK',
                'name' => 'Corée, république populaire démocratique',
            ),
            'PT' => array(
                'id' => 'PRT',
                'name' => 'Portugal',
                'phone_code' => '+351'
            ),
            'PY' => array(
                'id' => 'PRY',
                'name' => 'Paraguay',
            ),
            'PS' => array(
                'id' => 'PSE',
                'name' => 'Palestinien occupé',
            ),
            'PF' => array(
                'id' => 'PYF',
                'name' => 'Polynésie',
            ),
            'QA' => array(
                'id' => 'QAT',
                'name' => 'Qatar',
            ),
            'RE' => array(
                'id' => 'REU',
                'name' => 'Réunion ',
                'phone_code' => '+262'
            ),
            'RO' => array(
                'id' => 'ROU',
                'name' => 'Roumanie',
            ),
            'RU' => array(
                'id' => 'RUS',
                'name' => 'Russie',
            ),
            'RW' => array(
                'id' => 'RWA',
                'name' => 'Rwanda',
            ),
            'SA' => array(
                'id' => 'SAU',
                'name' => 'Arabie Saoudite',
            ),
            'SD' => array(
                'id' => 'SDN',
                'name' => 'Soudan',
            ),
            'SN' => array(
                'id' => 'SEN',
                'name' => 'Sénégal',
            ),
            'SG' => array(
                'id' => 'SGP',
                'name' => 'Singapour',
            ),
            'GS' => array(
                'id' => 'SGS',
                'name' => 'Géorgie du Sud-Et-Les îles Sandwich du Sud',
            ),
            'MF' => array(
                'id' => 'SHN',
                'name' => 'Saint-Marin',
                'phone_code' => '+378'
            ),
            'SJ' => array(
                'id' => 'SJM',
                'name' => 'Svalbard et île Jan Mayen',
            ),
            'SB' => array(
                'id' => 'SLB',
                'name' => 'Salomon',
            ),
            'SL' => array(
                'id' => 'SLE',
                'name' => 'Sierra Leone',
            ),
            'SV' => array(
                'id' => 'SLV',
                'name' => 'El Salvador',
            ),
            'SM' => array(
                'id' => 'SMR',
                'name' => 'Saint-Martin (partie néerlandaise)',
            ),
            'SO' => array(
                'id' => 'SOM',
                'name' => 'Somalie',
            ),
            'VA' => array(
                'id' => 'SPM',
                'name' => 'Saint-Siège',
            ),
            'RS' => array(
                'id' => 'SRB',
                'name' => 'Serbie',
            ),
            'SSD' => array(
                'id' => 'SSD',
                'name' => 'Soudan Du Sud',
            ),
            'ST' => array(
                'id' => 'STP',
                'name' => 'Sao Tomé-Et-Principe',
            ),
            'SR' => array(
                'id' => 'SUR',
                'name' => 'Suriname',
            ),
            'SK' => array(
                'id' => 'SVK',
                'name' => 'Slovaquie',
                'phone_code' => '+421'
            ),
            'SI' => array(
                'id' => 'SVN',
                'name' => 'Slovénie',
                'phone_code' => '+386'
            ),
            'SE' => array(
                'id' => 'SWE',
                'name' => 'Suède',
                'phone_code' => '+46'
            ),
            'SZ' => array(
                'id' => 'SWZ',
                'name' => 'Swaziland',
            ),
            'PM' => array(
                'id' => 'SXM',
                'name' => 'Saint-Pierre-Et-Miquelon',
                'phone_code' => '+508'
            ),
            'SC' => array(
                'id' => 'SYC',
                'name' => 'Seychelles',
            ),
            'SY' => array(
                'id' => 'SYR',
                'name' => 'Syrienne, république arabe',
            ),
            'TC' => array(
                'id' => 'TCA',
                'name' => 'Turks-Et-Caïcos',
            ),
            'TD' => array(
                'id' => 'TCD',
                'name' => 'Tchad',
            ),
            'TG' => array(
                'id' => 'TGO',
                'name' => 'Togo',
            ),
            'TH' => array(
                'id' => 'THA',
                'name' => 'Thaïlande ',
            ),
            'TJ' => array(
                'id' => 'TJK',
                'name' => 'Tadjikistan ',
            ),
            'TK' => array(
                'id' => 'TKL',
                'name' => 'Tokelau',
            ),
            'TM' => array(
                'id' => 'TKM',
                'name' => 'Turkménistan',
            ),
            'TL' => array(
                'id' => 'TLS',
                'name' => 'Timor-Leste',
            ),
            'TO' => array(
                'id' => 'TON',
                'name' => 'Tonga',
            ),
            'TT' => array(
                'id' => 'TTO',
                'name' => 'Trinité-Et-Tobago',
            ),
            'TN' => array(
                'id' => 'TUN',
                'name' => 'Tunisie',
            ),
            'TR' => array(
                'id' => 'TUR',
                'name' => 'Turquie',
            ),
            'TV' => array(
                'id' => 'TUV',
                'name' => 'Tuvalu',
            ),
            'TW' => array(
                'id' => 'TWN',
                'name' => 'Taïwan',
            ),
            'TZ' => array(
                'id' => 'TZA',
                'name' => 'Tanzanie',
            ),
            'UG' => array(
                'id' => 'UGA',
                'name' => 'Ouganda',
            ),
            'UA' => array(
                'id' => 'UKR',
                'name' => 'Ukraine',
            ),
            'UMI' => array(
                'id' => 'UMI',
                'name' => 'Îles mineures éloignées des États-Unis',
            ),
            'UY' => array(
                'id' => 'URY',
                'name' => 'Uruguay',
            ),
            'US' => array(
                'id' => 'USA',
                'name' => 'États-Unis ',
            ),
            'UZ' => array(
                'id' => 'UZB',
                'name' => 'Ouzbékistan ',
            ),
            'VC' => array(
                'id' => 'VAT',
                'name' => 'Saint-Vincent-Et-Les Grenadines',
            ),
            'LC' => array(
                'id' => 'VCT',
                'name' => 'Sainte-Lucie',
            ),
            'VE' => array(
                'id' => 'VEN',
                'name' => 'Venezuela',
            ),
            'VG' => array(
                'id' => 'VGB',
                'name' => 'Îles vierges britaniques',
            ),
            'VI' => array(
                'id' => 'VIR',
                'name' => 'Îles vierges des États-Unis',
            ),
            'VN' => array(
                'id' => 'VNM',
                'name' => 'Vietnam',
            ),
            'VU' => array(
                'id' => 'VUT',
                'name' => 'Vanuatu',
            ),
            'WF' => array(
                'id' => 'WLF',
                'name' => 'Wallis et Futuna',
            ),
            'WS' => array(
                'id' => 'WSM',
                'name' => 'Samoa',
            ),
            'YE' => array(
                'id' => 'YEM',
                'name' => 'Yémen ',
            ),
            'ZA' => array(
                'id' => 'ZAF',
                'name' => 'Afrique Du Sud',
            ),
            'ZM' => array(
                'id' => 'ZMB',
                'name' => 'Zambie',
            ),
            'ZW' => array(
                'id' => 'ZWE',
                'name' => 'Zimbabwe',
            ),
        );
    }

    /**
     * Get the refund params
     *
     * @param WC_Order object, float, string
     * @return array
     */
    public static function get_refund_params($order, $amount, $transaction_reference) {
        $currencyCode = self::get_mercanet_currency();
        $params['currencyCode'] = $currencyCode;
        $ws_interface_version = get_option('MERCANET_WS_INTERFACE_VERSION');
        $params['interfaceVersion'] = $ws_interface_version;
        $params['merchantId'] = get_option('mercanet_merchant_id');
        $params['keyVersion'] = get_option('mercanet_version_key');
        $params['operationOrigin'] = 'BATCH';
        $params['operationAmount'] = (float) $amount * 100;
        $params['transactionReference'] = $transaction_reference;
        ksort($params);
        $params['seal'] = Mercanet_Api::generate_seal($params, true, true);

        return $params;
    }

    /**
     * Generate SEAL
     *
     * @param array, bool
     * @return string
     */
    public static function generate_seal($params, $webservice = false, $need_params = false) {
        if (is_array($params)) {
            ksort($params);
        }

        if ($webservice == true) {
            if (isset($params['keyVersion'])) {
                unset($params['keyVersion']);
            }
        }
        if ($need_params) {
            $raw_data = self::get_raw_data($params, $webservice);
        } else {
            $raw_data = $params;
        }
        $secret_key = get_option('mercanet_secret_key');

        if (empty($secret_key)) {
            return false;
        }
        if ($webservice == true) {
            $seal = hash_hmac('sha256', utf8_encode($raw_data), $secret_key);
        } else {
            $seal = @hash('sha256', utf8_encode($raw_data . $secret_key));
        }

        return $seal;
    }

    /**
     * Check SEAL
     *
     * @param array, string, bool
     * @return bool
     */
    public static function check_seal($data, $seal_received, $webservice = false) {
        $secret_key = get_option('mercanet_secret_key');

        if (empty($secret_key)) {
            return false;
        }
        if ($webservice == true) {
            $data_array = (array) $data;
            if ($data_array['seal']) {
                unset($data_array['seal']);
            }
            ksort($data_array);
            $data = implode('', $data_array);
            $seal = hash_hmac('sha256', utf8_encode($data), $secret_key);
        } else {
            $seal = hash('sha256', utf8_encode($data . $secret_key));
        }

        if ($seal == $seal_received) {
            return true;
        }

        return false;
    }

    /**
     * Return data row
     *
     * @param array, bool
     * @return string
     */
    public static function get_raw_data($params, $webservice = false) {
        if ($webservice == true) {
            return implode('', $params);
        }

        return base64_encode(implode('|', array_map(
                                function ($value, $key) {
                            return $key . '=' . $value;
                        }, $params, array_keys($params)
                        )
        ));
    }

    /**
     * Return data from row data
     *
     * @param string
     * @return array
     */
    public static function get_data_from_raw_data($raw_data) {
        if (empty($raw_data)) {
            return false;
        }
        $data = array();

        foreach (explode('|', $raw_data) as $raw) {
            list($key, $value) = explode('=', $raw, 2);
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Generate a new transaction reference
     *
     * @param int
     * @return string
     */
    public static function generate_reference($id = null, $length = 21) {
        if (empty($id)) {
            $id = 0;
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $id_length = strlen($id);

        $random_string = $id;
        for ($i = 0; $i < ($length - $id_length); $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }

        return $random_string;
    }

    /**
     * Get the wordpress locale
     *
     * @return string
     */
    public static function get_locale() {
        $locale = get_locale();

        if (empty($locale)) {
            $locale = 'en_US';
        }

        return $locale;
    }

    /**
     * Get the response code message text in the current language
     *
     * @param string
     * @return mercanet_response_code Object
     */
    public static function get_message_response_code($code) {
        global $wpdb;
        $locale = self::get_locale();

        return $wpdb->get_row("SELECT rc_message FROM {$wpdb->prefix}mercanet_response_code
                                WHERE response_code = '$code' AND rc_locale = '$locale'");
    }

    /**
     * Retire les caractères non autorisé pour chaque type de champs
     * 
     * @param string $type
     * @param string $string
     * @return type
     */
    public static function check_regexp_data($type, $string, $length = 0) {
        $return_str = (is_string($string)) ? str_replace(array("'", "’"), "'", $string) : "";
        switch ($type) {
            case 'PHONE' :
                $first_occurence = (substr($return_str, 0, 1) === "+") ? "+" : "";
                $return_str = substr($first_occurence . preg_replace("/[^0-9]/", "", $return_str), 0, 30);
                break;
            
            case 'MOBILE' :
                $iso_convert = self::getAvailableCountries();                
                $phone = $string['phone'];
                $return_str = "";                
                if(preg_match("/^\+(?:[0-9] ?){6,14}[0-9]$/",$phone)) {                    
                    $return_str = $phone;
                } else if(array_key_exists('phone_code', $iso_convert[$string['iso_code']])) {
                    $iso_phone_code = $iso_convert[$string['iso_code']]['phone_code'];
                    $international_number = substr(preg_replace("/^(?:09|\+?63)(?:\d(?:-)?){9,10}$/", "", $phone), 0, 30);
                    $return_str = preg_replace('/^0/', $iso_phone_code, $international_number);
                }
                break;

            case 'EMAIL' :
                if (!filter_var($string, FILTER_VALIDATE_EMAIL))
                    $return_str = "";
                break;

            //Indique que les valeurs alphabétiques [aA-zZ] sont acceptées
            case 'A' :
                $return_str = preg_replace("/[^a-zA-Z]/", "", $return_str);
                break;

            //Tout caractère est accepté.
            case 'ANS' :
                break;

            //------------------------------------------------------------------
            //-----------------------RestrictedString---------------------------                  
            //Indique que seules certaines valeurs alphabétiques [aA-zZ] sont acceptées
            case 'A-R' :
                $return_str = preg_replace("/[^a-zA-Z]/", "", $return_str);
                break;

            //Les caractères suivants sont acceptés :
            //  - Alphabétique [aA-zZ]
            //  - Numerique [0-9]
            //  - Spécial _ . + - @
            //  - espace,
            case 'AN-R' :
                $return_str = preg_replace("/[^0-9a-zA-Z_+.\-@, ]/", "", $return_str);
                break;

            // RestrictedString
            //Les caractères suivants sont acceptés :
            //  - alphabétique [aA-zZ]
            //  - numerique [0-9]
            //  - spécial " ' ` _ + . - @ ,
            //  - espace
            //  - tout caractère linguistique de toute langue (à â ç é è ê ë î Ï ô ù ...)
            case 'ANU-R' :
                $return_str = preg_replace("/[^0-9a-zA-Z\"'_+.\-@,ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝáàâäãåçéèêëíìîïñóòôöõúùûüýÿ ]/", "", $return_str);
                break;
            //------------------------------------------------------------------
        }

        //Cas téléphone spécifique, au cas où ce soit du +33... par exemple
        if ($length != 0 && $type != "PHONE" && $type != "MOBILE")
            $return_str = mb_substr($return_str, 0, $length);

        return $return_str;
    }

}
