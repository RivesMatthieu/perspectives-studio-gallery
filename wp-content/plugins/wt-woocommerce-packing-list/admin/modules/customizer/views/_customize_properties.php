<?php
if (!defined('ABSPATH')) {
    exit;
}
function Wf_gen_customize_form_field_sub($arg)
{
	$field_type=isset($arg['type']) ? $arg['type'] : 'text';
	$label=isset($arg['label']) ? $arg['label'] : '';
	
	//event class decides which event to bind (keyup, keypress)
	$event_class=isset($arg['event_class']) ? $arg['event_class'] : 'wf_cst_keyup';
	
	//css property/attribute
	$css_prop=isset($arg['css_prop']) ? ' data-prop="'.$arg['css_prop'].'"' : '';
	
	//target element
	$trgt_elm=isset($arg['trgt_elm']) ? ' data-elm="'.$arg['trgt_elm'].'"' : '';

	//preview element. in case of attribute as target prop
	$preview_elm=isset($arg['preview_elm']) ? ' data-preview_elm="'.$arg['preview_elm'].'"' : '';

	$unit=isset($arg['unit']) ? ' data-unit="'.$arg['unit'].'"' : ' data-unit=""';
	$default_data=isset($arg['default_data']) ? ' data-default="'.$arg['default_data'].'"' : '';
	if($field_type=='color' && $default_data=='')
	{
		$default_data='data-default="#ffffff"';
	}
	$width=isset($arg['width']) ? 'width:'.$arg['width'].'; ' : '';
	$float=isset($arg['float']) ? 'float:'.$arg['float'].'; ' : '';

	$elm_props=$css_prop.$trgt_elm.$preview_elm.$unit.$default_data;
	$frmgrp_style_props=$width.$float;
	?>
	<div class="wf_side_panel_frmgrp" style="<?php echo $frmgrp_style_props;?>">
		<label><?php echo $label;?></label>
	<?php
	if($field_type=='text')
	{
		?>
		<input type="text" name="" class="wf_sidepanel_txt <?php echo $event_class;?>" <?php echo $elm_props;?> />
		<?php
	}elseif($field_type=='select')
	{
		$select_options=isset($arg['select_options']) ? $arg['select_options'] : array();
		?>
		<select class="wf_sidepanel_sele <?php echo $event_class;?>" <?php echo $elm_props;?> >
			<?php
			foreach($select_options as $select_optionK=>$select_optionV)
			{
				?>
				<option value="<?php echo $select_optionK;?>"><?php echo $select_optionV;?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
	elseif($field_type=='text_inputgrp')
	{
		$addonblock_vl=isset($arg['addonblock']) ? $arg['addonblock'] : 'px';
		?>
		<div class="wf_inptgrp">
			<input type="text" name="" class="wf_sidepanel_txt <?php echo $event_class;?>" <?php echo $elm_props;?> >
			<div class="addonblock"><input type="text" name="" value="<?php echo $addonblock_vl;?>"></div>
		</div>
		<?php
	}
	elseif($field_type=='textarea')
	{
		?>
		<textarea class="wf_sidepanel_txtarea <?php echo $event_class;?>" <?php echo $elm_props;?> ></textarea>
		<?php
	}
	elseif($field_type=='color')
	{
		?>
		<input type="text" name="" class="wf-color-field <?php echo $event_class;?>" <?php echo $elm_props;?> >
		<?php
	}
	elseif($field_type=='checkbox')
	{
		?>
		<input type="checkbox" name="" class="wf-checkbox <?php echo $event_class;?>" <?php echo $elm_props;?> >
		<?php
	}
	?>
	</div>
	<?php
}
function Wf_gen_customize_form_field($args)
{
	if(isset($args['type'])) //single field
	{
		Wf_gen_customize_form_field_sub($args);
	}else
	{
		foreach($args as $arg)
		{
			Wf_gen_customize_form_field_sub($arg);
		}
	}
}
function Wf_get_customize_panel_html($type,$template_type)
{
	$wf_font_weight_labels=array(
		100=>__('Lighter','wf-woocommerce-packing-list'),
		400=>__('Normal','wf-woocommerce-packing-list'),
		700=>__('Bold','wf-woocommerce-packing-list'),
		900=>__('Bolder','wf-woocommerce-packing-list'),
	);
	$wf_font_weight_arr=array();
	for($i=900; $i>=100; $i=$i-100)
	{
		$wf_font_weight_arr[$i]=(isset($wf_font_weight_labels[$i]) ? $wf_font_weight_labels[$i] : $i);
	}
	$wf_date_format_arr=array(
		""=>'--'.__('Select One','wf-woocommerce-packing-list').'--',
		"d/m/Y"=>'d/m/Y',
		"d/m/y"=>'d/m/y',
		"d/M/y"=>'d/M/y',
		"d/M/Y"=>'d/M/Y',
		"m/d/Y"=>'m/d/Y',
		"m/d/y"=>'m/d/y',
		"M/d/y"=>'M/d/y',
		"M/d/Y"=>'M/d/Y',);
	$wf_text_align_arr=array(
		'left'=>__('Left','wf-woocommerce-packing-list'),
		'right'=>__('Right','wf-woocommerce-packing-list'),
		'center'=>__('Center','wf-woocommerce-packing-list'),
		'start'=>__('Start','wf-woocommerce-packing-list'),
		'end'=>__('End','wf-woocommerce-packing-list'),
	);
	$fields=array();
	if($type=='doc_title')
	{
		$fields=array(
			array(
				'label'=>__('Title','wf-woocommerce-packing-list'),
				'type'=>'text',
				'css_prop'=>'html',
				'trgt_elm'=>'doc_title',
			),
			array(
				'label'=>__('Font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>'doc_title',
				'width'=>'49%',
			),
			array(
				'label'=>__('Text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'doc_title',
				'event_class'=>'wf_cst_change',
				'width'=>'49%',
				'float'=>'right',
			),			
			array(
				'label'=>__('Text Color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>'doc_title',
				'event_class'=>'wf_cst_click',
			)
		);
	}
	elseif($type=='company_logo')
	{
		$fields=array(
			array(
				'label'=>__('Display','wf-woocommerce-packing-list'),
				'type'=>'select',
				'event_class'=>'wf_cst_switcher',
				'select_options'=>array(
					'company_logo_img_box'=>__('Company Logo','wf-woocommerce-packing-list'),
					'company_name'=>__('Company Name','wf-woocommerce-packing-list'),
				),
			),
			array(
				'label'=>__('Logo Width','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'width',
				'trgt_elm'=>'company_logo_img',
				'width'=>'49%',
			),
			array(
				'label'=>__('Logo Height','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'height',
				'trgt_elm'=>'company_logo_img',
				'width'=>'49%',
				'float'=>'right',
			),
			array(
				'label'=>__('Company name font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>'company_name',
			),			
			array(
				'label'=>__('Extra Details','wf-woocommerce-packing-list'),
				'type'=>'textarea',
				'css_prop'=>'html',
				'trgt_elm'=>'company_logo_extra_details',
			),
			array(
				'label'=>__('Extra detail font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>'company_logo_extra_details',
			),
			array(
				'label'=>__('Header Color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'background-color',
				'trgt_elm'=>'invoice-header',
				'event_class'=>'wf_cst_click',
			),
		);
	}elseif($type=='barcode')
	{
	}elseif($type=='invoice_number' || $type=='order_number' || $type=='proforma_invoice_number' || $type=='creditnote_number')
	{
		$fields=array(
			array(
				'label'=>__('Text','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>$type.'_label',
			),
			array(
				'label'=>__('Font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>$type,
				'unit'=>'px',
				'width'=>'49%',
			),
			array(
				'label'=>__('Style','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_font_weight_arr,
				'css_prop'=>'font-weight',
				'trgt_elm'=>$type,
				'width'=>'49%',
				'float'=>'right',
				'event_class'=>'wf_cst_change',
			),
			array(
				'label'=>__('Text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_click',
			),
		);
	}elseif($type=='invoice_date' || $type=='order_date' || $type=='dispatch_date' || $type=='proforma_invoice_date')
	{
		$fields=array(
			array(
				'label'=>__('Text','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>$type.'_label',
			),
			array(
				'label'=>__('Format','wf-woocommerce-packing-list'),
				'css_prop'=>'attr-data-'.$type.'-format',
				'event_class'=>'wf_'.$type.'_txt wf_cst_change wf_cst_keyup',
				'trgt_elm'=>$type,
				'unit'=>'',
				'width'=>'49%',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'select',
				'select_options'=>$wf_date_format_arr,
				'css_prop'=>'attr-data-'.$type.'-format',
				'trgt_elm'=>$type,
				'event_class'=>'wf_'.$type.'_sele wf_cst_change',
				'width'=>'49%',
				'float'=>'right',
			),
			array(
				'label'=>__('Font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>$type,
				'unit'=>'px',
				'width'=>'49%',
			),
			array(
				'label'=>__('Style','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_font_weight_arr,
				'css_prop'=>'font-weight',
				'trgt_elm'=>$type,
				'width'=>'49%',
				'float'=>'right',
				'event_class'=>'wf_cst_change',
			),
			array(
				'label'=>__('Text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_click',
			),
		);
	}elseif($type=='from_address' || $type=='billing_address' || $type=='shipping_address' || $type=='return_address')
	{
		$fields=array(
			array(
				'label'=>__('Title','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>$type.'_label',
			),
			array(
				'label'=>__('Text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_change',
			),
			array(
				'label'=>__('Text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_click',
			),
		);
	}elseif($type=='email' || $type=='tel' || $type=='vat_number' || $type=='ssn_number' || $type=='shipping_method' || $type=='tracking_number')
	{
		$fields=array(
			array(
				'label'=>__('Title','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>$type.'_label',
			),
			array(
				'label'=>__('Font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>$type,
				'unit'=>'px',
				//'width'=>'49%',
			),
			/*array(
				'label'=>__('Text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_change',
				'width'=>'49%',
				'float'=>'right',
			), */
			array(
				'label'=>__('Text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>$type,
				'event_class'=>'wf_cst_click',
			),
		);
	}elseif($type=='product_table')
	{
		$fields=array(
			array(
				'label'=>__('Table head background','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'background-color|border-color|border-color',
				'trgt_elm'=>'product_table_head_bg|product_table_head th|product_table_head_bg td',
				'event_class'=>'wf_cst_click',
			),
			array(
				'label'=>__('Table head text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>'table_head_color',
				'event_class'=>'wf_cst_click',
			),
			array(
				'label'=>__('Table body text color','wf-woocommerce-packing-list'),
				'type'=>'color',
				'css_prop'=>'color',
				'trgt_elm'=>'table_body_color',
				'event_class'=>'wf_cst_click',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_image',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Image label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_image',
				'width'=>'44%',
			),
			array(
				'label'=>__('Image text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_image',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_sku',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('SKU label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_sku',
				'width'=>'44%',
			),
			array(
				'label'=>__('SKU text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_sku',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_product',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Product label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_product',
				'width'=>'44%',
			),
			array(
				'label'=>__('Product text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_product',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_quantity',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Qty label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_quantity',
				'width'=>'44%',
			),
			array(
				'label'=>__('Qty text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_quantity',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_price',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Price label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_price',
				'width'=>'44%',
			),
			array(
				'label'=>__('Price text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_price',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_tax',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Tax label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_tax',
				'width'=>'44%',
			),
			array(
				'label'=>__('Tax text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_tax',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_total_weight',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Total weight label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_total_weight',
				'width'=>'44%',
			),
			array(
				'label'=>__('Total weight text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_total_weight',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
			array(
				'label'=>'&nbsp;',
				'type'=>'checkbox',
				'trgt_elm'=>'product_table_head_total_price',
				'event_class'=>'wf_cst_toggler',
				'width'=>'10%',
			),
			array(
				'label'=>__('Total label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_head_total_price',
				'width'=>'44%',
			),
			array(
				'label'=>__('Total text align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'product_table_head_total_price',
				'event_class'=>'wf_cst_change',
				'width'=>'44%',
				'float'=>'right',
			),
		);
	}elseif($type=='signature')
	{
		$fields=array(
			array(
				'label'=>__('Signature type','wf-woocommerce-packing-list'),
				'type'=>'select',
				'event_class'=>'wf_cst_switcher',
				'select_options'=>array(
					'manual_signature'=>__('Manual Signature','wf-woocommerce-packing-list'),
					'image_signature_box'=>__('Image Signature','wf-woocommerce-packing-list'),
				),
			),
			array(
				'label'=>__('Title','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'signature_label',
			),
			array(
				'label'=>__('Font size','wf-woocommerce-packing-list'),
				'type'=>'text_inputgrp',
				'css_prop'=>'font-size',
				'trgt_elm'=>'signature_label',
				'unit'=>'px',
				'width'=>'49%',
			),
			array(
				'label'=>__('Align','wf-woocommerce-packing-list'),
				'type'=>'select',
				'select_options'=>$wf_text_align_arr,
				'css_prop'=>'text-align',
				'trgt_elm'=>'signature',
				'event_class'=>'wf_cst_change',
				'width'=>'49%',
				'float'=>'right',
			)
		);
	}
	elseif($type=='product_table_subtotal' || $type=='product_table_shipping' || $type=='product_table_cart_discount'
 || $type=='product_table_order_discount' || $type=='product_table_total_tax' || $type=='product_table_fee'
  || $type=='product_table_payment_method' || $type=='product_table_payment_total' || $type=='product_table_coupon')
	{
		$fields=array(			
			array(
				'label'=>__('Label','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>$type.'_label',
			),
		);
	}
	elseif($type=='product_table_payment_summary')
	{

		$fields=array(			
			array(
				'label'=>__('Sub Total','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_subtotal_label',
				'width'=>'49%',
			),
			array(
				'label'=>__('Shipping','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_shipping_label',
				'width'=>'49%',
				'float'=>'right',
			),
			array(
				'label'=>__('Cart Discount','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_cart_discount_label',
				'width'=>'49%',
			),
			array(
				'label'=>__('Order Discount','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_order_discount_label',
				'width'=>'49%',
				'float'=>'right',
			),
			array(
				'label'=>__('Total Tax','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_total_tax_label',
				'width'=>'49%',
			),
			array(
				'label'=>__('Fee','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_fee_label',
				'width'=>'49%',
				'float'=>'right',
			),
			array(
				'label'=>__('Payment Method','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_payment_method_label',
				'width'=>'49%',
			),
			array(
				'label'=>__('Total','wf-woocommerce-packing-list'),
				'css_prop'=>'html',
				'trgt_elm'=>'product_table_payment_total_label',
				'width'=>'49%',
				'float'=>'right',
			),
		);
	}

	//an informational text
	$info_text='';
	$info_text=apply_filters('wf_pklist_alter_customize_info_text',$info_text,$type,$template_type);
	echo '<div class="wf_side_panel_info_text">'.$info_text.'</div>';
	$fields=apply_filters('wf_pklist_alter_customize_inputs',$fields,$type,$template_type);
	if(count($fields)>0)
	{
		Wf_gen_customize_form_field($fields);
	}
}
foreach($customizable_items as $key=>$label)
{
	$expndble=in_array($key,$non_options_fields) ? false : true;
	$toggle=in_array($key,$non_disable_fields) ? false : true;
	$non_customizable=in_array($key,$non_customizable_items) ? true : false;
	Wf_Woocommerce_Packing_List_Customizer::envelope_customize_hdblock($key,$label,$expndble,$toggle,$non_customizable);
	Wf_get_customize_panel_html($key,$template_type);
	Wf_Woocommerce_Packing_List_Customizer::envelope_customize_ftblock($expndble);
}
?>


