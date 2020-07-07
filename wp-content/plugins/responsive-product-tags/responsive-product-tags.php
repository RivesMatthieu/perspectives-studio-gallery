<?php

/**
 * Plugin Name: Responsive Product Image Tags - WooCommerce Plugin
 * Plugin URI: http://codecanyon.net/item/responsive-product-image-tags-woocommerce-plugin/11016995?ref=WPShowCase
 * Description: Creates Responsive Product Image Tags for your WooCommerce Products
 * Author: WPShowCase
 * Version: 1.7.3
 * Author URI: http://codecanyon.net/user/wpshowcase?ref=WPShowCase
 * WC tested up to: 3.3.2
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once dirname( __FILE__ ) . '/includes/functions.php';
require_once dirname( __FILE__ ) . '/includes/settings.php';
require_once dirname( __FILE__ ) . '/includes/frontend.php';
require_once dirname( __FILE__ ) . '/includes/admin.php';
require_once dirname( __FILE__ ) . '/plugins/plugins.php';

class Responsive_Product_Tags {

    /**
     * Constructor - adds actions and filters
     */
    function __construct() {
        //plugins page
        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
        //settings page
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        //mo/po files
        load_plugin_textdomain( 'responsive-product-tags', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Adds a link to the settings on the plugin page
     */
    function settings_link( $links ) {
        $settings = '<a href="' . admin_url( 'admin.php?page=responsive-product-tags-settings' ) . '">' . __( 'Responsive Product Image Tag Settings', 'woocommerce-import-lang' ) . '</a>';
        array_unshift( $links, $settings );
        return $links;
    }

    /**
     * Adds an settings page to the WooCommerce menu
     */
    function admin_menu() {
        global $responsive_product_tags_settings;
        add_submenu_page( 'woocommerce', __( 'Responsive Product Image Tag Settings', 'woocommerce-import-lang' ), __( 'Responsive Product Image Tag Settings', 'woocommerce-import-lang' ), 'manage_woocommerce', 'responsive-product-tags-settings', array( $responsive_product_tags_settings, 'settings_page' )
        );
    }

}

$responsive_product_tags = new Responsive_Product_Tags();
