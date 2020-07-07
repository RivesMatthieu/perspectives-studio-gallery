<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
<p><?php _e('Configure the general settings required for shipping label.','wf-woocommerce-packing-list'); ?></p>
<table class="wf-form-table">
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>"radio",
			'label'=>__("Enable multiple labels in one page",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_enable_multiple_shipping_label",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wf_shipping_label_column_count',
			)
		),
		array(
			'type'=>"number",
			'label'=>__("No of labels in one row",'wf-woocommerce-packing-list'),
			'option_name'=>"wf_shipping_label_column_number",
			'form_toggler'=>array(
				'type'=>'child',
				'id'=>'wf_shipping_label_column_count',
				'val'=>'Yes',
			)
		),
		array(
			'type'=>"select",
			'label'=>__("Shipping label size",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_packinglist_label_size",
			'select_fields'=>array(
				2=>__('Full Page','wf-woocommerce-packing-list'),
				1=>__('Custom','wf-woocommerce-packing-list'),
			),
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wf_shipping_label_custom_size',
			)
		),
	),$this->module_id);
	?>
	<tr wf_frm_tgl-id="wf_shipping_label_custom_size" wf_frm_tgl-val="1">
		<th scope="row" >
			<label><?php _e("Custom size",'wf-woocommerce-packing-list'); ?></label>
		</th>
		<td style="text-align:center; line-height:40px;">
			<?php
			$width_vl=Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_width',$this->module_id);
			$height_vl=Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_height',$this->module_id);
			?>
			<input type="number" value="<?php echo $width_vl;?>" name="wf_custom_label_size_width" style="width:45%; float:left;" step="0.01"> X 
			<input type="number" value="<?php echo $height_vl;?>" name="wf_custom_label_size_height" style="width:45%; float:right;" step="0.01">
			<span class="wf_form_help" style="line-height:12px; text-align:left; margin-top:4px; display:block;"><?php _e('Enter the custom width and height values(in order), in inches.','wf-woocommerce-packing-list'); ?></span>
		</td>
	</tr>
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>"additional_fields",
			'label'=>__("Order meta fields",'wf-woocommerce-packing-list'),
			'option_name'=>'wf_'.$this->module_base.'_contactno_email',
			'module_base'=>$this->module_base,
			'help_text'=>__('Select/add additional order information in the shipping label.','wf-woocommerce-packing-list'),
		),
		array(
			'type'=>'order_st_multiselect',
			'label'=>__("Enable print shipping label option for order status",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_attach_shippinglabel",
			'help_text'=>__("Adds print packing slip button to the order email for chosen status",'wf-woocommerce-packing-list'),
			'order_statuses'=>$order_statuses,
			'field_vl'=>array_flip($order_statuses),
		),
	),$this->module_id);
	?>
</table>

<?php 
include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
?>
</div>