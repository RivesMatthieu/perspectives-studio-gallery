<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://woocommerce.db-dzine.de
 * @since      1.0.0
 *
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/includes
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WooCommerce_Buying_Guide {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WooCommerce_Buying_Guide_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function __construct($version) {

		$this->plugin_name = 'woocommerce-buying-guide';
		$this->version = $version;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WooCommerce_Buying_Guide_Loader. Orchestrates the hooks of the plugin.
	 * - WooCommerce_Buying_Guide_i18n. Defines internationalization functionality.
	 * - WooCommerce_Buying_Guide_Admin. Defines all hooks for the admin area.
	 * - WooCommerce_Buying_Guide_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-buying-guide-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-buying-guide-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-buying-guide-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-buying-guide-post-type.php';

        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/meta-boxes.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/meta-boxes.php';
        }

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-buying-guide-public.php';

		$this->loader = new WooCommerce_Buying_Guide_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WooCommerce_Buying_Guide_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$this->plugin_i18n = new WooCommerce_Buying_Guide_i18n();

		$this->loader->add_action( 'plugins_loaded', $this->plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->plugin_admin = new WooCommerce_Buying_Guide_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles', 999);
		$this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts', 999);

		$this->loader->add_action('plugins_loaded', $this->plugin_admin, 'load_redux' );
		$this->loader->add_action('init', $this->plugin_admin, 'init' );

		$this->buying_guide_post_type = new WooCommerce_Buying_Guide_Post_Type( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('init', $this->buying_guide_post_type, 'init', 70);
        $this->loader->add_action('add_meta_boxes', $this->buying_guide_post_type, 'add_custom_metaboxes', 10, 2);
        $this->loader->add_action('save_post', $this->buying_guide_post_type, 'save_custom_metaboxes', 1, 2);
        $this->loader->add_action('admin_footer', $this->buying_guide_post_type, 'get_buying_guide_data');
        $this->loader->add_action('wp_ajax_woocommerce_buying_guide_get_item', $this->buying_guide_post_type, 'get_item');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->plugin_public = new WooCommerce_Buying_Guide_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp', $this->plugin_public, 'init' );
		$this->loader->add_action( 'woocommerce_product_query', $this->plugin_public, 'check_filtered_products', 90, 2 );

		add_shortcode('woocommerce_buying_guide', array($this->plugin_public, 'shortcode'));
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WooCommerce_Buying_Guide_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
