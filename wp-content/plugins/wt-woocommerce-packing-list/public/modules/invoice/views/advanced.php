<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<p>
		<?php _e('The below settings can be used to configure additional information with respect to order/product 
Meta.','wf-woocommerce-packing-list');?>
	</p>
	<table class="wf-form-table">
	    <?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'type'=>"additional_fields",
				'label'=>__("Order meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_contactno_email',
				'module_base'=>$this->module_base,
				'help_text'=>__('Select/add the additional order information in the invoice.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"product_meta",
				'label'=>__("Product meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_product_meta_fields',
				'module_base'=>$this->module_base,
				'help_text'=>__('Select/add the additional product information in the invoice.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"textarea",
				'label'=>__("Custom footer for invoice",'wf-woocommerce-packing-list'),
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