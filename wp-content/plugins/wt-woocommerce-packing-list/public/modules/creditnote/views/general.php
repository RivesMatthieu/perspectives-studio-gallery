<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<p><?php _e('Configure the general settings required for credit note.','wf-woocommerce-packing-list');?>
	<table class="wf-form-table">
	    <?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'type'=>"additional_fields",
				'label'=>__("Order meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_contactno_email',
				'module_base'=>$this->module_base,
				'help_text'=>__('Select/add additional order information in the credit note.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"product_meta",
				'label'=>__("Product meta fields",'wf-woocommerce-packing-list'),
				'option_name'=>'wf_'.$this->module_base.'_product_meta_fields',
				'module_base'=>$this->module_base,
				'help_text'=>__('Select/add additional product information in the credit note Product Table.','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"radio",
				'label'=>__("Attach credit note PDF in email",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_add_creditnote_in_mail",
				'radio_fields'=>array(
					'Yes'=>__('Yes','wf-woocommerce-packing-list'),
					'No'=>__('No','wf-woocommerce-packing-list')
				),
				'help_text'=>__('PDF version of credit note will be attached with the refund email','wf-woocommerce-packing-list'),
			)
		),$this->module_id);
		?>
	</table>
	<?php 
    include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
    ?>
</div>