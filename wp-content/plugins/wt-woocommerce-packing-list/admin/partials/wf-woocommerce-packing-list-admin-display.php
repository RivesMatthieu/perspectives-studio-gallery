<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      4.0.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/admin/partials
 */

$wf_admin_view_path=plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME).'admin/views/';
$wf_img_path=WF_PKLIST_PLUGIN_URL . 'images/';
$wfpk_activation_status=get_option(WF_PKLIST_ACTIVATION_ID.'_activation_status');
?>
<div class="wrap">
    <h2 class="wp-heading-inline">
	<?php _e('Settings','wf-woocommerce-packing-list');?>: 
	<?php _e('WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels','wf-woocommerce-packing-list');?>
	</h2>
	<div class="nav-tab-wrapper wp-clearfix wf-tab-head">
		<?php
	    if($wfpk_activation_status)
	    {
	        $activate_icon='<span class="dashicons dashicons-yes" style="color:#03da01; font-size:25px;"></span>';   
	    }else
	    {
	        $activate_icon='<span class="dashicons dashicons-warning" style="color:#ff1515; font-size:25px;"></span>';
	    }
	    $tab_head_arr=array(
            'wf-documents'=>__('Documents','wf-woocommerce-packing-list'),
	        'wf-general'=>__('General','wf-woocommerce-packing-list'),            
	        'wf-advanced'=>__('Advanced','wf-woocommerce-packing-list'),
	        'wf-licence'=>array(__('Licence','wf-woocommerce-packing-list'),$activate_icon),
	        'wf-help'=>__('Help Guide','wf-woocommerce-packing-list')
	    );
	    if(isset($_GET['debug']))
	    {
	        $tab_head_arr['wf-debug']='Debug';
	    }
	    Wf_Woocommerce_Packing_List::generate_settings_tabhead($tab_head_arr);
	    ?>
	</div>
	<div class="wf-tab-container">
        <?php
        //inside the settings form
        $setting_views_a=array(
            'wf-general'=>'admin-settings-general.php',                                     
            'wf-advanced'=>'admin-settings-advanced.php',          
        );

        //outside the settings form
        $setting_views_b=array(   
            'wf-documents'=>'admin-settings-documents.php',                 
            'wf-licence'=>'admin-settings-licence.php',           
            'wf-help'=>'admin-settings-help.php',           
        );
        if(isset($_GET['debug']))
        {
            $setting_views_b['wf-debug']='admin-settings-debug.php';
        }
        ?>
        <form method="post" class="wf_settings_form">
            <input type="hidden" value="main" class="wf_settings_base" />
            <?php
            
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=$wf_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }

            //settings form fields for module
            do_action('wf_pklist_plugin_settings_form');
            ?>           
        </form>
        <?php
        foreach ($setting_views_b as $target_id=>$value) 
        {
            $settings_view=$wf_admin_view_path.$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php do_action('wf_pklist_plugin_out_settings_form');?> 
    </div>
</div>