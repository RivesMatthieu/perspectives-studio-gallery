<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              http://woocommerce.db-dzine.de
 * @since             1.0.0
 * @package           WooCommerce_Buying_Guide
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Buying Guide
 * Plugin URI:        https://welaunch.io/plugins/woocommerce-buying-guide/
 * Description:       Create an interative buying guide to help your customers.
 * Version:           1.2.0
 * Author:            weLaunch
 * Author URI:        https://welaunch.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-buying-guide
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-buying-guide-activator.php
 */
function activate_WooCommerce_Buying_Guide() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-buying-guide-activator.php';
	WooCommerce_Buying_Guide_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-buying-guide-deactivator.php
 */
function deactivate_WooCommerce_Buying_Guide() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-buying-guide-deactivator.php';
	WooCommerce_Buying_Guide_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WooCommerce_Buying_Guide' );
register_deactivation_hook( __FILE__, 'deactivate_WooCommerce_Buying_Guide' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-buying-guide.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_WooCommerce_Buying_Guide() {

	$plugin_data = get_plugin_data( __FILE__ );
	$version = $plugin_data['Version'];

	$plugin = new WooCommerce_Buying_Guide($version);
	$plugin->run();

	return $plugin;

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php') && (is_plugin_active('redux-framework/redux-framework.php') || is_plugin_active('redux-dev-master/redux-framework.php')) && is_plugin_active('meta-box/meta-box.php') ){
	$WooCommerce_Buying_Guide = run_WooCommerce_Buying_Guide();
} else {
	add_action( 'admin_notices', 'WooCommerce_Buying_Guide_Not_Installed' );
}

function WooCommerce_Buying_Guide_Not_Installed()
{
	?>
    <div class="error">
      <p><?php _e( 'WooCommerce Buying Guide requires the WooCommerce, Meta Boxes and Redux Framework plugin. Please install or activate them!', 'woocommerce-buying-guide'); ?></p>
    </div>
    <?php
}