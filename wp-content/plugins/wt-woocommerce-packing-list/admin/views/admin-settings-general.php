<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">

	<p><?php _e('The company and shipping from address details from the sections below will be used to fill up the invoice and related documents accordingly.','wf-woocommerce-packing-list'); ?></p>
	<h3 style="border-bottom:dashed 1px #ccc; padding-bottom:5px;"><?php _e('Company Info', 'wf-woocommerce-packing-list'); ?></h3>
	<table class="wf-form-table">
	    <?php
		self::generate_form_field(array(
			array(
			'label'=>__("Name",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_companyname",
			),
			array(
				'type'=>"uploader",
				'label'=>__("Logo",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_logo",
			),
			array(
				'type'=>'textarea',
				'label'=>__("Return policy/conditions",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_return_policy",
			),
			array(
				'type'=>'textarea',
				'label'=>__("Footer",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_footer",
				'help_text'=>__("Set up a footer which will be used across the respective documents.",'wf-woocommerce-packing-list'),
			),
		));
		?>
	</table>

	<h3 style="border-bottom:dashed 1px #ccc; padding-bottom:5px;">
		<?php _e('Address(Sender details)', 'wf-woocommerce-packing-list'); ?> <?php echo Wf_Woocommerce_Packing_List_Admin::set_tooltip('address_details'); ?>
		<?php $tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('load_default_address'); ?>
		<a class="wf_pklist_load_address_from_woo <?php echo $tooltip_conf['class'];?>" <?php echo $tooltip_conf['text'];?>>
			<span class="dashicons dashicons-admin-page"></span>
			<?php _e('Load from WooCommerce','wf-woocommerce-packing-list');?>
		</a>		
	</h3>
	<table class="wf-form-table">
		<?php
		self::generate_form_field(array(
			array(
				'label'=>__("Department/Business Unit/Sender",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sender_name",
			),
			array(
				'label'=>__("Address line 1",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sender_address_line1",
			),
			array(
				'label'=>__("Address line 2",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sender_address_line2",
			),
			array(
				'label'=>__("City",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sender_city",
			)
		));
        $country_selected=Wf_Woocommerce_Packing_List::get_option('wf_country');
        if( strstr( $country_selected, ':' ))
        {
			$country_selected = explode( ':', $country_selected );
			$country         = current( $country_selected );
			$state           = end( $country_selected );                                            
		}else 
		{
			$country = $country_selected;
			$state   = '*';
		}                                 
        ?>
        <tr valign="top">
	        <th scope="row" >
	        	<label for="wf_country">
	        	<?php _e('Country/State','wf-woocommerce-packing-list'); ?></label></th>
	        <td>
	        	<select name="wf_country" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'woocommerce' ); ?>">
						<?php WC()->countries->country_dropdown_options($country,$state ); ?>
         		</select>
	        </td>
	        <td></td>
	    </tr>
	    <?php
	    self::generate_form_field(array(
			array(
				'label'=>__("Postal code",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sender_postalcode",
			),
			array(
				'label'=>__("Contact number",'wf-woocommerce-packing-list'), 
				'option_name'=>"woocommerce_wf_packinglist_sender_contact_number",
			),
			array(
				'label'=>__("VAT",'wf-woocommerce-packing-list'), 
				'option_name'=>"woocommerce_wf_packinglist_sender_vat",
			),
		));
	    ?>
	</table>
	<?php 
    include "admin-settings-save-button.php";
    ?>
</div>