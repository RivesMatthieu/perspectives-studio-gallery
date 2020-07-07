<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<h3><?php _e('Advanced', 'wf-woocommerce-packing-list'); ?></h3>
	<p>
		<?php _e('The below settings can be used to configure additional information with respect to custom data, 
RTL support, packaging formats etc
','wf-woocommerce-packing-list'); ?>
	</p>
	<table class="wf-form-table">
	    <tr>
	        <th>
	        <span><?php _e('Add additional fields on checkout page','wf-woocommerce-packing-list'); ?> 
	        	<?php echo Wf_Woocommerce_Packing_List_Admin::set_tooltip('wf_invoice_additional_checkout_data_fields'); ?>
	        </span>
	            </th>
	        <td> 
	            <div class="wf_select_multi">
	                <?php
	                $user_selected_add_fields = array();
	                
	                $add_checkout_data_flds=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
	                $user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');

	                /* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
		            $user_created=Wf_Woocommerce_Packing_List::process_checkout_fields($user_created);
		            $fields=array_merge($add_checkout_data_flds,$user_created);
	                ?>
	                <input type="hidden" name="wf_invoice_additional_checkout_data_fields_hidden" value="1" />
	                <select class="wc-enhanced-select" data-placeholder='<?php _e('Additional Fields','wf-woocommerce-packing-list'); ?>' name="wf_invoice_additional_checkout_data_fields[]" multiple="multiple">               
	                <?php
	                /* user selected fields */
	                $vl=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields'); //document specific user selected fields
	                $user_selected_arr=$vl && is_array($vl) ? $vl : array();
	                $additional_checkout_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
	                foreach($fields as $key=>$field)
	                {
	                	$add_data=isset($additional_checkout_field_options[$key]) ? $additional_checkout_field_options[$key] : array();
	                	$is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);

	                	/* we are giving option to edit title of builtin items */
	                	$field=(isset($add_data['title']) && trim($add_data['title'])!="" ? $add_data['title'] : $field);
	                ?>
	                    <option value="<?php echo $key;?>" <?php echo in_array($key,$user_selected_arr) ? 'selected' : '';?>><?php echo $field;?> (<?php echo $key;?>) <?php echo ($is_required==1 ? ' ('.__('required','wf-woocommerce-packing-list').')' : '');?></option>
	                <?php
	                }
	                ?>						 
	                </select>
	                <button type="button" class="button button-secondary" data-wf_popover="1" data-title="Checkout Field Inserter" data-module-base="main" data-content-container=".wt_pklist_checkout_inserter_form" style="margin-top:5px; margin-left:5px; float:right;">
	                    <?php _e('Add/Edit Custom Field','wf-woocommerce-packing-list'); ?>                        
	                </button>
	                
	                <div class="wt_pklist_checkout_inserter_form" style="display:none;">
	                	<div class="wt_pklist_checkout_field_tab">
	                		<div class="wt_pklist_checkout_field_tab_head active_tab" data-target="add_new" data-add-title="<?php _e('Add new','wf-woocommerce-packing-list');?>" data-edit-title="<?php _e('Edit','wf-woocommerce-packing-list');?>">
	                			<span class="wt_pklist_checkout_field_tab_head_title"><?php _e('Add new','wf-woocommerce-packing-list');?></span>
	                			<div class="wt_pklist_checkout_field_tab_head_patch"></div>
	                		</div>
	                		<div class="wt_pklist_checkout_field_tab_head" data-target="list_view">
	                			<?php _e('Previously added','wf-woocommerce-packing-list');?>
	                			<div class="wt_pklist_checkout_field_tab_head_patch"></div>		
	                		</div>
	                		<div class="wt_pklist_checkout_field_tab_head_add_new" title="<?php _e('Add new','wf-woocommerce-packing-list');?>">
	                			<span class="dashicons dashicons-plus"></span>
	                		</div>
	                	</div>
	                	<div class="wt_pklist_checkout_field_tab_content active_tab" data-id="add_new">
		                	<div class='wt_pklist_checkout_field_tab_form_row wt_pklist_checkout_field_form_notice'>
		                		<?php _e('You can edit existing Meta items by using Meta key of the item.','wf-woocommerce-packing-list');?>
		                	</div>
		                	<div class='wt_pklist_checkout_field_tab_form_row'>
								<div style='width:48%; float:left;'><?php _e('Field Name','wf-woocommerce-packing-list'); ?><i style="color:red;">*</i>: <input type='text' name='wf_new_custom_field' data-required="1" style='width:100%'/></div>
								<div style='width:48%; float:right;'><?php _e('Meta Key','wf-woocommerce-packing-list'); ?><i style="color:red;">*</i>: <input type='text' name='wf_new_custom_field_key' data-required="1" style='width:100%'/> </div>
							</div>
							<div class="wt_pklist_checkout_field_tab_form_row">
								<div style='width:48%; float:left;'><?php _e('Placeholder','wf-woocommerce-packing-list'); ?>: <input type='text' name='wf_new_custom_field_placeholder' style='width:100%'/></div>
								<div style='width:48%; float:right;'><?php _e('Is mandatory field?','wf-woocommerce-packing-list'); ?>
										<div style='width:100%; height:32px; margin-top:10px;'>
												<span style='display:inline-block; margin:3px 10px;'>
													<input type='radio' name='wt_pklist_cst_chkout_required' value='1'> Yes
												</span>
												<span style='display:inline-block; margin:3px 10px;'>
													<input type='radio' name='wt_pklist_cst_chkout_required' value='0' checked="checked"> No
												</span>
										</div>
								</div>
							</div>
							<div class='wt_pklist_checkout_field_tab_form_row'> 
								<i><?php _e('Please use only alphabets and underscore for meta key.','wf-woocommerce-packing-list'); ?> 
								<br /> <?php _e('Correct','wf-woocommerce-packing-list'); ?>: my_meta_key, meta_one 
								<br /> <?php _e('Incorrect','wf-woocommerce-packing-list'); ?>: #My meta Key, Meta.1 </i>
							</div>
						</div>
						<div class="wt_pklist_checkout_field_tab_content" data-id="list_view" style="height:265px; overflow:auto;">
							
						</div>
	                </div>

	                <br>
	                <span class="wf_form_help" style="display:inline;">
	                	<?php echo __('Select/add additional fields in the checkout page e.g VAT, SSN etc','wf-woocommerce-packing-list'); ?>
	                </span>
	            </div>
	        </td>
	        <td></td>	
	    </tr>
	    <?php
		self::generate_form_field(array(
			array(
				'type'=>"radio",
				'label'=>__("Preview before printing",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_preview",
				'radio_fields'=>array(
					'enabled'=>__('Enabled','wf-woocommerce-packing-list'),
					'disabled'=>__('Disabled','wf-woocommerce-packing-list')
				),
			),
			array(
				'type'=>"radio",
				'label'=>__("Enable RTL support",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_add_rtl_support",
				'radio_fields'=>array(
					'Yes'=>__('Yes','wf-woocommerce-packing-list'),
					'No'=>__('No','wf-woocommerce-packing-list')
				),
				'help_text'=>__('RTL support in documents generated by browser','wf-woocommerce-packing-list'),
			),
			array(
				'label'=>__("Tracking number meta",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_tracking_number",
				'help_text'=>__("Enter the tracking number meta field to add tracking number information",'wf-woocommerce-packing-list'),
			),
			array(
				'type'=>"radio",
				'label'=>__("Display state name",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_state_code_disable",
				'radio_fields'=>array(
					'yes'=>__('Yes','wf-woocommerce-packing-list'),
					'no'=>__('No','wf-woocommerce-packing-list')
				),
				'help_text'=>__('Displays the state name instead of state code in billing and shipping address','wf-woocommerce-packing-list'),
			),
			array(
				'type'=>'textarea',
				'label'=>__("Transport terms",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_transport_terms",
				'help_text'=>__("Now only applicable for Proforma invoice",'wf-woocommerce-packing-list'),
			),
			array(
				'type'=>'textarea',
				'label'=>__("Sale terms",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_sale_terms",
				'help_text'=>__("Now only applicable for Proforma invoice",'wf-woocommerce-packing-list'),
			),
		));
		?>
		<tr>
			<th> <span><?php _e('Show tax', 'wf-woocommerce-packing-list'); ?>
				<?php echo Wf_Woocommerce_Packing_List_Admin::set_tooltip('woocommerce_wf_generate_for_taxstatus'); ?>
			</span>
			</th>
			<td>
				<div class="wf-form-group">	
					<select style="width:70%; float:left;" id="tax_status" name="woocommerce_wf_generate_for_taxstatus[]" >
						<?php
						$wf_generate_invoice_for_tax=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
						echo "<option value='ex_tax'";
						if (in_array('ex_tax', $wf_generate_invoice_for_tax))
							echo "  selected";
						echo ">".__('Exclude tax','wf-woocommerce-packing-list')."</option>";
						echo "<option value='in_tax'";
						if (in_array('in_tax', $wf_generate_invoice_for_tax))
							echo "  selected";
						echo ">".__('Include tax','wf-woocommerce-packing-list')."</option>";
						?>
					</select>
					<a href="<?php echo admin_url('admin.php?page=wc-settings&tab=tax');?>" class="button button-primary" target='_blank' style="float: right; text-align: center; width:25%;"><?php _e('Setup Tax','wf-woocommerce-packing-list');?> <span class="dashicons dashicons-external"></span></a>
				</div>
				<span class="wf_form_help" style="display:inline-block;">
                	<?php echo __('Configure the product price/subtotal inclusive or exclusive of tax.','wf-woocommerce-packing-list'); ?>
                </span>
			</td>
		<td></td>	
		</tr>
		<?php
		self::generate_form_field(array(
			array(
				'type'=>"select",
				'label'=>__("Packaging Type",'wf-woocommerce-packing-list'),
				'option_name'=>"woocommerce_wf_packinglist_package_type",
				'select_fields'=>array(
					'pack_items_individually'=>__('Pack items individually','wf-woocommerce-packing-list'),
					'box_packing'=>__('Box packing','wf-woocommerce-packing-list'),
					'single_packing'=>__('Single package per order','wf-woocommerce-packing-list')
				),
				'help_text'=>sprintf(__('%sSingle package(per order)%s - All the items belonging to an order are packed together into a single package. Every order will have a respective package.'),'<b>','</b>').'<br />'.
    				sprintf(__('%sBox packing(per order)%s - All the items belonging to an order are packed into the respective boxes as per the configuration. Every order may have one or more boxes based on the configuration.'),'<b>','</b>').'<br />'.
    				sprintf(__('%sPack items individually%s - Every item from the order/s are packed individually. e.g if an order has 2 quantities of product A and 1 quantity of product B, there will be three packages consisting one item each from the order.'),'<b>','</b>'),
				'form_toggler'=>array(
					'type'=>'parent',
					'target'=>'wf_box_packing_table',
				)
			)
		));
		?>
	</table>
	<!-- box dimensions -->
	<div wf_frm_tgl-id="wf_box_packing_table" wf_frm_tgl-val="box_packing">
		<?php
		$wf_packlist_boxes=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_boxes');
		$weight_unit = get_option('woocommerce_weight_unit');
	    $dimension_unit = get_option('woocommerce_dimension_unit');
		?>
		<input type='hidden' id='dimension_unit' value='<?php echo $dimension_unit; ?>'>
	    <input type='hidden' id='weight_unit' value='<?php echo $weight_unit; ?>'>
	    <strong><?php _e('Box Sizes','wf-woocommerce-packing-list'); ?></strong><br><br>
	    <table class="woocommerce_wf_packinglist_boxes widefat">
	        <thead>
	            <tr>
	                <th class="check-column" style="padding: 0px; vertical-align: middle;"><input type="checkbox" /></th>
	                <th><?php _e('Name','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Length','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Width','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Height','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Box Weight','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Max Weight','wf-woocommerce-packing-list'); ?></th>
	                <th><?php _e('Enabled','wf-woocommerce-packing-list'); ?></th>
	            </tr>
	        </thead>
	        <tfoot>
	            <tr>
	                <th colspan="8">
	                	<div style="float:left; margin:0px 15px;">
	                 		<a href="#" class="button plus insert"><?php _e('Add Box','wf-woocommerce-packing-list'); ?></a>
	                    	<a href="#" class="button minus remove"><?php _e('Remove selected box(es)','wf-woocommerce-packing-list'); ?></a>
	                	</div>
	                    <small class="description"><?php _e('Items will be packed into these boxes depending on its dimensions and volume, those that do not fit will be packed individually.','wf-woocommerce-packing-list'); ?></small>
	                </th>
	            </tr>
	        </tfoot>
	        <tbody id="rates">
			<?php
			if ($wf_packlist_boxes) 
			{
				foreach ($wf_packlist_boxes as $key => $box) 
				{
					if (!is_numeric($key))
				    continue;
				?>
	                <tr>
	                    <td class="check-column" style="padding: 10px; vertical-align: middle;"><input type="checkbox" /></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][name]" value="<?php echo esc_attr($box['name']); ?>" /></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][length]" value="<?php echo esc_attr($box['length']); ?>" /><?php echo $dimension_unit; ?></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][width]" value="<?php echo esc_attr($box['width']); ?>" /><?php echo $dimension_unit; ?></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][height]" value="<?php echo esc_attr($box['height']); ?>" /><?php echo $dimension_unit; ?></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][box_weight]" value="<?php echo esc_attr($box['box_weight']); ?>" /><?php echo $weight_unit; ?></td>
	                    <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][max_weight]" value="<?php echo esc_attr($box['max_weight']); ?>" /><?php echo $weight_unit; ?></td>
	                    <td><input type="checkbox" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][enabled]" <?php checked($box['enabled'], true); ?> /></td>
	                </tr>
				<?php
				}
			}
			?>
	        </tbody>
	    </table>    
	</div>
	<!-- box dimensions -->

	<table class="wf-form-table">
		<?php
		self::generate_form_field(array(
			array(
				'type'=>'field_group_head', //field type
				'head'=>__('Temp files','wf-woocommerce-packing-list'),
				'group_id'=>'temp_files_field', //field group id
				'show_on_default'=>1,
			),
			array(
				'type'=>"plaintext",
				'label'=>__("File path",'wf-woocommerce-packing-list'),
				'option_name'=>"",
				'field_group'=>"temp_files_field",
				'text'=>Wf_Woocommerce_Packing_List::get_temp_dir('path'),
				'non_field'=>true,
			),
		));
		$total_temp_files=Wf_Woocommerce_Packing_List_Admin::get_total_temp_files();
		?>
		<tr data-field-group="temp_files_field" class="wt_pklist_field_group_children">
			<th>
				<label for="<?php echo $field_name;?>"><?php _e('Total files','wf-woocommerce-packing-list'); ?></label>
			</th>
			<td>
				<?php
				if($total_temp_files>0)
				{
				?>
				<span style="line-height:38px;" class="tmp_files_count"><?php echo $total_temp_files.' '.__('Temp file(s) found.', 'wf-woocommerce-packing-list'); ?></span>
				<?php
				}else
				{
					_e('No files found.', 'wf-woocommerce-packing-list');
				}
				if($total_temp_files>0)
				{
				?>
					<button type="button" class="button button-secondary wt_pklist_temp_files_btn" data-action="delete_all_temp" style="float:right;"><?php _e('Delete all', 'wf-woocommerce-packing-list'); ?></button>
					<button type="button" class="button button-secondary wt_pklist_temp_files_btn" data-action="download_all_temp" style="float:right; margin-right:10px;"><?php _e('Download all', 'wf-woocommerce-packing-list'); ?></button>
				<?php
				}
				?>
			</td>
			<td></td>
		</tr>
		<?php
		self::generate_form_field(array(
			array(
				'type'=>"radio",
				'label'=>__("Automatic cleanup",'wf-woocommerce-packing-list'),
				'option_name'=>"wf_pklist_auto_temp_clear",
				'field_group'=>"temp_files_field",
				'radio_fields'=>array(
					'Yes'=>__('Yes','wf-woocommerce-packing-list'),
					'No'=>__('No','wf-woocommerce-packing-list')
				),
				'form_toggler'=>array(
					'type'=>'parent',
					'target'=>'wf_pklist_auto_temp',
				)
			),
			array(
				'type'=>'text',
				'label'=>__("Interval",'wf-woocommerce-packing-list'),
				'option_name'=>"wf_pklist_auto_temp_clear_interval",
				'help_text'=>__('In minutes. Eg: 1440 for 1 day.'),
				'field_group'=>"temp_files_field",
				'form_toggler'=>array(
					'type'=>'child',
					'id'=>'wf_pklist_auto_temp',
					'val'=>'Yes',
				)
			),
		));
		?>
	</table>
	<?php 
    include "admin-settings-save-button.php";
    ?>
</div>