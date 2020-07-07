<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.webtoffee.com/
 * @since      4.0.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      4.0.0
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Wf_Woocommerce_Packing_List_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    4.0.0
	 * @since    4.0.5 	Temp file clearing cron deactivation functionality	added
	 */
	public static function deactivate()
	{
		if(wp_next_scheduled('wt_pklist_auto_clear_temp_files')) //its already scheduled then remove
		{
			wp_clear_scheduled_hook('wt_pklist_auto_clear_temp_files');
		}
	}

}
