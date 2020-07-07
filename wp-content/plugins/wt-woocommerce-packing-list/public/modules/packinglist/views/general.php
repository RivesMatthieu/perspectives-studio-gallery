<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
<p>
	<?php _e('Configure the general settings required for packing slip.','wf-woocommerce-packing-list');?>
</p>
<table class="wf-form-table">
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>"radio",
			'label'=>__("Group by category",'wf-woocommerce-packing-list'),
			'option_name'=>"wf_woocommerce_product_category_wise_splitting",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			)
		),
		array(
			'type'=>"radio",
			'label'=>__("Enable variation data",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_variation_data",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			//'help_text'=>__("Enable to add variation information",'wf-woocommerce-packing-list'),
		),
		array(
			'type'=>"additional_fields",
			'label'=>__("Order meta fields ",'wf-woocommerce-packing-list'),
			'option_name'=>'wf_'.$this->module_base.'_contactno_email',
			'module_base'=>$this->module_base,
			'help_text'=>__('Select/add additional order information in the packing slip.','wf-woocommerce-packing-list'),
		),
		array(
			'type'=>"product_meta",
			'label'=>__("Product meta fields",'wf-woocommerce-packing-list'),
			'option_name'=>'wf_'.$this->module_base.'_product_meta_fields',
			'module_base'=>$this->module_base,
			'help_text'=>__('Select/add additional product information in the packing slip.','wf-woocommerce-packing-list'),
		),
		array(
			'type'=>'order_st_multiselect',
			'label'=>__("Enable print packing slip option for order status",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_attach_".$this->module_base,
			'help_text'=>__("Adds print packing slip button to the order email for chosen status",'wf-woocommerce-packing-list'),
			'order_statuses'=>$order_statuses,
			'field_vl'=>array_flip($order_statuses),
		),
		array(
			'type'=>'order_st_multiselect',
			'label'=>__("Attach Packing slip PDF in order email for statuses", 'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_generate_for_orderstatus",
			'order_statuses'=>$order_statuses,
			'field_vl'=>array_flip($order_statuses),
			'help_text'=>__('PDF version of Packing slip will be attached with the order email','wf-woocommerce-packing-list'),
		),
		array(
			'type'=>"radio",
			'label'=>__("Share Packing slip PDF as a separate email",'wf-woocommerce-packing-list'),
			'option_name'=>"wt_pklist_separate_email",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'help_text'=>sprintf(__('Select `Yes` and save settings, if you need to send a copy of the Packing slip to another email id e.g admin email. You may then configure the email %shere%s accordingly.', 'wf-woocommerce-packing-list'), '<a href="'.$email_settings_path.'" target="_blank">', '</a>'),
		),
		array(
			'type'=>"textarea",
			'label'=>__("Custom footer",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_footer",
			'help_text'=>__('If left blank, defaulted to footer from General settings.','wf-woocommerce-packing-list'),
		),
	),$this->module_id);
	?>
</table>

<?php 
include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
?>
</div>