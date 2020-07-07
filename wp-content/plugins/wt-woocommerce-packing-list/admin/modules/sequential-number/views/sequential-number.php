<?php
if (!defined('ABSPATH')) {
	exit;
}
$date_frmt_tooltip=__('Click to append with existing data','wf-woocommerce-packing-list');
?>
<style type="text/css">
.wf_inv_num_frmt_hlp_btn{ cursor:pointer; }
.wf_inv_num_frmt_hlp table thead th{ font-weight:bold; text-align:left; }
.wf_inv_num_frmt_hlp table tbody td{ text-align:left; }
.wf_inv_num_frmt_hlp .wf_pklist_popup_body{min-width:300px; padding:20px;}
.wf_inv_num_frmt_append_btn{ cursor:pointer; }
</style>
<!-- Invoice number Prefix/Suffix help popup -->
<div class="wf_inv_num_frmt_hlp wf_pklist_popup">
	<div class="wf_pklist_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-calendar-alt"></span> <?php _e('Date formats','wf-woocommerce-packing-list');?>
		<div class="wf_pklist_popup_close">X</div>
	</div>
	<div class="wf_pklist_popup_body">
		
		<p style="text-align:left; max-width:400px; margin-top:0px;">
			<?php _e("By default the arguments will consider the document date(date on which the document invoice, shipping label etc is generated) as the input. ",'wf-woocommerce-packing-list'); ?>
			<br />
			<br />
			<input type="checkbox" name="wf_inv_num_frmt_data_val" id="wf_inv_num_frmt_order_date" value="order_date"> <label for="wf_inv_num_frmt_order_date"><?php _e('User order date as input instead.','wf-woocommerce-packing-list');?></label>
			<span class="wf_form_help" style="margin-top:2px;">Enable this if you want to use the order date as input for the below arguments.</span> 
		</p>

		<p style="text-align:left; margin-bottom:2px;">
			<?php _e('Select from any of the formats below:','wf-woocommerce-packing-list');?>
		</p>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php _e('Format','wf-woocommerce-packing-list');?></th><th><?php _e('Output','wf-woocommerce-packing-list');?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[F]</a></td>
					<td><?php echo date('F'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[dS]</a></td>
					<td><?php echo date('dS'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[M]</a></td>
					<td><?php echo date('M'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[m]</a></td>
					<td><?php echo date('m'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d]</a></td>
					<td><?php echo date('d'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[D]</a></td>
					<td><?php echo date('D'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[y]</a></td>
					<td><?php echo date('y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[Y]</a></td>
					<td><?php echo date('Y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d/m/y]</a></td>
					<td><?php echo date('d/m/y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d-m-Y]</a></td>
					<td><?php echo date('d-m-Y'); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
<p> 
	<?php _e(sprintf('Use the configurations below to set up a custom %s number with prefix/suffix/number series or mirror the order number respectively.',$to_module_title),'wf-woocommerce-packing-list');?>
</p>
<form method="post" class="wf_settings_form">
<?php
    // Set nonce:
    if (function_exists('wp_nonce_field'))
    {
        wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
    }
?>
<input type="hidden" class="wf_settings_base" value="<?php echo $to_module;?>">
<input type="hidden" name="update_sequential_number" value="<?php echo $to_module_id;?>">
<table class="wf-form-table">
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>'select',
			'label'=>__(sprintf('%s number format', $to_module_title),'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_invoice_number_format",
			'select_fields'=>array(
				'[number]'=>'[number]',
				'[number][suffix]'=>'[number][suffix]',
				'[prefix][number]'=>'[prefix][number]',
				'[prefix][number][suffix]'=>'[prefix][number][suffix]',
			)
			//'help_text'=>"Eg: [prefix][number][suffix]",
		),
		array(
			'type'=>"radio",
			'label'=>__(sprintf('Use order number as %s number', $to_module_title),'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_invoice_as_ordernumber",
			'radio_fields'=>array(
				'Yes'=>__('Yes','wf-woocommerce-packing-list'),
				'No'=>__('No','wf-woocommerce-packing-list')
			),
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wwpl_custom_inv_no',
			),
			'help_text'=>($to_module=='creditnote' ? __('While using this option multiple refunds(via partial refunds) from the same order will carry the same credit note number unless prefixed or suffixed accordingly with an appropriate date format.', 'wf-woocommerce-packing-list') : ''),
		),
	),$to_module_id);
	?>
	<?php
	$opt_name="woocommerce_wf_invoice_start_number";
	$vl=Wf_Woocommerce_Packing_List::get_option($opt_name,$to_module_id);
	$tt_text=Wf_Woocommerce_Packing_List_Admin::set_tooltip($opt_name,$to_module_id); //tooltip text
	?>
	<tr id="woocommerce_wf_invoice_start_number_tr" wf_frm_tgl-id="wwpl_custom_inv_no" wf_frm_tgl-val="No" wf_frm_tgl-lvl="2">
		<th><label><?php _e(sprintf('%s Start Number', $to_module_title),'wf-woocommerce-packing-list'); ?> <?php echo $tt_text; ?></label></th>
		<td>
			<div class="wf-form-group">

				<input type="number" min="1" step="1" readonly="" style="background:#eee; width:58%; float:left;" name="<?php echo $opt_name;?>" value="<?php echo $vl;?>">
				<input style="float: right;" id="reset_invoice_button" type="button"  class="button button-primary" value="<?php _e(sprintf('Reset %s no', $to_module_title),'wf-woocommerce-packing-list'); ?>"/>
			</div>
			<?php
			$opt_name="woocommerce_wf_Current_Invoice_number";
			$vl=Wf_Woocommerce_Packing_List::get_option($opt_name,$to_module_id);
			?>
			<input type="hidden" class="wf_current_invoice_number" value="<?php echo $vl;?>" name="<?php echo $opt_name;?>">
		</td>
		<td></td>
	</tr>
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'label'=>__("Prefix",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_invoice_number_prefix",
			'help_text'=>"Use any of the <a class=\"wf_inv_num_frmt_hlp_btn\" data-wf-trget=\"woocommerce_wf_invoice_number_prefix\">date formats</a>(to append the document generated date/order date) or alphanumeric characters.",
		),
		array(
			'label'=>__("Suffix",'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_invoice_number_postfix",
			'help_text'=>"Use any of the <a class=\"wf_inv_num_frmt_hlp_btn\" data-wf-trget=\"woocommerce_wf_invoice_number_postfix\">date formats</a>(to append the document generated date/order date) or alphanumeric characters.",
		),
		array(
			'type'=>'number',
			'label'=>__(sprintf('%s number length', $to_module_title),'wf-woocommerce-packing-list'),
			'option_name'=>"woocommerce_wf_invoice_padding_number",
			'attr'=>'min="0"',
			'help_text'=>sprintf('Indicates the total length of the %s number, excluding the length of prefix and suffix if added. If the length of the generated %s number is less than the provided, it will be padded with ‘0’. E.g if you specify 7 as %s number length and your %s number is 8009, it will be represented as 0008009 in the respective documents.
', $to_module_title, $to_module_title, $to_module_title, $to_module_title),
		)
	),$to_module_id);
	?>	
</table>
<?php
$settings_button_title='Update';
include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
?>
</form>
</div>