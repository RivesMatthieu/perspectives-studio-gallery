<?php

/**
 * Plugin Name: BNP Paribas - Mercanet
 * Version: 1.0.21
 * Author: Quadra Informatique
 * Author URI: http://shop.quadra-informatique.fr
 * Description: BNP Paribas Mercanet essential POST WooCommerce
 * WC requires at least: 2.4
 * WC tested up to: 3.3
 */

/**
 * Mercanet Class
 */
class Mercanet {

    public function __construct() {
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-api.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-logger.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-transaction.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-schedule.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-refund.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-admin-general.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-admin-credentials.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-admin-transactions.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-admin-recurring-payment-list.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-wallet.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-webservice.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-payment.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-mercanet-recurring-payment.php';
        include_once plugin_dir_path(__FILE__) . 'gateways/class-mercanet-gateway-onetime.php';
        include_once plugin_dir_path(__FILE__) . 'gateways/class-mercanet-gateway-nx.php';
        include_once plugin_dir_path(__FILE__) . 'gateways/class-mercanet-gateway-recurrent.php';
        include_once plugin_dir_path(__FILE__) . 'gateways/class-mercanet-gateway-sdd.php';

        include_once plugin_dir_path(__FILE__) . 'mercanet-install.php';
        register_activation_hook(__FILE__, array('Mercanet_Install', 'install'));
        register_deactivation_hook(__FILE__, array('Mercanet_Install', 'deactivation'));

        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('admin_notices', array($this, 'notice'));
        
        load_theme_textdomain('mercanet', plugin_dir_path(__FILE__) . '/languages');
    }

    public function add_meta_boxes() {
        add_meta_box('woocommerce-order-transactions', __('Transactions history', 'mercanet'), 'Mercanet_Transaction::output', 'shop_order', 'normal', 'low');
        add_meta_box('woocommerce-order-schedules', __('Transactions schedule', 'mercanet'), 'Mercanet_Schedule::output', 'shop_order', 'normal', 'low');
        add_meta_box('woocommerce-mercanet-recurrent-payment', __('Recurring Payment', 'mercanet'), 'Mercanet_Gateway_Recurring::add_form_mercenat_recurring_payment_admin', 'product', 'side', 'low');
        
        //if(Mercanet_Gateway_Sdd::mercanet_sdd_recurrent_is_allow()) {
        //    add_meta_box('woocommerce-mercanet-sdd-payment', __('Recurring SDD Payment', 'mercanet'), 'Mercanet_Gateway_Sdd::add_form_mercenat_sdd_payment_admin', 'product', 'side', 'low');
        //}
    }

    public function notice() {
        if (get_option('mercanet_activation_key') == '') {
            echo '<div class="update-nag"><a href = "admin.php?page=wc-settings&tab=settings_mercanet_credentials">' . __('You have to register your activation key to use this module.', 'mercanet') . '</a></div>';
        }
        if (extension_loaded('curl') == false) {
            echo '<div class="update-nag">' . __('You have to enable the cURL extension on your server to use this module.', 'mercanet') . '</div>';
        }
        if (extension_loaded('openssl') == false) {
            echo '<div class="update-nag">' . __('You have to enable the OpenSSL extension on your server to use this module.', 'mercanet') . '</div>';
        }
    }
}
new Mercanet();
