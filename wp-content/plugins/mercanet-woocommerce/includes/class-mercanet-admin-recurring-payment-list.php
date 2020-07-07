<?php

class Mercanet_Admin_Recurring_Payment_List {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public function __construct() {
        add_action('woocommerce_settings_tabs_settings_mercanet_recurring_payment_list', array($this, 'settings_recurring_payment_list_tab'));
        add_action('wp_ajax_get_list_customer_autocomp', array($this, 'get_list_customer_autocomp'), 50);
        add_action('wp_ajax_get_content_recurring_payment_list', array($this, 'get_content_recurring_payment_list_ajax'), 50);
        add_action('wp_ajax_update_recurring_payment_status', array($this, 'update_recurring_payment_status'), 50);
    }

    /**
     * Get config of tab
     *
     * @return array
     */
    public function get_setting_payment_list_tab() {
        
        $settings = array(
            'mercanet_general' => array(
                'title' => __('Mercanet recurring payment list', 'mercanet'),
                'type' => 'title',
                'id' => 'mercanet_general_title'
            ),
            'section_end' => array(
                'type' => 'sectionend',
            )
        );
        return apply_filters('wc_settings_mercanet_recurring_payment_list', $settings);
    }

    /**
     * Get user list for autocomplete
     * 
     * @global type $wpdb
     */
    public function get_list_customer_autocomp() {
        global $wpdb;

        $customer_ids = $wpdb->get_col(<<<SQL
            SELECT DISTINCT user_id FROM $wpdb->usermeta 
            WHERE  (meta_key = 'last_name' AND meta_value LIKE "{$_POST['term']}%")
                OR (meta_key = 'first_name' AND meta_value LIKE "{$_POST['term']}%")
SQL
        );
        $list_autocomp = [];
        foreach ($customer_ids as $customer_id) {
            $customer = new WP_User($customer_id);
            $list_autocomp[] = array(
                "id" => $customer_id,
                "firstname" => $customer->user_firstname,
                "lastname" => $customer->user_lastname
            );
        }
        echo json_encode($list_autocomp);
        wp_die();
    }

    /**
     * Show reccuring payment list table
     *
     * @return void
     */
    public function settings_recurring_payment_list_tab() {
        global $hide_save_button;
        $hide_save_button = true;
        woocommerce_admin_fields($this->get_setting_payment_list_tab());

        $plugin_base_name = explode('/', plugin_basename(__FILE__))[0];
        wp_register_script('mercanet-back-recurring-payment-list', plugins_url("$plugin_base_name/assets/js/back-recurring-payment-list.js"));
        wp_enqueue_script('mercanet-back-recurring-payment-list');

        $fn = function ($fn) {
            return $fn;
        };
        $styles = $this->get_content_styles_table();
        $filter = $this->get_content_filter_table();
        $tbody = $this->get_content_recurring_payment_list(Mercanet_Recurring_Payment::get_recucrring_payment_list_with_filter());
        echo <<<HTML
            $styles
            <table class="wp-list-table widefat fixed striped posts sortable" id="recurring-payment-list">
                <thead>
                    $filter
                    <tr>
                        <td class="manage-column column-sku sortable desc">{$fn(__('Customer', 'mercanet'))}</td>
                        <td colspan=2>{$fn(__('Product', 'mercanet'))}</td>
                        <td>{$fn(__('Type', 'mercanet'))}</td>
                        <td>{$fn(__('Subscription price', 'mercanet'))}</td>
                        <td>{$fn(__('Periodicity', 'mercanet'))}</td>
                        <td colspan=2>{$fn(__('Order date', 'mercanet'))}</td>
                        <td>{$fn(__('Nb valid payment', 'mercanet'))}</td>
                        <td>{$fn(__('Last payment', 'mercanet'))}</td>
                        <td>{$fn(__('Status', 'mercanet'))}</td>
                        <td>{$fn(__('Actions', 'mercanet'))}</td>
                    </tr>
                </thead>
                <tbody id="recurring-payment-list-content">
                    $tbody
                </tbody>
            </table>
HTML;
    }

    /**
     * Get DOM CSS
     * 
     * @return string
     */
    public function get_content_styles_table() {
        $style = <<<CSS
        <style>
            #filter-lign {width: 100%; table-layout: fixed;}
            #filter-lign td input[type=text] {max-width: 90%; padding: 0 2px; margin: 0; line-height: 28px;}
            #filter-lign td #filter_date_add, 
            #filter-lign td #filter_date_add_end {width: 45%;} 
            #recurring-payment-list .actions-lign .dashicons {padding:0 5px; font-size:2em;}
            #recurring-payment-list .actions-lign .dashicons:hover { cursor: pointer; }
            #recurring-payment-list .actions-lign .dashicons.dashicons-yes {color: #1bb11b;}
            #recurring-payment-list .actions-lign .dashicons.dashicons-no-alt {color: #d42424;}
            #recurring-payment-list-content .loading {text-align: center;}
            #recurring-payment-list-content .loading  img{height: 30px; padding: 10px;}
        </style>
CSS;
        return $style;
    }

    /**
     * Get DOM filter row
     * 
     * @return string
     */
    public function get_content_filter_table() {
        $fn = function ($fn) {
            return $fn;
        };
        $recurring_products = Mercanet_Recurring_Payment::get_recurring_products();
        $options_product = "<option value='-1'> {$fn(__('Product', 'mercanet'))} ... </option>";
        foreach ($recurring_products as $recurring_product) {
            $product = wc_get_product($recurring_product->id_product);
            $options_product .= "<option value='{$recurring_product->id_product}'>{$product->get_title()}</option>";
        }

        $html = <<<HTML
            <tr id="filter-lign">         
                <td>
                    <input type="text" id="filter_id_customer" class="filter-recurring-payment"  placeholder="{$fn(__('Customer', 'mercanet'))} ...">
                    <input type="hidden" id="filter_id_customer_key" /> 
                </td>
                <td colspan=2><select id="filter_id_product" class="filter-recurring-payment" >$options_product</select></td>
                <td></td>
                <td></td>
                <td>
                    <select id="filter_periodicity" class="filter-recurring-payment" >
                        <option value="-1">{$fn(__('Periodicity', 'mercanet'))} ...</option>
                        <option value="D">{$fn(__('Day', 'mercanet'))}</option>
                        <option value="M">{$fn(__('Month', 'mercanet'))}</option>
                    </select>
                </td>
                <td colspan=2>
                    <input type="text" id="filter_date_add" class="filter-recurring-payment"  placeholder="{$fn(__('Beginning', 'mercanet'))} ...">
                    <input type="text" id="filter_date_add_end" class="filter-recurring-payment"  placeholder="{$fn(__('End', 'mercanet'))}  ...">
                </td>
                <td></td>
                <td></td>
                <td>
                    <select id="filter_status" class="filter-recurring-payment" >
                        <option value="-1">{$fn(__('Status', 'mercanet'))} ...</option>
                        <option value="1">{$fn(__('In progress', 'mercanet'))}</option>
                        <option value="2">{$fn(__('Problem', 'mercanet'))}</option>
                        <option value="3">{$fn(__('Canceled', 'mercanet'))}</option>
                    </select>
                </td>
                <td>
                    <input type="submit" class="button-primary woocommerce-save-button" value="Remise filtre à zéro" id="reset-filter" />
                </td>
            </tr>
HTML;
        return $html;
    }

    /**
     * Get DOM of recurring payment list
     * 
     * @return string
     */
    public function get_content_recurring_payment_list($list) {
        $fn = function ($fn) {
            return $fn;
        };
        $tbody = (sizeof($list) == 0) ? "<tr><td colspan=12>Aucun abonnement à afficher</td></tr>" : "";
        foreach ($list as $recurring_payment) {
            $status = "";
            switch ($recurring_payment->status) {
                case "1" :
                    $status = "<mark class='order-status status-processing'><span>{$fn(__('In progress', 'mercanet'))}</span></mark>";
                    break;
                case "2" :
                    $status = "<mark class='order-status status-failed'><span>{$fn(__('Problem', 'mercanet'))}</span></mark>";
                    break;
                case "3" :
                    $status = "<mark class='order-status status-pending'><span>{$fn(__('Canceled', 'mercanet'))}</span></mark>";
                    break;
            }

            $customer = get_user_by('ID', $recurring_payment->id_customer);
            $user_link = "<a href='" . get_edit_user_link($recurring_payment->id_customer) . "' target='_blank'>{$customer->user_firstname} {$customer->user_lastname}</a>";
            $product = wc_get_product($recurring_payment->id_product);
            $product_link = "<a href='" . get_edit_post_link($recurring_payment->id_product) . "' target='_blank'>{$product->get_title()}</a>";
            $currency = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $periodicity = ($recurring_payment->periodicity == "D") ? __('Day', 'mercanet') : __('Month', 'mercanet');
            $date_add = new DateTime($recurring_payment->date_add);
            $last_schedule = new DateTime($recurring_payment->last_schedule);
            $transaction = Mercanet_Transaction::get_by_id($recurring_payment->id_mercanet_transaction);            
            $type = ($transaction->payment_mean_brand === "SEPA_DIRECT_DEBIT") ? "SDD" : "Abonnement";
            
            $tbody .= <<<HTML
                <tr name="{$recurring_payment->id_mercanet_customer_payment_recurring}">
                    <td>$user_link</td>
                    <td colspan=2>$product_link</td>
                    <td>$type</td>
                    <td>{$recurring_payment->current_specific_price} $currency</td>
                    <td>{$recurring_payment->number_occurences} $periodicity</td>
                    <td colspan=2>{$date_add->format('d/m/Y')}</td>
                    <td>{$recurring_payment->current_occurence}</td>
                    <td>{$last_schedule->format('d/m/Y')}</td>
                    <td class="status-lign">$status</td>
                    <td class="actions-lign">
                        <span class="dashicons dashicons-yes" name="enable" title="{$fn(__('Enable', 'mercanet'))}"></span>
                        <span class="dashicons dashicons-no-alt" name="disable" title="{$fn(__('Disable', 'mercanet'))}"></span>
                        <span class="dashicons dashicons-trash" name="remove" title="{$fn(__('Remove', 'mercanet'))}"></span>
                    </td>
                </tr>
                        
HTML;
        }

        return $tbody;
    }

    public function get_content_recurring_payment_list_ajax() {

        unset($_POST['action']);
        $filters = array();
        foreach ($_POST['filters'] as $key => $value) {
            $field = substr($key, 7);
            switch ($field) {
                case "date_add" :
                    $date = date('Y-m-d', strtotime(str_replace('-', '/', $value)));
                    $filters[] = array(
                        "field" => $field,
                        "operator" => " >= ",
                        "value" => '"' . $date . ' 00:00:00"'
                    );
                    break;
                case "date_add_end" :
                    $date = date('Y-m-d', strtotime(str_replace('-', '/', $value)));
                    $filters[] = array(
                        "field" => "date_add",
                        "operator" => " <= ",
                        "value" => '"' . $date . ' 23:59:59"'
                    );
                    break;
                default :
                    $filters[] = array(
                        "field" => $field,
                        "operator" => "=",
                        "value" => '"' . $value . '"'
                    );
                    break;
            }
        }

        $recurring_payment_list = Mercanet_Recurring_Payment::get_recucrring_payment_list_with_filter($filters);
        $content = $this->get_content_recurring_payment_list($recurring_payment_list);

        echo json_encode(array(
            "html" => $content
        ));
        wp_die();
    }

    /**
     * Update status of recurring payment
     * 
     * @global type $wpdb
     * @return void
     */
    public function update_recurring_payment_status() {
        global $wpdb;
        $datas = $_POST;
        switch ($datas['action_libelle']) {
            case "enable" :
                $result = $wpdb->update(
                        $wpdb->prefix . "mercanet_customer_payment_recurring", array(
                    'status' => 1
                        ), array(
                    'id_mercanet_customer_payment_recurring' => $_POST['recurring_payment_id']
                        )
                );
                $status = '<mark class="order-status status-processing"><span>' . __('In progress', 'mercanet') . '</span></mark>';
                break;
            case "disable" :
                $result = $wpdb->update(
                        $wpdb->prefix . "mercanet_customer_payment_recurring", array(
                    'status' => 3
                        ), array(
                    'id_mercanet_customer_payment_recurring' => $_POST['recurring_payment_id']
                        )
                );
                $status = '<mark class="order-status status-pending"><span>' . __('Canceled', 'mercanet') . '</span></mark>';
                break;
            case "remove" :
                $result = $wpdb->delete("{$wpdb->prefix}mercanet_customer_payment_recurring", array("id_mercanet_customer_payment_recurring" => $_POST['recurring_payment_id']));
                $status = "";
                break;
        }

        echo json_encode(array(
            "result" => $result,
            "html" => $status
        ));

        wp_die();
    }

}

new Mercanet_Admin_Recurring_Payment_List();
