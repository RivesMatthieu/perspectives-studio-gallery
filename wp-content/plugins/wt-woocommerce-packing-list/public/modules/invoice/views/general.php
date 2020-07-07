<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
<p><?php _e('Configure the general settings required for the invoice.','wf-woocommerce-packing-list');?></p>
<table class="wf-form-table">
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>"radio",
			'label'=>__("Enable invoice",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_enable_invoice",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
		),
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
			'label'=>__("Use order date as invoice date",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_orderdate_as_invoicedate",
			'help_text'=>__("If you choose 'No' then the invoice date will be the date on which it is generated.",'wf-woocommerce-packing-list'),
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
		),
		array(
			'type'=>'order_st_multiselect',
			'label'=>__("Generate invoice for order statuses",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_generate_for_orderstatus",
			'help_text'=>__("Order statuses for which an invoice should be generated.",'wf-woocommerce-packing-list'),
			'order_statuses'=>$order_statuses,
			'field_vl'=>array_flip($order_statuses),
			'attr'=>'',
		),
		array(
			'type'=>"radio",
			'label'=>__("Attach invoice PDF in order email",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_add_".$this->module_base."_in_mail",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'help_text'=>__('PDF version of invoice will be attached with the order email based on the above statuses','wf-woocommerce-packing-list'),		
		),
		array(
			'type'=>"radio",
			'label'=>__("Enable print option for customers",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_frontend_info",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'help_text'=>__("Add print button to the order email/order summary",'wf-woocommerce-packing-list'),
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wf_enable_print_button',
			)
		),
		array(
			'type'=>'order_st_multiselect',
			'label'=>__("Show print button only for statuses",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_attach_".$this->module_base,
			'order_statuses'=>$order_statuses,
			'field_vl'=>$wf_generate_invoice_for,
			'form_toggler'=>array(
				'type'=>'child',
				'id'=>'wf_enable_print_button',
				'val'=>'Yes',
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
		),
		array(
			'type'=>"radio",
			'label'=>__("Show individual tax column in product table",'wf-woocommerce-packing-list'),
			'option_name'=>"wt_pklist_show_individual_tax_column",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'help_text'=>__("Your template must support tax columns",'wf-woocommerce-packing-list'),
		),
		array(
			'type'=>"uploader",
			'label'=>__("Upload signature",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_invoice_signature",
		),
		array(
			'type'=>"uploader",
			'label'=>__("Custom logo for invoice",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_logo",
			'help_text'=>__('If left blank, defaulted to logo from General settings.','wf-woocommerce-packing-list'),
		),
	),$this->module_id);
	?>
</table>
<?php 
include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
?>
</div>