<?php

class Mercanet_Wallet {

    public function __construct() {
        add_action('woocommerce_before_my_account', array($this, 'output_wallet'), 10, 0);
    }

    public static function save($user_id, $wallet_id) {
        global $wpdb;

        if (empty($user_id) && empty($wallet_id)) {
            return false;
        }

        $wpdb->insert("{$wpdb->prefix}mercanet_wallet", array(
            'wallet_id' => $wallet_id,
            'user_id' => $user_id
                )
        );
    }

    /**
     * Return the wallet's. If not exist generate it
     *
     * @param id
     * @return string
     */
    public static function get_wallet_by_user($user_id) {
        global $wpdb;

        if (!empty($user_id)) {
            $wallet = $wpdb->get_row("SELECT wallet_id FROM {$wpdb->prefix}mercanet_wallet WHERE user_id = '$user_id'");

            if (empty($wallet)) {
                $wallet_id = self::generate_wallet($user_id);
            } else {
                $wallet_id = $wallet->wallet_id;
            }

            if (!empty($wallet_id)) {
                return $wallet_id;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Check if the new wallet exist
     *
     * @param string
     * @return int
     */
    public static function check_wallet($wallet_id) {
        global $wpdb;

        if (empty($wallet_id)) {
            return false;
        }

        $value = $wpdb->get_row("SELECT wallet_id FROM {$wpdb->prefix}mercanet_wallet WHERE wallet_id = '$wallet_id'");

        if (empty($value)) {
            return true;
        }
        if (strcmp($value->wallet_id, $wallet_id) == 0) {
            return false;
        }
    }

    /**
     * Generate and save a new wallet for an user
     *
     * @param int
     * @return string
     */
    public static function generate_wallet($user_id) {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_tmp = str_shuffle($characters);
        $new_wallet = substr($characters_tmp, 0, 21);

        while (self::check_wallet($new_wallet) == false) {
            $characters_tmp = str_shuffle($characters);
            $new_wallet = substr($characters_tmp, 0, 21);
        }

        self::save($user_id, $new_wallet);
        return $new_wallet;
    }

    /**
     * Generate the wallet button to redirect on Mercanet wallet
     */
    public function output_wallet() {
        if (Mercanet_Api::is_allowed(array('ONE'))) {
            $user_id = get_current_user_id();

            if (empty($user_id)) {
                return false;
            }
            $data = self::get_data();
            $melange = $data['merchantWalletId'];
            $request_date_time = $data['requestDateTime'];
            if (get_option('mercanet_test_mode') == 'yes') {
                $url = get_option('MERCANET_WALLET_URL_TEST');
            } else {
                $url = get_option('MERCANET_WALLET_URL');
            }
            $interface_version = get_option('MERCANET_WALLET_INTERFACE_VERSION');
            ksort($data);
            $seal = Mercanet_Api::generate_seal($data, false, true);
            $raw_data = Mercanet_Api::get_raw_data($data);

            return self::generate_wallet_redirection($seal, $raw_data, $interface_version, $url, $request_date_time, $melange);
        }
    }

    /**
     * Generate the wallet button redirection
     */
    public function generate_wallet_redirection($seal, $data, $interface_version, $url, $request_date_time, $wallet_id) {

        $h2 = __('My Mercanet wallet', 'mercanet');
        $span = __('Manage my Mercanet wallet', 'mercanet');
        echo <<<HTML
        <h2>$h2</h2>
        <form id="mercanet_wallet_form" method="POST" action="$url">
            <input type="hidden" name="interfaceVersion" value="$interface_version" />
            <input type="hidden" name="requestDateTime" value="$request_date_time" />
            <input type="hidden" name="merchantWalletId" value="$wallet_id" />
            <input type="hidden" name="data" value="$data" />
            <input type="hidden" name="Encode" value="base64" />
            <input type="hidden" name="seal" value="$seal" />
            <button type="submit" value="submit" class="button" form="mercanet_wallet_form">
                <span>$span</span>
            </button>
        </form>        
HTML;
    }

    /**
     * Get the wallet params
     *
     * @return array
     */
    public function get_data() {
        $user_id = get_current_user_id();

        if (empty($user_id)) {
            return false;
        }

        $data = array();
        $data['normalReturnUrl'] = get_permalink(get_option('woocommerce_myaccount_page_id'));
        $data['keyVersion'] = get_option('mercanet_version_key');
        $data['merchantId'] = get_option('mercanet_merchant_id');
        $data['requestDateTime'] = gmdate("Y-m-d", time()) . "T" . gmdate("H:i:s", time()) . "+00:00";
        $data['merchantWalletId'] = self::get_wallet_by_user($user_id);
        return $data;
    }

}

new Mercanet_Wallet();
