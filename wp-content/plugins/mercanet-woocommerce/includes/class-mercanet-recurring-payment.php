<?php

class Mercanet_Recurring_Payment {

    private $translated = array();
    private $abo_enabled = false;

    public function __construct() {
        add_action('woocommerce_before_my_account', array($this, 'init_translate'), 10, 0);
        add_action('woocommerce_before_my_account', array($this, 'stop_recurring_payment'), 10, 0);
        add_action('woocommerce_before_my_account', array($this, 'output_recurring_payment_list'), 10, 0);
        add_action('template_redirect', array($this, 'change_payment_card'), 10, 0);
        add_action('woocommerce_review_order_before_cart_contents', array($this, 'change_payment_card'), 10, 0);

        $check_enabled = get_option('woocommerce_mercanet_recurring_settings', null);
        if (!empty($check_enabled) && $check_enabled['enabled'] == "yes") {
            $this->abo_enabled = true;
        }

        if (Mercanet_Api::is_allowed(array('ABO')) && $this->abo_enabled) {

            if (!wp_next_scheduled('mercanet_recurring_cronjob')) {
                wp_schedule_event(time(), "daily", "mercanet_recurring_cronjob");
            }

            add_action('mercanet_recurring_cronjob', 'send_reccurring_schedule');

            function send_reccurring_schedule() {
                Mercanet_Webservice::send_recurring_schedules();
            }

        }
    }

    public function init_translate() {
        $this->translated = array(
            'title' => __('My recurring payments', 'mercanet'),
            'order' => __('Order', 'mercanet'),
            'product' => __('Product', 'mercanet'),
            'next_payment' => __('Next payment', 'mercanet'),
            'amount' => __('Amount', 'mercanet'),
            'state' => __('State', 'mercanet'),
            'change_card' => __('Change payment card', 'mercanet'),
            'stop_all' => __('Stop all my recurring payments', 'mercanet'),
            'stop_all_confirm' => __('Your recurring payments have all been stopped', 'mercanet'),
            'stop_all_confirm_question' => __('Are you sure you want to stop all your recurring payments ?', 'mercanet'),
            'stop_all_confirm_yes' => __('Yes', 'mercanet'),
            'stop_all_confirm_no' => __('No', 'mercanet'),
            'state_enabled' => __('Enabled', 'mercanet'),
            'state_problem' => __('Problem', 'mercanet'),
            'state_disabled' => __('Disabled', 'mercanet')
        );
    }

    /**
     * Generate the list of reccuring payment to the current user
     */
    public function output_recurring_payment_list() {
        if (Mercanet_Api::is_allowed(array('ABO')) && $this->abo_enabled) {
            global $woocommerce;
            $user_id = get_current_user_id();
            if (empty($user_id)) {
                return false;
            }
            $payments = self::get_recucrring_payment_list($user_id);
            if (!empty($payments)) {
                $html_payments = "";
                foreach ($payments as $key => $payment) {
                    $order = new WC_Order($payment->id_order);
                    $product = new WC_Product($payment->id_product);
                    $date_order = date_i18n(get_option('date_format'), strtotime($payment->next_schedule));
                    $state = "";
                    switch ($payment->status) {
                        case '1': $state = $this->translated['state_enabled'];
                            break;
                        case '2': $state = $this->translated['state_problem'];
                            break;
                        case '3': $state = $this->translated['state_disabled'];
                            break;
                    }
                    $html_payments .= <<<HTML
                    <tr>
                        <td><a href='{$order->get_view_order_url()}'>#{$payment->id_order}</a></td>
                        <td>{$product->get_formatted_name()}</td>
                        <td>$date_order</td>
                        <td>{$payment->current_specific_price} €</td>  
                        <td>$state</td>
                        <td>
                            <form action="{$woocommerce->cart->get_checkout_url()}" method="post" >
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="change-payment-cart" value="1">
                                <input type="hidden" name="order-id" value="{$payment->id_order}">
                                <input type="hidden" name="product-to-add" value="{$payment->id_product}">
                                <input type="hidden" name="product-to-add-pos" value="{$payment->id_cart_quantity}">
                                <button type="submit" class="button alt">
                                    {$this->translated['change_card']} 
                                </button>
                            </form>
                        </td>
                    </tr>
HTML;
                }

                $plugin_base_name = explode('/', plugin_basename(__FILE__))[0];
                wp_enqueue_script('mercanet-back', plugins_url("$plugin_base_name/assets/js/front.js"));
                wp_enqueue_style('mercanet-back', plugins_url("$plugin_base_name/assets/css/front.css"));
                echo <<<HTML
                <h2>{$this->translated['title']}</h2>
                <form id="mercanet_stop_recurring_form" method="POST" action=""> 
                    <div class="quantity">
                        <input type="hidden" name="quantity" value="1" class="input-text qty text"">
                    </div>
                    <input type="hidden" name="mercanet_stop_recurring" value="$user_id"> 
                    <button type="submit" value="submit" class="button" form="mercanet_stop_recurring_form" id="stop_recurring_button">
                        <span>{$this->translated['stop_all']}</span>
                    </button> 
                </form>
                <div class='mercanet-overlay'></div>
                <div class="stop_recurring_confirmation" style="display: none;">
                    <p class="info-title">{$this->translated['stop_all_confirm_question']}</p>
                    <div class="text-center">
                        <button id="confirm_stop_recurring" type="submit" class="btn btn-default button button-small">
                            <span>{$this->translated['stop_all_confirm_yes']}</span>
                        </button>
                        <button id="noconfirm_stop_recurring" type="submit" class="btn btn-default button button-small">
                            <span>{$this->translated['stop_all_confirm_no']}</span>
                        </button>
                    </div>
                </div>
                <table id="order-list" class="shop_table shop_table_responsive my_account_orders"> 
                    <thead>
                        <tr>
                            <th class='item'>{$this->translated['order']}</th>
                            <th class='item'>{$this->translated['product']}</th>
                            <th class='item'>{$this->translated['next_payment']}</th>
                            <th class='item'>{$this->translated['amount']}</th>
                            <th class='item'>{$this->translated['state']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        $html_payments
                    </tbody>
                </table>
HTML;
            }
        }
    }

    public function stop_recurring_payment() {
        if (Mercanet_Api::is_allowed(array('ABO')) && $this->abo_enabled && isset($_POST['mercanet_stop_recurring'])) {
            global $wpdb;
            $wpdb->update($wpdb->prefix . 'mercanet_customer_payment_recurring', array(
                'status' => '3'
                    ), array(
                'id_customer' => $_POST['mercanet_stop_recurring']
                    )
            );

            if ($wpdb->last_error !== '') {
                $wpdb->print_error();
            } else {
                echo "<h4>{$this->translated['stop_all_confirm']}</h4>";
            }
        }
    }

    public static function add_to_card_change_card($product_to_add, $rf_order_id) {
        $recurring_infos_product = self::get_recurring_infos($product_to_add);
        $recurring_infos_order = self::get_mercanet_customer_payment_recurring(array("id_order" => $rf_order_id));
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($product_to_add, 1, 0, array(), array('rf_order_id' => $rf_order_id, 'product_to_add' => $product_to_add, 'change_payment_card' => true));
        if (!($recurring_infos_order[0]->status != "1" && $recurring_infos_order[0]->current_occurence == "0")) {
            foreach (WC()->cart->cart_contents as $item) {
                $item['data']->set_price($recurring_infos_product[0]->recurring_amount);
            }
        }
        WC()->cart->calculate_totals();
    }

    public function change_payment_card() {
        $ajax_call = false;
        foreach (WC()->cart->cart_contents as $item) {
            if (!empty($item['rf_order_id'])) {
                $ajax_call = true;
                $rf_order_id = $item['rf_order_id'];
                $product_to_add = $item['product_to_add'];
            }
        }
        if (Mercanet_Api::is_allowed(array('ABO')) && $this->abo_enabled && isset($_REQUEST['change-payment-cart']) && $_REQUEST['change-payment-cart'] == "1") {
            self::add_to_card_change_card($_REQUEST['product-to-add'], $_REQUEST['order-id']);
        } else if ($ajax_call) {
            self::add_to_card_change_card($product_to_add, $rf_order_id);
        }
    }

    public static function get_recucrring_payment_list($user_id) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_customer_payment_recurring WHERE `id_customer` = '$user_id' ORDER BY date_add DESC");
    }
    
    public static function get_recucrring_payment_list_with_filter($filters = array()) {        
        global $wpdb;
        $condition = "WHERE 1=1 ";
        foreach($filters as $filter) {
            $condition .= "AND " . $filter['field'] . " " . $filter['operator'] . " "  . $filter['value'] . " ";
        }
        
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_customer_payment_recurring $condition ORDER BY id_customer, date_add DESC");
    }
    
    public static function get_recurring_products() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_payment_recurring ORDER BY id_mercanet_payment_recurring DESC");
    }

    public static function get_schedules_to_capture() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_customer_payment_recurring WHERE `status` = '1' AND DATEDIFF(NOW(), `next_schedule`) >= 0 ORDER BY date_add DESC");
    }

    public static function get_recurring_infos($product_id, $is_sdd = false) {
        global $wpdb;
        return ($is_sdd) ? 
            $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_payment_recurring WHERE id_product = '$product_id' AND type IN ('3','4') ") : 
            $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mercanet_payment_recurring WHERE id_product = '$product_id' AND type IN ('1','2')");
    }

    public static function get_mercanet_customer_payment_recurring($field) {
        global $wpdb;
        $sql = <<<SQL
        SELECT * FROM {$wpdb->prefix}mercanet_customer_payment_recurring 
        WHERE 1 
SQL;

        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $sql .= " AND $key = '$value' ";
            }
        } else {
            $sql .= " AND id_mercanet_customer_payment_recurring = '$field' ";
        }
        $result = $wpdb->get_results($sql);
        return $result;
    }

}

new Mercanet_Recurring_Payment();
