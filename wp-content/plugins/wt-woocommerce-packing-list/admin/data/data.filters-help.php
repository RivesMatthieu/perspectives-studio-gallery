<?php 
$wf_filters_help_doc=array(
	'wf_pklist_alter_order_date'=> array(
		'description'=>'Alter order date',
		'params'=>'$order_date, $template_type, $order',
		'function_name'=>'wt_pklist_change_order_date_format',
		'function_code'=>'
			/* new date format */ <br />
			return <i class={inbuilt_fn}>date</i>("Y-m-d",strtotime(<span class={prms_css}>$order_date</span>)); <br />
		',
	),
	'wf_pklist_alter_invoice_date'=> array(
		'description'=>'Alter invoice date',
		'params'=>'$invoice_date, $template_type, $order',
		'function_name'=>'wt_pklist_change_invoice_date_format',
		'function_code'=>'
			/* new date format */ <br />
			return <i class={inbuilt_fn}>date</i>("M d Y",strtotime(<span class={prms_css}>$invoice_date</span>)); <br />
		',
	),
	'wf_pklist_alter_dispatch_date'=> array(
		'description'=>'Alter dispatch date',
		'params'=>'$dispatch_date, $template_type, $order',
		'function_name'=>'wt_pklist_change_dispatch_date_format',
		'function_code'=>'
			/* new date format */ <br />
			return <i class={inbuilt_fn}>date</i>("d - M - y",strtotime(<span class={prms_css}>$dispatch_date</span>)); <br />
		',
	),
	'wf_pklist_alter_barcode_data'=> array(
		'description'=>'Alter barcode information',
		'params'=>'$invoice_number, $template_type, $order',
		'function_name'=>'wt_pklist_order_number_in_barcode',
		'function_code'=>'
			/* order number in barcode */ <br />
			return $order->get_order_number();<br />
		',
	),
	'wf_pklist_add_additional_info'=> array(
		'description'=>'Add additional info',
		'params'=>'$additional_info, $template_type, $order',
		'function_name'=>'wt_pklist_add_additional_data',
		'function_code'=>'
			$additional_info.=\'Additional text\';<br />
			return $additional_info;<br />
		',
	),
	'wf_pklist_alter_subtotal'=> array(
		'description'=>'Alter subtotal',
		'params'=>'$sub_total, $template_type, $order',
		'function_name'=>'wt_pklist_alter_sub',
		'function_code'=>'
			$sub_total=\'New subtotal\';<br />
			return $sub_total;<br />
		'
	),
	'wf_pklist_alter_subtotal_formated'=> array(
		'description'=>'Alter formated subtotal',
		'params'=>'$sub_total_formated, $template_type, $sub_total, $order',
		'function_name'=>'wt_pklist_alter_formated_sub',
		'function_code'=>'
			$sub_total_formated=\'New formatted subtotal\';<br />
			return $sub_total_formated;<br />
		'
	),
	'wf_pklist_alter_shipping_method'=> array(
		'description'=>'Alter shipping method',
		'params'=>'$shipping, $template_type, $order',
		'function_name'=>'wt_pklist_alter_ship_method',
		'function_code'=>'
			$shipping=\'New shipping method\';<br />
			return $shipping;<br />'
	),
	'wf_pklist_alter_fee'=> array(
		'description'=>'Alter fee',
		'params'=>'$fee_detail_html, $template_type, $fee_detail, $user_currency, $order',
		'function_name'=>'wt_pklist_new_fee',
		'function_code'=>'
			$fee_detail_html=\'New Fee\';<br />
			return $fee_detail_html;<br />'
	),
	'wf_pklist_alter_total_fee'=> array(
		'description'=>'Alter total fee',
		'params'=>'$fee_total_amount_formated, $template_type, $fee_total_amount, $user_currency, $order',
		'function_name'=>'wt_pklist_new_formated_fee',
		'function_code'=>'
			$fee_total_amount_formated=\'New Formated Fee\';<br />
			return $fee_total_amount_formated;<br />'
	),
	'wf_pklist_alter_total_price'=> array(
		'description'=>'Alter total price',
		'params'=>'$total_price, $template_type, $order',
		'function_name'=>'wt_pklist_alter_total_price',
		'function_code'=>'
			$total_price=\'New Price\';<br />
			return $total_price;<br />
		',
	),
	'wf_pklist_alter_total_price_in_words'=> array(
		'description'=>'Alter total price in words',
		'params'=>'$total_in_words, $template_type, $order',
		'function_name'=>'wt_pklist_alter_total_price_in_words',
		'function_code'=>'
			$total_in_words=\'Price in words: \'.$total_in_words;<br />
			return $total_in_words;<br />
		',
	),
	'wf_pklist_alter_tax_inclusive_text'=> array(
		'description'=>'Alter inclusive tax text.',
		'params'=>'$incl_tax_text, $template_type, $order'
	),
	'wf_pklist_alter_refund_html'=> array(
		'description'=>'Alter refund data.',
		'params'=>'$refund_formated, $template_type, $refund_amount, $order',
		'function_name'=>'wt_pklist_alter_total_price_in_words',
	),
	'wf_pklist_alter_product_table_head'=> array(
		'description'=>'Alter product table head.(Add, remove, change order)',
		'params'=>'$columns_list_arr, $template_type, $order',
		'function_name'=>'wt_pklist_alter_product_columns',
		'function_code'=>'
			/* removing image column */ <br />
			unset($columns_list_arr[\'image\']); <br /><br />

			/* adding a new custom column with text align right */ <br />
			$columns_list_arr[\'new_col\']=\'&lt;th class=&quot;wfte_product_table_head_new_col wfte_text_right&quot; col-type=&quot;new_col&quot;&gt;__[New column]__&lt;/th&gt;\'; <br />
			<br />

			return $columns_list_arr;<br />
		',
	),
	'wf_pklist_alter_package_product_name'=> array(
		'description'=>'Alter product name in product (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$item_name, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_alter_product_name',
	),
	'wf_pklist_add_package_product_variation'=> array(
		'description'=>'Add product variation in product (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$item_meta, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_add_meta',
	),
	'wf_pklist_add_package_product_meta'=> array(
		'description'=>'Add product meta in product table (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$addional_product_meta, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_add_product_meta',
	),
	'wf_pklist_alter_package_item_quantiy'=> array(
		'description'=>'Alter item quantity in product table (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$item_quantity, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_package_item_quantiy',
		'function_code'=>'
			$item_quantity=\'New quantity\';<br />
			return $item_quantity;<br />',
	),
	'wf_pklist_alter_package_item_total_weight'=> array(
		'description'=>'Alter total weight in product table (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$item_weight, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_package_item_weight',
		'function_code'=>'
			$item_weight=\'New weight\';<br />
			return $item_weight;<br />',
	),
	'wf_pklist_alter_package_item_total'=> array(
		'description'=>'Alter item total in product table (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$product_total, $template_type, $_product, $item, $order',
		'function_name'=>'wt_pklist_alter_item_total',
		'function_code'=>'
			$product_total=\'New total price\';<br />
			return $product_total;<br />',
	),
	'wf_pklist_package_product_table_additional_column_val'=> array(
		'description'=>'You can add additional column head via `wf_pklist_alter_product_table_head` filter. You need to add column data via this filter. (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$column_data, $template_type, $columns_key, $_product, $item, $order',
		'function_name'=>'wt_pklist_package_add_custom_col_vl',
		'function_code'=>'				
			if($columns_key==\'new_col\')<br />
			{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp; $column_data=\'Column data\'; <br />
			}<br />
			return $column_data;<br />
		',
	),
	'wf_pklist_alter_package_product_table_columns'=> array(
		'description'=>'Alter product table column. (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>'$product_row_columns, $template_type, $_product, $item, $order'
	),
	'wf_pklist_alter_product_name'=> array(
		'description'=>'Alter product name. (Works with Invoice and Dispatch label only)',
		'params'=>'$order_item_name, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_new_prodct_name',
	),
	'wf_pklist_add_product_variation'=> array(
		'description'=>'Add product variation. (Works with Invoice and Dispatch label only)',
		'params'=>'$item_meta, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_prodct_varition',
	),
	'wf_pklist_add_product_meta'=> array(
		'description'=>'Add product meta. (Works with Invoice and Dispatch label only)',
		'params'=>'$addional_product_meta, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_prodct_meta',
	),
	'wf_pklist_alter_item_quantiy'=> array(
		'description'=>'Alter item quantity. (Works with Invoice and Dispatch label only)',
		'params'=>'$order_item_qty, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_item_qty',
		'function_code'=>'
			$order_item_qty=\'New item quantity\';<br />
			return $order_item_qty;<br />',
	),
	'wf_pklist_alter_item_price'=> array(
		'description'=>'Alter item price. (Works with Invoice and Dispatch label only)',
		'params'=>'$item_price, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_item_price',
		'function_code'=>'
			$item_price=\'New item price\';<br />
			return $item_price;<br />',
	),
	'wf_pklist_alter_item_price_formated'=> array(
		'description'=>'Alter formated item price. (Works with Invoice and Dispatch label only)',
		'params'=>'$item_price_formated, $template_type, $item_price, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_item_price_formatted',
		'function_code'=>'
			$item_price_formated=\'New item formatted price\';<br />
			return $item_price_formated;<br />',
	),
	'wf_pklist_alter_item_total'=> array(
		'description'=>'Alter item total. (Works with Invoice and Dispatch label only)',
		'params'=>'$product_total, $template_type, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_item_total',
		'function_code'=>'
			$product_total=\'New product total\';<br />
			return $product_total;<br />'
	),
	'wf_pklist_alter_item_total_formated'=> array(
		'description'=>'Alter formated item total. (Works with Invoice and Dispatch label only)',
		'params'=>'$product_total_formated, $template_type, $product_total, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_item_total_formatted',
		'function_code'=>'
			$product_total_formated=\'New product total formatted\';<br />
			return $product_total_formated;<br />'
	),
	'wf_pklist_product_table_additional_column_val'=> array(
		'description'=>'You can add additional column head via `wf_pklist_alter_product_table_head` filter. You need to add column data via this filter. (Works with Invoice and Dispatch label only)',
		'params'=>'$column_data, $template_type, $columns_key, $_product, $order_item, $order',
		'function_name'=>'wt_pklist_add_custom_col_vl',
		'function_code'=>'				
			if($columns_key==\'new_col\')<br />
			{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp; $column_data=\'Column data\'; <br />
			}<br />
			return $column_data;<br />
		',
	),
	'wf_pklist_alter_product_table_columns'=> array(
		'description'=>'Alter product table column. (Works with Invoice and Dispatch label only)',
		'params'=>'$product_row_columns, $template_type, $_product, $order_item, $order'
	),
	'wf_pklist_tracking_data_key'=> array(
		'description'=>'Alter tracking data key',
		'params'=>'$tracking_key, $template_type, $order',
		'function_name'=>'wt_pklist_track_key',
		'function_code'=>'
			$tracking_key=\'new_tracking_key\';<br />
			return $tracking_key;<br />'
	),
	'wf_pklist_alter_tracking_details'=> array(
		'description'=>'Alter tracking data',
		'params'=>'$tracking_details, $template_type, $order'
	),
	'wf_pklist_alter_additional_fields'=> array(
		'description'=>'Alter additional fields',
		'params'=>'$extra_fields, $template_type, $order',
		'function_name'=>'wt_pklist_add_extra_fields',
		'function_code'=>'
			/* To unset existing field */ <br />
			if(!empty($extra_fields[\'field_name\']))<br/>
			{<br/>
				unset($extra_fields[\'field_name\']);<br/>
			} <br /><br />

			/* add a new field  */ <br />
			$extra_fields[\'new_field\']=\'new field value\';<br /><br />
			return $extra_fields;<br />',
	),
	'wf_pklist_order_additional_item_meta'=> array(
		'description'=>'Alter additional item meta',
		'params'=>'$order_item_meta_data, $template_type, $order',
		'function_name'=>'wf_pklist_add_order_meta',
		'function_code'=>'			
			/* get post meta */<br/>
			$order_id = $order->get_id(); <br/>
			$meta=get_post_meta($order_id, \'_meta_key\', true);<br/>
			$order_item_meta_data=$meta;<br />
			return $order_item_meta_data;<br />'
	),
	'wf_pklist_alter_shipping_address'=> array(
		'description'=>'Alter shipping address',
		'params'=>'$shipping_address, $template_type, $order',
		'function_name'=>'wt_pklist_alter_shipping_addr',
		'function_code'=>'
			/* To unset existing field */ <br />
			if(!empty($shipping_address[\'field_name\']))<br/>
			{<br/>
				unset($shipping_address[\'field_name\']);<br/>
			} <br /><br />

			/* add a new field shipping address */ <br />
			$shipping_address[\'new_field\']=\'new field value\';<br /><br />
			return $shipping_address;<br />',
	),
	'wf_pklist_alter_billing_address'=> array(
		'description'=>'Alter billing address',
		'params'=>'$billing_address, $template_type, $order',
		'function_name'=>'wt_pklist_alter_billing_addr',
		'function_code'=>'
			/* To unset existing field */ <br />
			if(!empty($billing_address[\'field_name\']))<br/>
			{<br/>
				unset($billing_address[\'field_name\']);<br/>
			} <br /><br />

			/* add a new field billing address */ <br />
			$billing_address[\'new_field\']=\'new field value\';<br /><br />
			return $billing_address;<br />'
	),
	'wf_pklist_alter_shipping_from_address'=> array(
		'description'=>'Alter shipping from address',
		'params'=>'$fromaddress, $template_type, $order',
		'function_name'=>'wt_pklist_alter_from_addr',
		'function_code'=>'
			/* To unset existing field */ <br />
			if(!empty($fromaddress[\'field_name\']))<br/>
			{<br/>
				unset($fromaddress[\'field_name\']);<br/>
			} <br /><br />

			/* add a new field from address */ <br />
			$fromaddress[\'new_field\']=\'new field value\';<br /><br />
			return $fromaddress;<br />'
	),
	'wf_pklist_alter_shipping_return_address'=> array(
		'description'=>'Alter shipping return address',
		'params'=>'$returnaddress, $template_type, $order'
	),
	'wf_pklist_alter_meta_value'=> array(
		'description'=>'Alter meta data',
		'params'=>'$meta_value, $meta_data, $meta_key',
		'function_name'=>'wt_pklist_alter_meta',
		'function_code'=>'
			$meta_value=\'New meta value\';<br />
			return $meta_value;<br />',
	),
	'wf_pklist_toggle_received_seal'=> array(
		'description'=>'Hide/Show received seal in invoice.',
		'params'=>'$is_enable_received_seal, $template_type, $order',
		'function_name'=>'wt_pklist_toggle_received_seal',
		'function_code'=>'
			/* hide or show received seal */  <br />
			if($order->get_status()==\'refunded\')<br />
			{ <br />
			&nbsp;&nbsp;&nbsp;&nbsp; return false;  <br />
			}<br />
			return true; <br />
		',
	),
	'wf_pklist_received_seal_extra_text'=> array(
		'description'=>'Add extra text in received seal.',
		'params'=>'$extra_text, $template_type, $order',
		'function_name'=>'wt_pklist_received_seal_extra_text',
		'function_code'=>'
			/* add invoice date in received seal */  <br />
			$order_id=$order->get_id();  <br />
			$invoice_date=get_post_meta($order_id, \'_wf_invoice_date\', true);  <br />
			if($invoice_date)   <br />
			{   <br />
				&nbsp;&nbsp;&nbsp;&nbsp; return \'&lt;br /&gt;\'.<i class={inbuilt_fn}>date</i>(\'Y-m-d\',$invoice_date);  <br />
			} <br />
			return \'\'; <br />
		',
	),
	'wf_pklist_alter_hide_empty'=> array(
		'description'=>'You can add any custom placeholder in your template. You can add that placeholder key via this filter to hide it, while it\'s value is empty.',
		'params'=>'$hide_on_empty_fields, $template_type',
		'function_name'=>'wt_pklist_alter_hide_empty_element',
		'function_code'=>'
			/* To remove an element from the list */<br/>
			unset($hide_on_empty_fields[\'element_name\']);<br/><br/>

			/* add an element to the list */<br/>
			$hide_on_empty_fields[]=\'new_element_name\';<br /><br />
			return $hide_on_empty_fields;<br />',
	),
	'wf_pklist_alter_template_html'=> array(
		'description'=>'Alter template HTML before printing.',
		'params'=>'$html, $template_type',
		'function_name'=>'wt_pklist_add_custom_css_in_invoice_html',
		'function_code'=>'
			/* add cutsom css in invoice */  <br />
			if($template_type==\'invoice\')<br />
			{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp; $html.=\'&lt;style type=&quot;text/css&quot;&gt; body{ font-weight:bold; } &lt;/style&gt;\'; <br />
			}<br />
			return $html;<br />
		',
	),
	'wf_pklist_alter_find_replace'=> array(
		'description'=>'You can add any custom placeholder in your template. You can set your placeholder\'s value via this filter.',
		'params'=>'$find_replace, $template_type, $order, $box_packing, $order_package, $html',
		'function_name'=>'wt_pklist_add_custom_placeholder_value',
		'function_code'=>'
			/* add cutsom placeholder value */  <br />
			$find_replace[\'[wfte_my_placeholder]\']=\'Custom value for my placeholder\';
			<br />
			return $find_replace;<br />
		',
	),
	'wf_pklist_alter_addresslabel_extradata'=> array(
		'description'=>'Add extra data to address label.',
		'params'=>'$wfte_addresslabel_extradata, $order',
		'function_name'=>'wt_pklist_alter_addlabel',
		'function_code'=>'
		$wfte_addresslabel_extradata=\'Extra info to addresslabel\';<br />
		return $wfte_addresslabel_extradata;<br />'
	),
	'wf_pklist_label_keep_dimension'=> array(
		'description'=>'Shipping & Address Labels: The customization preference is always given to the number of items in a row by default compared to the dimensions. So the system will override the dimensions to accomdate the number of items thereby shrinking the labels. This filter allows you to prioritize the label dimension and override the default behaviour.',
		'params'=>'$keep_label_dimension, $template_type',
		'function_name'=>'wt_pklist_keep_dimension',
		'function_code'=>'
			/* keep dimension of labels when paper size is small or big  */  <br />				
			return true;<br />
		',
	),
	'wf_pklist_include_box_name_in_packinglist'=> array(
		'description'=>'Add/Remove box name in packing slip',
		'params'=>'$box_name, $box_details, $order',
		'function_name'=>'wt_pklist_box_name',
		'function_code'=>'
			$box_name=\' Enter the box name\'; <br />				
			return $box_name;<br />
		',
	),
	'wf_pklist_alter_pdf_file_name'=> array(
		'description'=>'Alter PDF/print file name.',
		'params'=>'$name, $template_type, $order_ids',
		'function_name'=>'wt_pklist_box_name',
		'function_code'=>'
			$name=\' Enter the pdf name\'; <br />				
			return $name;<br />',
	),
	'wf_pklist_alter_order_number'=> array(
		'description'=>'Alter order number.',
		'params'=>'$order_number,$template_type,$order',
		'function_name'=>'wt_pklist_order_number',
		'function_code'=>'
			$order_number=\' Enter the new order number\'; <br />				
			return $order_number;<br />',
	),
	'wf_pklist_alter_taxitem_label'=> array(
		'description'=>'Alter tax item label.',
		'params'=>'$tax_label,$template_type,$order'
	),
	'wf_pklist_alter_package_order_items'=> array(
		'description'=>'Alter order package items',
		'params'=>'$order_package, $template_type, $order'
	),
	'wf_pklist_package_product_tbody_html'=> array(
		'description'=>'Alter product table body. (Works with Packing List, Shipping Label and Delivery note only)',
		'params'=>' $html, $columns_list_arr, $template_type, $order, $box_packing, $order_package'
	),
	'wf_pklist_alter_order_grouping_row_text'=> array(
		'description'=>'Alter grouping row text.',
		'params'=>' $order_info_arr, $item, $template_type'
	),
	'wf_pklist_alter_category_row_html'=> array(
		'description'=>'Alter category row html.',
		'params'=>' $order_info_arr, $item, $template_type'
	),
	'wf_pklist_alter_order_items'=> array(
		'description'=>'Alter the order items.(Works with Invoice and Dispatch label only)',
		'params'=>' $order_items, $template_type, $order'
	),
	'wf_pklist_alter_product_meta'=> array(
		'description'=>'Alter product meta.(Works with Invoice and Dispatch label only)',
		'params'=>' $meta_info_arr,$template_type,$_product,$order_item,$order',
	),
	'wf_pklist_alter_item_individual_tax'=> array(
		'description'=>'Alter individual tax column.(Works with Invoice and Dispatch label only)',
		'params'=>' $tax_val,$template_type,$tax_id,$order_item,$order'
	),
	'wf_pklist_alter_item_tax'=> array(
		'description'=>'Alter individual tax item.(Works with Invoice and Dispatch label only)',
		'params'=>' $item_tax,$template_type,$_product,$order_item,$order'
	),
	'wf_pklist_alter_item_tax_formated'=> array(
		'description'=>'Alter individual tax item formated.(Works with Invoice and Dispatch label only)',
		'params'=>' $item_tax_formated,$template_type,$item_tax,$_product,$order_item,$order'
	),
	'wf_pklist_alter_weight'=> array(
		'description'=>'Alter the product weight.',
		'params'=>' $weight_data, $total_weight, $order'
	),
	'wf_pklist_modify_meta_data'=> array(
		'description'=>'Alter the meta data.',
		'params'=>' $meta_data'
	),
	'wf_alter_line_item_variation_data'=> array(
		'description'=>'Alter the variation data.',
		'params'=>' $current_item, $meta_data, $id, $value'
	),
	'wf_pklist_alter_dummy_data_for_customize'=> array(
		'description'=>'Alter the dummy data for customizer.',
		'params'=>' $find_replace, $template_type, $html'
	),
	'wf_pklist_alter_settings'=> array(
		'description'=>'Alter the settings array.',
		'params'=>'$settings,$base_id',
		'function_name'=>'wt_pklist_alter_setting',
		'function_code'=>'
			
			/* To remove a setting from the list */<br/>
			unset($settings[\'setting_name\']);<br/><br/>
			
			/* add new setting to the list */<br/>
			$settings[\'new_setting_name\']=\'new default value\';<br/><br/>			

			return $settings;<br />',
	),
	'wf_pklist_alter_package_grouped_order_items'=> array(
		'description'=>'Alter the grouped order items.',
		'params'=>'$item_arr, $grouping_config, $order_package, $template_type, $order'
	),
	'wf_pklist_alter_grouping_term_names'=> array(
		'description'=>'Alter the grouping term name array.',
		'params'=>'$term_name_arr, $id, $template_type, $order'
	),
	'wf_pklist_add_custom_css'=> array(
		'description'=>'Add custom css.',
		'params'=>'$custom_css, $template_type, $template_for_pdf',
		'function_name'=>'wt_pklist_add_custom_css_in_invoice_html',
		'function_code'=>'
			/* add cutsom css for pdf in invoice */  <br />
			if($template_type==\'invoice\')<br />
			{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if($template_for_pdf)<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $custom_css.=\' body{ font-weight:bold !important; } \'; <br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
			}<br />
			return $custom_css;<br />
		',
	),
	'wf_pklist_alter_print_css'=> array(
		'description'=>'Alter print css.',
		'params'=>'$print_css, $template_type, $template_for_pdf',
		'function_name'=>'wt_pklist_add_custom_css_in_invoice_html',
		'function_code'=>'
			/* add cutsom css in invoice */  <br />
			if($template_type==\'invoice\')<br />
			{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp; $print_css.=\' body{ font-weight:bold !important; } \'; <br />
			}<br />
			return $print_css;<br />
		',

	),
);