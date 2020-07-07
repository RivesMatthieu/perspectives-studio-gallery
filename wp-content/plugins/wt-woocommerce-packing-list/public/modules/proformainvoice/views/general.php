<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<p><?php _e('Configure the general settings required for proforma invoice.','wf-woocommerce-packing-list');?>
	<table class="wf-form-table">
	    <?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'type'=>"radio",
				'label'=>__("Use order date as proforma invoice date",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_orderdate_as_invoicedate",
				'help_text'=>__("If you choose 'No' then the proforma invoice date will be the date on which it is generated.",'wf-woocommerce-packing-list'),
				'radio_fields'=>array(
					'Yes'=>__('Yes','wf-woocommerce-packing-list'),
					'No'=>__('No','wf-woocommerce-packing-list')
				),
			),
			array(
				'type'=>'order_st_multiselect',
				'label'=>__("Generate proforma invoice for order statuses",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_generate_for_orderstatus",
				'help_text'=>__("Order statuses for which an proforma invoice should be generated.",'wf-woocommerce-packing-list'),
				'order_statuses'=>$order_statuses,
				'field_vl'=>array_flip($order_statuses),
				'attr'=>'',
			),
			array(
				'type'=>"radio",
				'label'=>__("Attach proforma invoice PDF in email",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_add_".$this->module_base."_in_mail",
				'radio_fields'=>array(
					'Yes'=>__('Yes','wf-woocommerce-packing-list'),
					'No'=>__('No','wf-woocommerce-packing-list')
				),
				'help_text'=>__('PDF version of proforma invoice will be attached with the order email based on the above statuses','wf-woocommerce-packing-list'),		
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
				'label'=>__("Order statuses to show print button",'wf-woocommerce-packing-list'),
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
				'type'=>"additional_fields",
				'label'=>__("Order meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_contactno_email',
				'module_base'=> $this->module_base,
				'help_text'=> __('Select/add additional order information in the proforma invoice.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"product_meta",
				'label'=>__("Product meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_product_meta_fields',
				'module_base'=>$this->module_base,
				'help_text'=>__('Select/add additional product information in the proforma invoice Product Table.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"textarea",
				'label'=>__("Custom footer for proforma invoice",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_footer",
				'help_text'=>__('If left blank, defaulted to footer from General settings.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>'textarea',
				'label'=>__("Special notes",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_special_notes",
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
		),$this->module_id);
		?>
	</table>
	<?php 
    include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
    ?>
</div>