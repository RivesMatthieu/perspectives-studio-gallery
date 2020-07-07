<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://woocommerce.db-dzine.de
 * @since      1.0.0
 *
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/admin
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WooCommerce_Buying_Guide_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Enqueue Admin Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_styles()
    {
    	wp_enqueue_style('jquery-nestable', plugin_dir_url(__FILE__).'css/jquery-nestable.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'css/woocommerce-buying-guide-admin.css', array(), $this->version, 'all');
    }

    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery-nestable', plugin_dir_url(__FILE__).'js/jquery-nestable.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'js/woocommerce-buying-guide-admin.js', array('jquery'), $this->version, true);
    }

	/**
	 * Gets options
	 *
	 * @since    1.0.0
	 */
    private function get_option($option)
    {
    	if(!is_array($this->options)) {
    		return false;
    	}
    	if(!array_key_exists($option, $this->options))
    	{
    		return false;
    	}
    	return $this->options[$option];
    }

	public function load_redux()
	{
        if(!is_admin() || !current_user_can('administrator') || (defined('DOING_AJAX') && DOING_AJAX && (isset($_POST['action']) && !$_POST['action'] == "woocommerce_buying_guide_options_ajax_save") )) {
            return false;
        }

        // Load the theme/plugin options
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/options-init.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/options-init.php';
        }
        return true;
	}

	public function init()
	{
        global $woocommerce_buying_guide_options;

        if(!is_admin() || !current_user_can('administrator') || (defined('DOING_AJAX') && DOING_AJAX)){
            $woocommerce_buying_guide_options = get_option('woocommerce_buying_guide_options');
        }

        $this->options = $woocommerce_buying_guide_options;

		$this->flush_meta_box_order();

		$removePagination = $this->get_option('removePagination');
		if($removePagination) {
			add_filter( 'loop_shop_per_page', array($this, 'removePagination' ), 20 );
		}

		add_action( 'woocommerce_product_query', array($this, 'remove_buying_guides_from_query') );

    }

	public function removePagination()
	{
		return '-1';
	}

	public function remove_buying_guides_from_query( $q )
	{
	    $q->set( 'post_type', 'product' );
	}

	private function flush_meta_box_order() 
	{
		global $wpdb;

		$query = $wpdb->prepare("
			DELETE
			FROM  $wpdb->usermeta
			WHERE meta_key LIKE %s
			", '%_buying-guide%' );

		$wpdb->query( $query );
	}
}