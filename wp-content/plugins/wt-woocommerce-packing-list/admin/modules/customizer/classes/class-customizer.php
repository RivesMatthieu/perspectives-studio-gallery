<?php
/**
 * Necessary functions for customizer module
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */

if (!defined('ABSPATH')) {
    exit;
}
class Wf_Woocommerce_Packing_List_CustomizerLib
{
	const TO_HIDE_CSS='wfte_hidden';
	public static $reference_arr=array();
	public static function get_order_number($order,$template_type)
	{
		$order_number=$order->get_order_number();
		return apply_filters('wf_pklist_alter_order_number', $order_number, $template_type, $order);
	}

	/**
	* @since 4.0.3
	* get documnted generated date
	*/
	public static function get_printed_on_date($html)
	{
		$printed_on_format=self::get_template_html_attr_vl($html, 'data-printed_on-format', 'm/d/Y');
		return date($printed_on_format);
	}

	public static function set_order_data($find_replace,$template_type,$html,$order=null)
	{
		if(!is_null($order))
        {
        	$wc_version=WC()->version;
			$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();

			$find_replace['[wfte_order_number]']=self::get_order_number($order,$template_type);
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				$find_replace['[wfte_invoice_number]']=Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,false); //do not force generate
			}else
			{
				$find_replace['[wfte_invoice_number]']='';
			}

			//order date
			$order_date_match=array();
			$order_date_format='m/d/Y';
			if(preg_match('/data-order_date-format="(.*?)"/s',$html,$order_date_match))
			{
				$order_date_format=$order_date_match[1];
			}

			$order_date=get_the_date($order_date_format,$order_id);
			$order_date=apply_filters('wf_pklist_alter_order_date', $order_date, $template_type, $order);
			$find_replace['[wfte_order_date]']=$order_date;

			//invoice date
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				$invoice_date_match=array();
				$invoice_date_format='m/d/Y';
				if(preg_match('/data-invoice_date-format="(.*?)"/s',$html,$invoice_date_match))
				{
					$invoice_date_format=$invoice_date_match[1];
				}

				//must call this line after `generate_invoice_number` call
				$invoice_date=Wf_Woocommerce_Packing_List_Invoice::get_invoice_date($order_id,$invoice_date_format,$order);
				$invoice_date=apply_filters('wf_pklist_alter_invoice_date',$invoice_date,$template_type,$order);
				$find_replace['[wfte_invoice_date]']=$invoice_date;
			}else
			{
				$find_replace['[wfte_invoice_date]']='';
			}

			//dispatch date
			$dispatch_date_match=array();
			$dispatch_date_format='m/d/Y';
			if(preg_match('/data-dispatch_date-format="(.*?)"/s',$html,$dispatch_date_match))
			{
				$dispatch_date_format=$dispatch_date_match[1];
			}
			$dispatch_date=get_the_date($dispatch_date_format,$order_id);
			$dispatch_date=apply_filters('wf_pklist_alter_dispatch_date',$dispatch_date,$template_type,$order);
			$find_replace['[wfte_dispatch_date]']=$dispatch_date;
		}
		return $find_replace;
	}
	public static function package_doc_items($find_replace,$template_type,$order,$box_packing,$order_package)
	{
		if(!is_null($box_packing))
        {
			$box_details=$box_packing->wf_packinglist_get_table_content($order,$order_package);
			
			$box_name=$box_details['name'];
			if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_package_type')=='box_packing')
			{
				$box_name=apply_filters('wf_pklist_include_box_name_in_packinglist',$box_name, $box_details, $order);
				$box_name_label=apply_filters('wf_pklist_alter_box_name_label','Box name',$template_type,$order);
				$find_replace['[wfte_box_name]']=(trim($box_name)!="" ? $box_name_label.': '.$box_name : '');
			}else
			{
				$find_replace['[wfte_box_name]']='';
			}
		}else
		{
			$find_replace['[wfte_box_name]']='';
		}
		return $find_replace;
	}

	/**
	* 	Set extra data like footer, special_notes. Modules can override these type of data
	*	@since 4.0.3
	*/
	private static function set_extra_text_data($find_replace,$data_slug,$template_type,$html,$order=null)
	{
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);

		$txt_data=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_'.$data_slug,$module_id);
		if($txt_data===false || $txt_data=='') //custom data from module not present or empty
		{
			//call main data
			$txt_data=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_'.$data_slug);
		}
		if(!is_null($order))
		{
			$txt_data=apply_filters('wf_pklist_alter_'.$data_slug.'_data', $txt_data, $template_type, $order);
		}
		$find_replace['[wfte_'.$data_slug.']']=nl2br($txt_data);
		return $find_replace;
	}

	/**
	* 	Process text data like return policy, sale terms, transport terms.
	*	@since 4.0.3
	*/
	private static function set_text_data($find_replace,$data_slug,$template_type,$html,$order=null)
	{
		$txt_data=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_'.$data_slug);
		if(!is_null($order))
		{
			$txt_data=apply_filters('wf_pklist_alter_'.$data_slug.'_data',$txt_data,$template_type,$order);
		}
		$find_replace['[wfte_'.$data_slug.']']=nl2br($txt_data);
		return $find_replace;
	}

	/**
	* 	Set other data, includes barcode, signature etc
	*	@since 4.0.0
	*	@since 4.0.2	Included total weight function, added $html argument
	*/
	public static function set_other_data($find_replace,$template_type,$html,$order=null)
	{
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);

		//return policy, sale terms, transport terms
		$find_replace=self::set_text_data($find_replace,'return_policy',$template_type,$html,$order);
		$find_replace=self::set_text_data($find_replace,'transport_terms',$template_type,$html,$order);
		$find_replace=self::set_text_data($find_replace,'sale_terms',$template_type,$html,$order);

		//footer data
		$find_replace=self::set_extra_text_data($find_replace,'footer',$template_type,$html,$order);
		
		//special notes
		$find_replace=self::set_extra_text_data($find_replace,'special_notes',$template_type,$html,$order);


		//signature	
		$signture_url=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_invoice_signature',$module_id);
		$find_replace['[wfte_signature_url]']=$signture_url;

		//barcode, additional info
		if(!is_null($order))
        {
			
			$invoice_number=Wf_Woocommerce_Packing_List_Public::module_exists('invoice') ? Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,false) : ''; 
			$invoice_number=apply_filters('wf_pklist_alter_barcode_data',$invoice_number, $template_type, $order);
			$find_replace['[wfte_barcode_url]']='';
			$find_replace['[wfte_barcode]']='';
			if($invoice_number!="" && strpos($html, '[wfte_barcode_url]') !== false)
			{
				include_once plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME).'includes/class-wf-woocommerce-packing-list-barcode_generator.php';
				$barcode_width_factor=2;
				$barcode_width_factor=apply_filters('wf_pklist_alter_barcode_width_factor',$barcode_width_factor,$template_type,$invoice_number,$order);

				$barcode_url=Wf_Woocommerce_Packing_List_Barcode_generator::generate($invoice_number, 'png', $barcode_width_factor);
				if($barcode_url)
				{
					$find_replace['[wfte_barcode_url]']=$barcode_url;
					$find_replace['[wfte_barcode]']='1'; //just a value to prevent hiding barcode
				}
			}
			$additional_info='';
			$find_replace['[wfte_additional_data]']=apply_filters('wf_pklist_add_additional_info',$additional_info,$template_type,$order);			
		}

		//set total weight
		$find_replace=self::set_total_weight($find_replace,$template_type,$html,$order);

		//prints the current date with the given format
		$find_replace['[wfte_printed_on]']=self::get_printed_on_date($html);
		return $find_replace;
	}

	/**
	*  	Set other charges fields in product table
	*	@since 	4.0.0
	*	@since 	4.0.2 refund amount calculation issue fixed. Total in words integrated. Added filter to alter total
	*/
	public static function set_extra_charge_fields($find_replace,$template_type,$html,$order=null)
	{
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);

		if(!is_null($order))
        {
        	$the_options=Wf_Woocommerce_Packing_List::get_settings($module_id);
			$order_items=$order->get_items();
			$wc_version=WC()->version;
			$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
			$user_currency=get_post_meta($order_id,'_order_currency',true);
			$sub_total=0;
			foreach($order_items as $order_item_id=>$order_item) 
			{
		    	$sub_total+=($wc_version< '2.7.0' ? $order->get_item_meta($order_item_id,'_line_total',true) : $order_item->get_total());
		    }
		    $sub_total=apply_filters('wf_pklist_alter_subtotal',$sub_total,$template_type,$order);
		    $sub_total_formated=wc_price($sub_total,array('currency'=>$user_currency));
		    $find_replace['[wfte_product_table_subtotal]']=apply_filters('wf_pklist_alter_subtotal_formated',$sub_total_formated,$template_type,$sub_total,$order);

		    //shipping method ==========================
		    if(get_option('woocommerce_calc_shipping')==='yes')
		    {
		        $shippingdetails=$order->get_items('shipping');
		        if (!empty($shippingdetails))
		        {
		            $shipping=$order->get_shipping_to_display();
		            $shipping=apply_filters('wf_pklist_alter_shipping_method',$shipping,$template_type,$order);
		            $find_replace['[wfte_product_table_shipping]']=__($shipping, 'wf-woocommerce-packing-list');
		        }else
		        {
		            $find_replace['[wfte_product_table_shipping]']='';
		        }
		    }else
		    {
		        $find_replace['[wfte_product_table_shipping]']='';
		    }

		    //cart discount ==========================
		    $cart_discount=($wc_version<'2.7.0' ? $order->cart_discount : get_post_meta($order_id,'_cart_discount',true));
		    if($cart_discount>0) 
		    {
		        $find_replace['[wfte_product_table_cart_discount]']=wc_price($cart_discount,array('currency'=>$user_currency));
			}
			else
			{
		        $find_replace['[wfte_product_table_cart_discount]']='';
			}

			//order discount ==========================
			$order_discount=($wc_version<'2.7.0' ? $order->order_discount : get_post_meta($order_id,'_order_discount',true));
			if ($order_discount>0)
			{
		        $find_replace['[wfte_product_table_order_discount]']=wc_price($order_discount,array('currency'=>$user_currency));
			}
			else
			{
		        $find_replace['[wfte_product_table_order_discount]']='';				
			}

			//tax items ==========================
			$tax_items = $order->get_tax_totals();
			$tax_type=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
			if(in_array('ex_tax',$tax_type))
			{
				//total tax ==========================
				if(is_array($tax_items) && count($tax_items)>0)
				{
					$find_replace['[wfte_product_table_total_tax]']=wc_price($order->get_total_tax(),array('currency'=>$user_currency));
				}else
				{
					$find_replace['[wfte_product_table_total_tax]']='';
				}
			}else
			{
				$find_replace['[wfte_product_table_total_tax]']='';
			}

			$tax_items_match=array();
			$tax_items_row_html=''; //row html
			$tax_items_html='';
			$tax_items_total=0;
			if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_tax_items\b[^"]*"[^>]*>(.*?)<\/tr>/s',$html,$tax_items_match))
			{
				$tax_items_row_html=isset($tax_items_match[0]) ? $tax_items_match[0] : '';
			}
			if(is_array($tax_items) && count($tax_items)>0)
			{
				foreach($tax_items as $tax_item)
				{
					if(in_array('ex_tax',$tax_type) && $tax_items_row_html!='')
					{
	                    $tax_label=apply_filters('wf_pklist_alter_taxitem_label', esc_html($tax_item->label), $template_type, $order, $tax_item);
	                    $tax_amount=wc_price($tax_item->amount, array('currency'=>$user_currency));
	                    $tax_items_html.=str_replace(array('[wfte_product_table_tax_item_label]','[wfte_product_table_tax_item]'),array($tax_label, $tax_amount),$tax_items_row_html);
	                }
	                else
	                {
	                    $tax_items_total+=$tax_item->amount;
	                }
				}
			}
			if($tax_items_row_html!='' && isset($tax_items_match[0])) //tax items placeholder exists
			{ 
				$find_replace[$tax_items_match[0]]=$tax_items_html; //replace tax items
			}

			//fee details ==========================
			$fee_details=$order->get_items('fee');
	        $fee_details_html='';
	        $fee_total_amount = 0;
	        if(!empty($fee_details))
	        {
		        foreach($fee_details as $fee_detail)
		        {
		            $fee_detail_html=wc_price($fee_detail['amount'],array('currency'=>$user_currency)).' via '.$fee_detail['name'];
		            $fee_detail_html=apply_filters('wf_pklist_alter_fee',$fee_detail_html,$template_type,$fee_detail,$user_currency,$order);
		            $fee_details_html.=($fee_detail_html!="" ? $fee_detail_html.'<br/>' : '');
		            $fee_total_amount+=$fee_detail['amount'];	            
		        }
		        $fee_total_amount_formated= wc_price($fee_total_amount,array('currency'=>$user_currency));
	        	$fee_total_amount_formated=apply_filters('wf_pklist_alter_total_fee',$fee_total_amount_formated,$template_type,$fee_total_amount,$user_currency,$order);
	        	$find_replace['[wfte_product_table_fee]']=$fee_details_html.($fee_total_amount_formated!="" ? '<br />'.$fee_total_amount_formated : '');
	    	}else
	        {
	        	$find_replace['[wfte_product_table_fee]']='';
	        }

	        //coupon details ==========================
	        $coupon_details=$order->get_items('coupon');
	        $coupon_info_arr=array();
	        $coupon_info_html='';
	        if(!empty($coupon_details))
	        {
				foreach($coupon_details as $coupon_id=>$coupon_detail)
				{
					$discount=$coupon_detail['discount_amount'];
					$discount_tax=$coupon_detail['discount_amount_tax'];
					$discount_total=$discount+$discount_tax;
					$coupon_info_arr[$coupon_detail['name']]=wc_price($discount_total,array('currency'=>$user_currency));
				}
				$coupon_code_arr=array_keys($coupon_info_arr);
				$coupon_info_html="{".implode("} , {",$coupon_code_arr)."}";
				$find_replace['[wfte_product_table_coupon]']=$coupon_info_html;
			}else
			{
				$find_replace['[wfte_product_table_coupon]']='';
			}

			//payment info ==========================
			$paymethod_title=($wc_version< '2.7.0' ? $order->payment_method_title : $order->get_payment_method_title());
        	$paymethod_title=__($paymethod_title,'wf-woocommerce-packing-list');
        	$find_replace['[wfte_product_table_payment_method]']=$paymethod_title;


        	//total amount ==========================
        	$total_price_final=($wc_version<'2.7.0' ? $order->order_total : get_post_meta($order_id,'_order_total',true));
			$total_price=$total_price_final; //taking value for future use
			$refund_amount=0;
			if($total_price_final)
			{ 
				$refund_data_arr=$order->get_refunds();
				if(!empty($refund_data_arr))
				{
					foreach($refund_data_arr as $refund_data)
					{	
						$refund_id=($wc_version< '2.7.0' ? $refund_data->id : $refund_data->get_id());
						$cr_refund_amount=(float) get_post_meta($refund_id,'_order_total',true);
						$total_price_final+=$cr_refund_amount;
						$refund_amount-=$cr_refund_amount;
					}
				}
			}			

        	$incl_tax_text=__('incl. tax','wf-woocommerce-packing-list');
        	$incl_tax_text=apply_filters('wf_pklist_alter_tax_inclusive_text',$incl_tax_text,$template_type,$order);
        	
        	//inclusive tax data      	
        	$tax_data=((in_array('in_tax', $tax_type) && !empty($tax_items_total)) ? ' ('.$incl_tax_text .wc_price($tax_items_total,array('currency'=>$user_currency)).')' : '');

        	if(!empty($refund_amount) && $refund_amount!=0) /* having refund */
			{
				$total_price_final=apply_filters('wf_pklist_alter_total_price', $total_price_final, $template_type, $order);
				
				$total_price_final_formated=wc_price($total_price_final, array('currency'=>$user_currency));

				/* price before refund */
				$total_price_formated=wc_price($total_price, array('currency'=>$user_currency));

				$refund_formated='<br /> ('.__('Refund','wf-woocommerce-packing-list').' -'.wc_price($refund_amount, array('currency'=>$user_currency)).')';
				$refund_formated=apply_filters('wf_pklist_alter_refund_html', $refund_formated, $template_type, $refund_amount, $order);

				$total_price_html='<strike>'.$total_price_formated.'</strike> '.$total_price_final_formated.$tax_data.$refund_formated;
			}else
			{
				$total_price_final=apply_filters('wf_pklist_alter_total_price',$total_price,$template_type,$order);

				$total_price_formated=wc_price($total_price_final, array('currency'=>$user_currency));

				$total_price_html=$total_price_formated.$tax_data;
			}

			/* total price in words */
			$find_replace = self::set_total_in_words($total_price_final, $find_replace, $template_type, $html, $order);

			$find_replace['[wfte_product_table_payment_total]']=$total_price_html;

		}
		return $find_replace;
	}

	/**
	*	@since 4.0.0 Generating product table
	*	@since 4.0.2 Tax column introduced 
	*	
	*/
	public static function set_product_table($find_replace,$template_type,$html,$order=null,$box_packing=null,$order_package=null)
	{
		$match=array();
		$default_columns=array('image','sku','product','quantity','price','total_price');
		$columns_list_arr=array();
		
		//extra column properties like text-align etc are inherited from table head column. We will extract that data to below array
	    $column_list_options=array();

	    $module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
	    /* checking product table markup exists  */
		if(preg_match('/\[wfte_product_table_start\](.*?)\[wfte_product_table_end\]/s',$html,$match))
		{
			$product_tb_html=$match[1];
			$thead_match=array();
			
			$th_html='';
			if(preg_match('/<thead(.*?)>(.*?)<\/thead>/s', $product_tb_html, $thead_match))
			{
				if(isset($thead_match[2]) && $thead_match[2]!="")
				{
					$thead_tr_match=array();
					if(preg_match('/<tr(.*?)>(.*?)<\/tr>/s',$thead_match[2],$thead_tr_match))
					{
						if(isset($thead_tr_match[2]))
						{
							$th_html=$thead_tr_match[2];
						}
					}
				}				
			}

			if($th_html!="")
			{
				$th_html_arr=explode('</th>',$th_html);

				$th_html_arr=array_filter($th_html_arr);
				$col_ind=0;
				foreach($th_html_arr as $th_single_html)
				{
					$th_single_html=trim($th_single_html);
					if($th_single_html!="")
					{
						$matchs=array();
						$is_have_col_id=preg_match('/col-type="(.*?)"/',$th_single_html,$matchs);
						$col_ind++;
						$col_key=($is_have_col_id ? $matchs[1] : $col_ind); //column id exists
						
						//extracting extra column options, like column text align class etc
						$extra_table_col_opt=self::extract_table_col_options($th_single_html);

						if($col_key=='tax' || $col_key=='-tax') //column key is tax then check, tax column options are enabled
						{
			            	//adding column data to arrays
							$columns_list_arr[$col_key]=$th_single_html.'</th>';
							$column_list_options[$col_key]=$extra_table_col_opt;
						}
						elseif($col_key=='tax_items' || $col_key=='-tax_items')
						{
							if(!is_null($order)) //do not show this column in customizer
        					{
								//show individual tax column
				            	$show_individual_tax_column=Wf_Woocommerce_Packing_List::get_option('wt_pklist_show_individual_tax_column',$module_id);
								if($show_individual_tax_column===false) //option not present, then add a filter to control the value
								{
									$show_individual_tax_column=apply_filters('wf_pklist_alter_show_individual_tax_column', $show_individual_tax_column, $template_type, $order);
								}

								if($show_individual_tax_column===true || $show_individual_tax_column==='Yes') 
								{
									$tax_items = $order->get_items('tax');
									$tax_id_prefix=($col_key[0]=='-' ? $col_key[0] : '').'individual_tax_';
									foreach($tax_items as $tax_item)
									{
										$tax_id=$tax_id_prefix.$tax_item->get_rate_id();
										$tax_label=$tax_item->get_label();
										$col_html=str_replace('[wfte_product_table_tax_item_column_label]',$tax_label,$th_single_html);

										//adding column data to arrays
										$columns_list_arr[$tax_id]=$col_html.'</th>';
										$column_list_options[$tax_id]=$extra_table_col_opt;
									}
								}
							}
						}
						else
						{
							//adding column data to arrays
							$columns_list_arr[$col_key]=$th_single_html.'</th>'; 
							$column_list_options[$col_key]=$extra_table_col_opt;
						}
					}
				}

				if(!is_null($order))
	    		{
	    			//filter to alter table head
					$columns_list_arr=apply_filters('wf_pklist_alter_product_table_head',$columns_list_arr,$template_type,$order);
				}
				$columns_list_arr=(!is_array($columns_list_arr) ? array() : $columns_list_arr);

				//for table head
				$columns_list_arr=apply_filters('wf_pklist_reverse_product_table_columns',$columns_list_arr,$template_type);
				
				/* update the column options according to $columns_list_arr */
				$column_list_option_modified=array();
				foreach($columns_list_arr as $column_key=>$column_data)
				{
					if(isset($column_list_options[$column_key]))
					{
						$column_list_option_modified[$column_key]=$column_list_options[$column_key];
					}else
					{
						//extracting extra column options, like column text align class etc
						$extra_table_col_opt=self::extract_table_col_options($column_data);
						$column_list_option_modified[$column_key]=$extra_table_col_opt;
					}
				}
				$column_list_options=$column_list_option_modified;
				

				//replace for table head section
				$find_replace[$th_html]=self::generate_product_table_head_html($columns_list_arr,$template_type);
				$find_replace['[wfte_product_table_start]']='';
				$find_replace['[wfte_product_table_end]']='';
			}

			//product table body section
			$tbody_tag_match=array();
			$tbody_tag='';
			if(preg_match('/<tbody(.*?)>/s',$product_tb_html,$tbody_tag_match))
			{
				self::$reference_arr['tbody_placholder']=$tbody_tag_match[0];
				if(!is_null($box_packing))
				{
					$find_replace[$tbody_tag_match[0]]=$tbody_tag_match[0].self::generate_package_product_table_product_row_html($column_list_options,$template_type,$order,$box_packing,$order_package);
				}else
				{
					$find_replace[$tbody_tag_match[0]]=$tbody_tag_match[0].self::generate_product_table_product_row_html($column_list_options,$template_type,$order);
				}
			}
		}
		return $find_replace;
	}

	/**
	* 	Extract table column style classes.
	*	@since 4.0.2
	*/
	private static function extract_table_col_options($th_single_html)
	{
		$matchs=array();
		$is_have_class=preg_match('/class="(.*?)"/',$th_single_html,$matchs);
		$option_classes=array('wfte_text_left','wfte_text_right','wfte_text_center');
		$out=array();
		if($is_have_class)
		{
			$class_arr=explode(" ",$matchs[1]);
			foreach($class_arr as $class)
			{
				if(in_array($class,$option_classes))
				{
					$out[]=$class;
				}
			}
		}
		return implode(" ",$out);
	}

	/*
	* Render product table column data for package type documents
	* 
	*/
	private static function generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr)
	{
		$html='';
		$product_row_columns=array(); //for html generation
        $product_id=($wc_version< '2.7.0' ? $_product->id : $_product->get_id());       
        
        $variation_id=($item['variation_id']!='' ? $item['variation_id']*1 : 0);
        $parent_id=wp_get_post_parent_id($variation_id);
        //$order_item_id=$item['order_item_id'];
        $dimension_unit=get_option('woocommerce_dimension_unit');
        $weight_unit = get_option('woocommerce_weight_unit');

        $order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
		$user_currency=get_post_meta($order_id,'_order_currency',true);

        foreach($columns_list_arr as $columns_key=>$columns_value)
        {
        	//$hide_it=array_key_exists($key,$columns_list_arr) ? '' : 'style="display:none;"';
            if($columns_key=='image' || $columns_key=='-image')
            {
            	$column_data=self::generate_product_image_column_data($product_id,$variation_id,$parent_id);
            }
            elseif($columns_key=='sku' || $columns_key=='-sku')
            {
            	$column_data=$_product->get_sku();
            }
            elseif($columns_key=='product' || $columns_key=='-product')
            {
            	$product_name=apply_filters('wf_pklist_alter_package_product_name',$item['name'],$template_type,$_product,$item,$order);

            	//variation data======
            	$variation='';
            	if(isset($the_options['woocommerce_wf_packinglist_variation_data']) && $the_options['woocommerce_wf_packinglist_variation_data']=='Yes')
            	{
	            	$variation=$item['variation_data'];
			        $item_meta=$item['extra_meta_details'];
			        $variation_data=apply_filters('wf_pklist_add_package_product_variation',$item_meta,$template_type,$_product,$item,$order);
			        if(!empty($variation_data) && !is_array($variation_data))
			        {
			            $variation.='<br>'.$variation_data;
			        }
			        if(!empty($variation))
			        {
			        	$variation='<br/><small style="word-break: break-word;">'.$variation.'</small>';
			        }			        
		    	}

		        //additional product meta
		        $addional_product_meta = '';
		        if(isset($the_options['wf_'.$template_type.'_product_meta_fields']) && is_array($the_options['wf_'.$template_type.'_product_meta_fields']) && count($the_options['wf_'.$template_type.'_product_meta_fields'])>0) 
		        {
		            $selected_product_meta_arr=$the_options['wf_'.$template_type.'_product_meta_fields'];
		            $product_meta_arr=Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
		            foreach($selected_product_meta_arr as $value)
		            {
		                $meta_data=get_post_meta($product_id,$value,true);
		                if($meta_data=='' && $variation_id>0)
		                {
		                	$meta_data=get_post_meta($parent_id,$value,true);
		                }
		                if(is_array($meta_data))
	                    {
	                        $output_data=(self::wf_is_multi($meta_data) ? '' : implode(', ',$meta_data));
	                    }else
	                    {
	                        $output_data=$meta_data;
	                    }
	                    $meta_info_arr=array('key'=>$value,'title'=>__($product_meta_arr[$value],'wf-woocommerce-packing-list'),'value'=>__($output_data,'wf-woocommerce-packing-list'));
	                    $meta_info_arr=apply_filters('wf_pklist_alter_package_product_meta', $meta_info_arr, $template_type, $_product, $item, $order);
                    	if(is_array($meta_info_arr) && isset($meta_info_arr['title']) && isset($meta_info_arr['value']) && $meta_info_arr['value']!="")
	                    {
	                    	$addional_product_meta.='<small>'.$meta_info_arr['title'].': '.$meta_info_arr['value'].'</small><br>';
	                    }
	                }
		        }
		        $addional_product_meta=apply_filters('wf_pklist_add_package_product_meta', $addional_product_meta, $template_type, $_product, $item, $order);
		        
		        $column_data='<b>'.$product_name.'</b>';
		        if(!empty($variation))
		        {
		        	$column_data.=$variation;
		        }
		        if(!empty($addional_product_meta))
		        {
		        	$column_data.=' <br />'.$addional_product_meta;
		        }
            }
            elseif($columns_key=='quantity' || $columns_key=='-quantity')
            {
            	$column_data=apply_filters('wf_pklist_alter_package_item_quantiy',$item['quantity'],$template_type,$_product,$item,$order);
            }
            elseif($columns_key=='total_weight' || $columns_key=='-total_weight')
            {
            	$item_weight=($item['weight']!= '') ? $item['weight']*$item['quantity'].' '.$weight_unit : __('n/a','wf-woocommerce-packing-list');
            	$column_data=apply_filters('wf_pklist_alter_package_item_total_weight', $item_weight, $template_type, $_product, $item, $order);         	
            }
            elseif($columns_key=='total_price' || $columns_key=='-total_price')
            {
            	$product_total=$item['quantity']*$item['price'];
				$total_price=apply_filters('wf_pklist_alter_package_item_total',$product_total,$template_type,$_product,$item,$order);          	
            	$product_total_formated=wc_price($total_price,array('currency'=>$user_currency));
            	$column_data=apply_filters('wf_pklist_alter_package_item_total_formated', $product_total_formated, $template_type, $product_total, $_product, $item, $order);

            }else //custom column by user
            {
            	$column_data='';
            	$column_data=apply_filters('wf_pklist_package_product_table_additional_column_val',$column_data,$template_type,$columns_key,$_product,$item,$order);
            }
            $product_row_columns[$columns_key]=$column_data;
        }
        $product_row_columns=apply_filters('wf_pklist_alter_package_product_table_columns', $product_row_columns, $template_type, $_product, $item, $order);
        $html=self::build_product_row_html($product_row_columns, $columns_list_arr);
        return $html;
	}

	/**
	* @since 4.0.3 
	* Grouping terms
	*/
	private static function get_term_data($id, $term_name_arr, $template_type, $order)
	{
		$terms=get_the_terms($id,'product_cat');
		if($terms)
		{
			foreach($terms as $term)
			{
				$term_name_arr[]=$term->name;
			}
		}
		$term_name_arr=apply_filters('wf_pklist_alter_grouping_term_names', $term_name_arr, $id, $template_type, $order);
		return $term_name_arr;
	}

	/**
	* @since 4.0.0 Render product table row HTML for package type documents
	* @since 4.0.3 Added group by order for Picklist, Compatibility for variable subscription product
	*/
	private static function generate_package_product_table_product_row_html($columns_list_arr,$template_type,$order=null,$box_packing=null,$order_package=null)
	{
		$html='';
		if(!is_null($order))
        {
        	$order_package=apply_filters('wf_pklist_alter_package_order_items', $order_package, $template_type, $order);

        	//module settings are saved under module id
			$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
			$wc_version=WC()->version;
			$the_options=Wf_Woocommerce_Packing_List::get_settings($module_id);

        	$package_type =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_package_type');
            $category_wise_split =Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting',$module_id);
            /* @since 4.0.3 only for picklist   */
            $order_wise_split =Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_order_wise_splitting',$module_id);
            if($package_type == 'single_packing' && ($category_wise_split == 'Yes'|| $order_wise_split=='Yes'))
           	{
           		/* if both are enabled we need to decide which is outer */
           		$is_category_under_order=1;
           		if($order_wise_split=='Yes' && $category_wise_split=='Yes')
	            {
	            	$is_category_under_order=apply_filters('wf_pklist_alter_groupby_is_category_under_order', $is_category_under_order, $template_type);
	            }
           		$item_arr=array();
	            foreach ($order_package as $id => $item)
	            {
	                $_product = wc_get_product($item['id']);
	                if(!$_product){ continue; }               
	                if($item['variation_id'] !='')
	                {
	                   $parent_id=wp_get_post_parent_id($item['variation_id']);
	                   $item['id']=$parent_id; 
	                }
	                $item_obj=$_product;
	                $item_obj->qty = $item['quantity'];
                    $item_obj->weight = $item['weight'];
                    $item_obj->price = $item['price'];
                    $item_obj->variation_data = $item['variation_data'];
                    $item_obj->variation_id = $item['variation_id'];
                    $item_obj->item_id = $item['id'];
                    $item_obj->name = $item['name'];
                    $item_obj->sku = $item['sku'];
                    $item_obj->order_item_id = $item['order_item_id'];
                    $item_obj->item= $item;

	                if($category_wise_split=='Yes')
	                {
	                	$terms=get_the_terms($item['id'], 'product_cat');
		                $term_name_arr=array();
		                if($terms)
		                {
		                	$term_name_arr=self::get_term_data($item['id'], $term_name_arr, $template_type, $order);

		                }else /* compatibility for variable subscription products */
						{
							if(isset($item['extra_meta_details']) && isset($item['extra_meta_details']['_product_id'])) //extra meta details available
							{
								if(is_array($item['extra_meta_details']['_product_id']))
								{
									foreach($item['extra_meta_details']['_product_id'] as $p_id)
									{
										$term_name_arr=self::get_term_data($p_id, $term_name_arr, $template_type, $order);
									}
								}else
								{
									$p_id=(int) $item['extra_meta_details']['_product_id'];
									if($p_id>0)
									{
										$term_name_arr=self::get_term_data($p_id, $term_name_arr, $template_type, $order);
									}
								}
							}
						}

						//adding empty value if no term found
						$term_name_arr=(count($term_name_arr)==0 ? array('--') : $term_name_arr);

	                	$term_name=implode(", ",$term_name_arr);
	                	if($order_wise_split=='Yes')
	                	{
	                		$order_text=self::order_text_for_product_table_grouping_row($item, $template_type);
	                		if($is_category_under_order==1)
	                		{
	                			if(!isset($item_arr[$order_text]))
								{
									$item_arr[$order_text]=array();
								}
								if(!isset($item_arr[$order_text][$term_name]))
								{
									$item_arr[$order_text][$term_name]=array();
								}

	                			$item_arr[$order_text][$term_name][]=$item_obj;
	                		}else
	                		{
	                			if(!isset($item_arr[$term_name]))
								{
									$item_arr[$term_name]=array();
								}
								if(!isset($item_arr[$term_name][$order_text]))
								{
									$item_arr[$term_name][$order_text]=array();
								}
								
	                			$item_arr[$term_name][$order_text][]=$item_obj;
	                		}
	                	}else
	                	{

	                		if(!isset($item_arr[$term_name]))
							{
								$item_arr[$term_name]=array();
							}

	                		//avoiding duplicate row of products (Picklist)
	                		if($template_type=='picklist') //not need a checking, but for perfomance and security
	                		{
	                			$product_id=($item['variation_id'] !='' ? $item['variation_id'] : $item['id']);
	                			if(isset($item_arr[$term_name][$product_id])) //already added then increment the quantity
	                			{
	                				$cr_item=$item_arr[$term_name][$product_id];
	                				$new_quantity=((int) $cr_item->qty) + ((int) $item_obj->qty);
	                				$cr_item->qty=$new_quantity;
	                				$cr_item->item['quantity']=$new_quantity;
	                				$item_obj=$cr_item;
	                			} 
	                			$item_arr[$term_name][$product_id]=$item_obj;		                			
	                		}else
	                		{
                        		$item_arr[$term_name][]=$item_obj;
                        	}
                    	}
	                }else
	                {
	                	$order_text=self::order_text_for_product_table_grouping_row($item,$template_type);
	                	$item_arr[$order_text][]=$item_obj;
	                }
	            }

	            $item_arr=apply_filters('wf_pklist_alter_package_grouped_order_items', $item_arr, array('order'=>$order_wise_split, 'category'=>$category_wise_split), $order_package, $template_type, $order);	            

	            $total_column=count($columns_list_arr);
	            if($order_wise_split=='Yes' && $category_wise_split=='Yes')
	            {
	            	foreach($item_arr as $key=>$val_arr)
            		{
		            	$html.=self::get_product_table_grouping_row($is_category_under_order, 1, $key, $total_column, $template_type);
            			foreach($val_arr as $val_key=>$val)
            			{
            				$html.=self::get_product_table_grouping_row($is_category_under_order, 2, $val_key, $total_column, $template_type);
			            	foreach($val as $cat_ind=>$cat_data) 
			            	{
			            		// get the product; if this variation or product has been deleted, this will return null...
					    		$_product=$cat_data;
					    		$item=$cat_data->item;
					    		if($_product)
					    		{
					    			$html.=self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr);
					    		}
			            	}
            			}
            		}
	            }else
	            {
	            	foreach($item_arr as $val_key=>$val)
        			{
        				$is_group_by_cat=($category_wise_split=='Yes' ? 1 : 0);
        				$html.=self::get_product_table_grouping_row($is_group_by_cat, 2, $val_key, $total_column, $template_type);
		            	foreach($val as $cat_ind=>$cat_data) 
		            	{
		            		// get the product; if this variation or product has been deleted, this will return null...
				    		$_product=$cat_data;
				    		$item=$cat_data->item;
				    		if($_product)
				    		{
				    			$html.=self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr);
				    		}
		            	}
        			}
	            }
           	}else
           	{
           		if($package_type == 'single_packing' && $template_type=='picklist') /* remove the duplicates and increase the quantity. not need a template type checking, but for perfomance and security */
           		{
           			$item_arr=array();
           			foreach ($order_package as $id => $item)
					{	            		
						$product_id=($item['variation_id'] !='' ? $item['variation_id'] : $item['id']);
						if(isset($item_arr[$product_id])) //already added then increment the quantity
						{
							$cr_item=$item_arr[$product_id];
							$item_arr[$product_id]['quantity']=((int) $cr_item['quantity']) + ((int) $item['quantity']);
						}else
						{
							$item_arr[$product_id]=$item;
						}
	            	}
	            	$order_package=$item_arr;
           		}

           		foreach($order_package as $id => $item)
	            {	            	
	            	$_product = wc_get_product($item['id']);                
	                if($item['variation_id'] !='')
	                {
	                   $parent_id=wp_get_post_parent_id($item['variation_id']);
	                   $item['id']=$parent_id; 
	                }
	                if($_product)
				    {
	            		$html.=self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr);
	            	}
	            }
           	}
           	$html=apply_filters('wf_pklist_package_product_tbody_html', $html, $columns_list_arr, $template_type, $order, $box_packing, $order_package);
        }else
        {
			$html=self::dummy_product_row($columns_list_arr);
        }
        return $html;
	}

	/**
	*	@since 4.0.3 Prepare order grouping row text for package product table
	*
	*/
	private static function order_text_for_product_table_grouping_row($item, $template_type)
	{
		$order_text=__('Unknown');
		if(isset($item['order']) && !is_null($item['order']) && is_object($item['order']) && is_a($item['order'],'WC_Order'))
		{
			$order_info_arr=array();
			$order_info_arr[]=__('Order number','wf-woocommerce-packing-list').': '.self::get_order_number($item['order'],$template_type);
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				$order_info_arr[]=__('Invoice number','wf-woocommerce-packing-list').': '.Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($item['order'],false); //do not force generate
			}
			$order_info_arr=apply_filters('wf_pklist_alter_order_grouping_row_text', $order_info_arr, $item['order'], $template_type);
			$order_text=implode(" ",$order_info_arr);
		}
		return $order_text;
	}

	/**
	*	@since 4.0.3 Prepare grouping row for package product table Eg: Order wise(Only for picklist), Category wise
	*	@since 4.0.5 Added new filter to alter grouping row content
	*/
	private static function get_product_table_grouping_row($is_category_under_order, $loop, $key, $total_column, $template_type)
	{
		$row_type='category';
		if(($is_category_under_order==1 && $loop==1) || ($is_category_under_order!=1 && $loop==2))
		{
			$row_type='order';
		}
		$key=apply_filters('wf_pklist_alter_grouping_row_data', $key, $row_type, $template_type);
		if($row_type=='category')
		{
			$category_tr_html='<tr class="wfte_product_table_category_row"><td colspan="'.$total_column.'">'.$key.'</td></tr>';
			return apply_filters('wf_pklist_alter_category_row_html', $category_tr_html, $key, $total_column, $template_type);
		}else
		{
			$order_tr_html='<tr class="wfte_product_table_order_row"><td colspan="'.$total_column.'">'.$key.'</td></tr>';
    		return apply_filters('wf_pklist_alter_order_row_html', $order_tr_html, $key, $total_column, $template_type);
		}
	}

	/**
	* 
	* Render image column for product table
	* @since 4.0.0
	* @since 4.0.2 Default image option added, CSS class option added
	*/
	private static function generate_product_image_column_data($product_id,$variation_id,$parent_id)
	{
		$img_url=plugin_dir_url(plugin_dir_path(__FILE__)).'assets/images/thumbnail-preview.png';
		if($product_id>0)
		{
			$image_id=get_post_thumbnail_id($product_id);
	        $attachment=wp_get_attachment_image_src($image_id);
	        if(empty($attachment[0]) && $variation_id>0) //attachment is empty and variation is available
	        {		            
	            $var_image_id=get_post_thumbnail_id($variation_id);
	            $image_id=(($var_image_id=='' || $var_image_id==0) ? get_post_thumbnail_id($parent_id) : $var_image_id);
	            $attachment=wp_get_attachment_image_src($image_id);
	        }
	        $img_url=(!empty($attachment[0]) ? $attachment[0] : $img_url);
    	}	
        return '<img src="'.$img_url.'" style="max-width:30px; max-height:30px; border-radius:25%;" class="wfte_product_image_thumb"/>';
	}


	/**
	* 	Render product table row HTML for non package type documents
	* 	@since 4.0.8 Group by category option added
	*/
	private static function generate_product_table_product_row_html($columns_list_arr, $template_type, $order=null)
	{
		$html='';
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
		if(!is_null($order))
        {
			$wc_version=WC()->version;
			$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
			$user_currency=get_post_meta($order_id,'_order_currency',true);
			$order_items=$order->get_items();
			$order_items=apply_filters('wf_pklist_alter_order_items', $order_items, $template_type, $order);

			$the_options=Wf_Woocommerce_Packing_List::get_settings($module_id);
			if($wc_version<'2.7.0')
			{
	            $order_prices_include_tax=$order->prices_include_tax;
	            $order_display_cart_ex_tax=$order->display_cart_ex_tax;
	        } else {
	            $order_prices_include_tax=$order->get_prices_include_tax();
	            $order_display_cart_ex_tax=get_post_meta($order_id,'_display_cart_ex_tax',true);
	        }	        

	        /**
	        *	Check grouping enabled
	        */
	        $category_wise_split =Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting',$module_id);
	        $item_arr=array();
	        $total_column=self::get_total_table_columms_enabled($columns_list_arr);
	        if($category_wise_split=='Yes')
	        {
	        	foreach ($order_items as $order_item_id=>$order_item) 
				{
					$product_id=$order_item->get_product_id();
	        		$term_name_arr=array();
	        		$term_name_arr=self::get_term_data($product_id, $term_name_arr, $template_type, $order); 
	        		
	        		//adding empty value if no term found
					$term_name_arr=(count($term_name_arr)==0 ? array('--') : $term_name_arr);
                	$term_name=implode(", ",$term_name_arr);
                	if(!isset($item_arr[$term_name]))
                	{
                		$item_arr[$term_name]=array();
                	}
                	$item_arr[$term_name][$order_item_id]=$order_item;
	        	}

	        }else /* prepare same array structure as in the grouping */
	        {
	        	$item_arr[]=$order_items;
	        }
	        foreach($item_arr as $item_key=>$items)
	        {

        		if($category_wise_split=='Yes')
	        	{
        			$html.=self::get_product_table_grouping_row(1, 2, $item_key, $total_column, $template_type);
        		}

				foreach ($items as $order_item_id=>$order_item) 
				{
				    // get the product; if this variation or product has been deleted, this will return null...
				    $_product=$order->get_product_from_item($order_item);
				    if($_product)
				    {
				        $product_row_columns=array(); //for html generation
				        $product_id=($wc_version< '2.7.0' ? $_product->id : $_product->get_id());
				        $variation_id=($order_item['variation_id']!='' ? $order_item['variation_id']*1 : 0);
				        $parent_id=wp_get_post_parent_id($variation_id);
				        $item_taxes=$order_item->get_taxes();
				        $item_tax_subtotal=(isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
				        foreach($columns_list_arr as $columns_key=>$columns_value)
				        {
				            if($columns_key=='image' || $columns_key=='-image')
				            {
				            	$column_data=self::generate_product_image_column_data($product_id,$variation_id,$parent_id);
				            }
				            elseif($columns_key=='sku' || $columns_key=='-sku')
				            {
				            	$column_data=$_product->get_sku();
				            }
				            elseif($columns_key=='product' || $columns_key=='-product')
				            {
				            	$product_name=(isset($order_item['name']) ? $order_item['name'] : '');
				            	$product_name=apply_filters('wf_pklist_alter_product_name', $product_name, $template_type, $_product, $order_item, $order);
								
				            	//variation data======
				            	$variation='';
				            	if(isset($the_options['woocommerce_wf_packinglist_variation_data']) && $the_options['woocommerce_wf_packinglist_variation_data']=='Yes')
				            	{ 
				            		// get variation data, meta data
					            	$variation=self::get_order_line_item_variation_data($order_item,$order_item_id,$_product,$order,$template_type);
							        
							        $item_meta=function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($order_item_id,'',false) : $order->get_item_meta($order_item_id);
							        $variation_data=apply_filters('wf_pklist_add_product_variation', $item_meta, $template_type, $_product, $order_item, $order);
							        if(!empty($variation_data) && !is_array($variation_data))
							        {
							        	$variation.='<br>'.$variation_data;
							        }
							        if(!empty($variation))
							        {	        
							        	$variation='<br/><small style="word-break: break-word;">'.$variation.'</small>';
							        }
						    	}

						        //additional product meta
						        $addional_product_meta = '';
						        if(isset($the_options['wf_'.$template_type.'_product_meta_fields']) && count($the_options['wf_'.$template_type.'_product_meta_fields'])>0) 
						        {
						            $selected_product_meta_arr=$the_options['wf_'.$template_type.'_product_meta_fields'];
						            $product_meta_arr=Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
						            foreach($selected_product_meta_arr as $value)
						            {
						                $meta_data=get_post_meta($product_id,$value,true);
						                if($meta_data=='' && $variation_id>0)
						                {
						                	$meta_data=get_post_meta($parent_id,$value,true);
						                }
						                if(is_array($meta_data))
					                    {
					                        $output_data=(self::wf_is_multi($meta_data) ? '' : implode(', ',$meta_data));
					                    }else
					                    {
					                        $output_data=$meta_data;
					                    }
					                    if(isset($product_meta_arr[$value]))
					                    {
						                    $meta_info_arr=array('key'=>$value,'title'=>__($product_meta_arr[$value],'wf-woocommerce-packing-list'),'value'=>__($output_data,'wf-woocommerce-packing-list'));
						                    $meta_info_arr=apply_filters('wf_pklist_alter_product_meta', $meta_info_arr, $template_type, $_product, $order_item, $order);
					                    	if(is_array($meta_info_arr) && isset($meta_info_arr['title']) && isset($meta_info_arr['value']) && $meta_info_arr['value']!="")
						                    {
						                    	$addional_product_meta.='<small>'.$meta_info_arr['title'].': '.$meta_info_arr['value'].'</small><br>';
						                    }
						                }			                    
					                }
						        }
						        $addional_product_meta=apply_filters('wf_pklist_add_product_meta',$addional_product_meta,$template_type,$_product,$order_item,$order);
						        
						        $column_data='<b>'.$product_name.'</b>';
						        if(!empty($variation))
						        {
						        	$column_data.=$variation;
						        }
						        if(!empty($addional_product_meta))
						        {
						        	$column_data.=' <br />'.$addional_product_meta;
						        }
				            }
				            elseif($columns_key=='quantity' || $columns_key=='-quantity')
				            {
				            	$column_data=apply_filters('wf_pklist_alter_item_quantiy',$order_item['qty'],$template_type,$_product,$order_item,$order);
				            }
				            elseif($columns_key=='price' || $columns_key=='-price')
				            {
				            	$item_price=$order->get_item_total($order_item,false,true);
		                    	$item_price=apply_filters('wf_pklist_alter_item_price',$item_price,$template_type,$_product,$order_item,$order);
		                    	$item_price_formated=wc_price($item_price, array('currency'=>$user_currency));
		                    	$column_data=apply_filters('wf_pklist_alter_item_price_formated',$item_price_formated,$template_type,$item_price,$_product,$order_item,$order);          	
				            }
				            elseif(strpos($columns_key,'individual_tax_')===0 || strpos($columns_key,'individual_tax_')===1)
				            {
				            	$tax_id_arr=explode("individual_tax_",$columns_key);
				            	$tax_id=end($tax_id_arr);
				            	$tax_val=(isset($item_tax_subtotal[$tax_id]) ? $item_tax_subtotal[$tax_id] : 0);
				            	$tax_val=apply_filters('wf_pklist_alter_item_individual_tax',$tax_val,$template_type,$tax_id,$order_item,$order);
				            	$column_data=wc_price($tax_val,array('currency'=>$user_currency));
				            }
				            elseif($columns_key=='tax' || $columns_key=='-tax')
				            {
				            	$item_tax=$order->get_line_tax($order_item);
								$item_tax=apply_filters('wf_pklist_alter_item_tax',$item_tax,$template_type,$_product,$order_item,$order);
								$item_tax_formated=wc_price($item_tax,array('currency'=>$user_currency));
	                    		$column_data=apply_filters('wf_pklist_alter_item_tax_formated',$item_tax_formated,$template_type,$item_tax,$_product,$order_item,$order); 
				            }
				            elseif($columns_key=='total_price' || $columns_key=='-total_price')
				            {
				            	$product_total=($wc_version< '2.7.0' ? $order->get_item_meta($order_item_id,'_line_total',true) : $order_item->get_total());
		                        $product_total=apply_filters('wf_pklist_alter_item_total',$product_total,$template_type,$_product,$order_item,$order);
		                        if($order_display_cart_ex_tax || !$order_prices_include_tax)
		                        {
		                            $ex_tax_label=($order_prices_include_tax ? 1 : 0);
		                            $product_total_formated=wc_price($product_total,array('currency'=>$user_currency),array('ex_tax_label'=>$ex_tax_label));
		                            $column_data=apply_filters('wf_pklist_alter_item_total_formated',$product_total_formated,$template_type,$product_total,$_product,$order_item,$order);
		                        }
		                        else
		                        {
		                            $product_total_formated=wc_price($product_total,array('currency'=>$user_currency));
		                            $column_data=apply_filters('wf_pklist_alter_item_total_formated',$product_total_formated,$template_type,$product_total,$_product,$order_item,$order);
		                        }
				            }else //custom column by user
				            {
				            	$column_data='';
				            	$column_data=apply_filters('wf_pklist_product_table_additional_column_val',$column_data,$template_type,$columns_key,$_product,$order_item,$order);
				            }
				            $product_row_columns[$columns_key]=$column_data;
				        }
				        $product_row_columns=apply_filters('wf_pklist_alter_product_table_columns',$product_row_columns,$template_type,$_product,$order_item,$order);
				        $html.=self::build_product_row_html($product_row_columns, $columns_list_arr);
				    }
				}
			}
			$html=apply_filters('wf_pklist_product_tbody_html', $html, $columns_list_arr, $template_type, $order);

		}else //dummy value for preview section (No order data available)
		{
			$html=self::dummy_product_row($columns_list_arr);
		}
		return $html;
	}
	public static function build_product_row_html($product_row_columns, $columns_list_arr)
	{
		$html='';
		if(is_array($product_row_columns))
        {
        	$html.='<tr>';
        	foreach($product_row_columns as $columns_key=>$columns_value) 
        	{
        		$hide_it=($columns_key[0]=='-' ? self::TO_HIDE_CSS : ''); //column not enabled
        		$extra_col_options=$columns_list_arr[$columns_key];
        		$td_class=$columns_key.'_td';
        		$html.='<td class="'.$hide_it.' '.$td_class.' '.$extra_col_options.'">';
        		$html.=$columns_value;
        		$html.='</td>';
        	}
        	$html.='</tr>';
        }
        return $html;
	}
	private static function dummy_product_row($columns_list_arr)
	{
		$html='';
		$dummy_vals=array(
			'image'=>self::generate_product_image_column_data(0,0,0),
			'product'=>'Jumbing LED Light Wall Ball',
			'sku'=>'A1234',
			'quantity'=>'1',
			'price'=>'$20.00',
			'tax'=>'$2.00',
			'total_price'=>'$100.00',
			'total_weight'=>'2 kg',
		);
		$html='<tr>';
		foreach($columns_list_arr as $columns_key=>$columns_value)
		{
			$is_hidden=($columns_key[0]=='-' ? 1 : 0); //column not enabled
			$column_id=($is_hidden==1 ? substr($columns_key,1) : $columns_key);
			$hide_it=($is_hidden==1 ? self::TO_HIDE_CSS : ''); //column not enabled
			$extra_col_options=$columns_list_arr[$columns_key];
			$td_class=$columns_key.'_td';
			$html.='<td class="'.$hide_it.' '.$td_class.' '.$extra_col_options.'">';
			$html.=isset($dummy_vals[$column_id]) ? $dummy_vals[$column_id] : '';
			$html.='</td>';
		}
		$html.='</tr>';
		return $html;
	}
	private static function generate_product_table_head_html($columns_list_arr,$template_type)
	{
		$is_rtl_for_pdf=false;
		$is_rtl_for_pdf=apply_filters('wf_pklist_is_rtl_for_pdf',$is_rtl_for_pdf,$template_type);

		$first_visible_td_key='';
		$last_visible_td_key='';

		foreach ($columns_list_arr as $columns_key=>$columns_value)
		{
			$is_hidden=($columns_key[0]=='-' ? 1 : 0); //column not enabled

			if(strip_tags($columns_value)==$columns_value) //column entry without th HTML so we need to add
			{
				$coumn_key_real=($is_hidden==1 ? substr($columns_key,1) : $columns_key);
				$columns_value='<th class="wfte_product_table_head_'.$coumn_key_real.'" col-type="'.$columns_key.'">'.$columns_value.'</th>';
			}
			
			if($is_hidden==1)
			{
				$columns_value_updated=self::addClass('',$columns_value,self::TO_HIDE_CSS);
				if($columns_value_updated==$columns_value) //no class attribute in some cases
				{
					$columns_value_updated=str_replace('<th>','<th class="'.self::TO_HIDE_CSS.'">',$columns_value);
				}
			}else
			{
				$columns_value_updated=self::removeClass('',$columns_value,self::TO_HIDE_CSS);

				if($first_visible_td_key=='')
				{
					$first_visible_td_key=$columns_key;
				}
				$last_visible_td_key=$columns_key;
			}
			//remove last column CSS class
			$columns_value_updated=str_replace('wfte_right_column','',$columns_value_updated);
			$columns_list_arr[$columns_key]=$columns_value_updated;
		}

		//add end th CSS class
		$end_td_key=($is_rtl_for_pdf===false ? $last_visible_td_key : $first_visible_td_key);
		if($end_td_key!="")
		{
			$columns_class_added=self::addClass('',$columns_list_arr[$end_td_key],'wfte_right_column');
			if($columns_class_added==$columns_list_arr[$end_td_key]) //no class attribute in some cases, so add it
			{
				$columns_class_added=str_replace('<th>','<th class="wfte_right_column">',$columns_list_arr[$end_td_key]);
			}
			$columns_list_arr[$end_td_key]=$columns_class_added;
		}
		$columns_list_val_arr=array_values($columns_list_arr);
		return implode('',$columns_list_val_arr);
	}

	/**
	* Total price in words
	*	@since 4.0.2
	*/
	public static function set_total_in_words($total,$find_replace,$template_type,$html,$order=null)
	{
		if(strpos($html,'[wfte_total_in_words]')!==false) //if total in words placeholder exists then only do the process
        {
        	$total_in_words=self::convert_number_to_words($total);
        	$total_in_words=apply_filters('wf_pklist_alter_total_price_in_words',$total_in_words,$template_type,$order);
        	$find_replace['[wfte_total_in_words]']=$total_in_words;
        }
        return $find_replace;
	}

	/**
	*	Get the total weight of an order.
	*	@since 4.0.2	
	*	@param array $find_replace find and replace data
	* 	@param string $template_type document type Eg: invoice
	*	@param string $html template HTML
	* 	@param object $order order object
	*
	*	@return array $find_replace
	*/
	public static function set_total_weight($find_replace,$template_type,$html,$order=null)
	{
		$total_weight=0;
		if(strpos($html,'[wfte_weight]')!==false) //if total weight placeholder exists then only do the process
        {
			if(!is_null($order))
			{
				$order_items=$order->get_items();
				$find_replace['[wfte_weight]']=__('n/a','wf-woocommerce-packing-list');
				if($order_items)
				{
					foreach($order_items as $item)
					{
						$quantity=(int) $item->get_quantity(); // get quantity
				        $product=$item->get_product(); // get the WC_Product object
				        $weight=0;
				        if($product)
				        {
				        	$weight=(float) $product->get_weight(); // get the product weight
				        }
				        $total_weight+=floatval($weight*$quantity);
					}
					$weight_data=$total_weight.' '.get_option('woocommerce_weight_unit');
					$weight_data=apply_filters('wf_pklist_alter_weight', $weight_data, $total_weight, $order);

					/* the below line is for adding compatibility for existing users */
					$weight_data=apply_filters('wf_pklist_alter_packinglist_weight',$weight_data,$total_weight,$order);
					$find_replace['[wfte_weight]']=$weight_data;
				}
			}else
			{
				$find_replace['[wfte_weight]']=$total_weight.' '.get_option('woocommerce_weight_unit');
			}
		}
		return $find_replace;
	}
	public static function set_extra_fields($find_replace,$template_type,$html,$order=null)
	{
		$extra_fields=array();
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
		if(!is_null($order))
        {
        	$the_options=Wf_Woocommerce_Packing_List::get_settings($module_id);
        	$default_options=Wf_Woocommerce_Packing_List::default_settings($module_id);
        	$default_fields=array_values(Wf_Woocommerce_Packing_List::$default_additional_data_fields);
        	$default_fields_label=array_flip(Wf_Woocommerce_Packing_List::$default_additional_data_fields);
        	$wc_version=(WC()->version<'2.7.0') ? 0 : 1;
        	$order=($wc_version==0 ? new WC_Order($order) : new wf_order($order));
        	$order_id=($wc_version==0 ? $order->id : $order->get_id());
        	if(isset($the_options['wf_'.$template_type.'_contactno_email']) && is_array($the_options['wf_'.$template_type.'_contactno_email'])) //if user selected any fields
        	{ 
        		$user_created_fields=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields'); //this is plugin main setting so no need to specify module id
        		$user_created_fields=is_array($user_created_fields) ? $user_created_fields : array();

        		//additional checkout fields
				$additional_checkout=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
				
				/* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
		        $additional_checkout=Wf_Woocommerce_Packing_List::process_checkout_fields($additional_checkout);

		        $user_created_fields=array_merge($user_created_fields,$additional_checkout);

        		foreach($the_options['wf_'.$template_type.'_contactno_email'] as $val) //user selected fields
        		{
        			if(in_array($val,$default_fields))
        			{
        				$meta_vl='';
        				if($val=='email')
        				{
        					$meta_vl=($wc_version==0 ? $order->billing_email : $order->get_billing_email());
        				}elseif($val=='contact_number')
        				{
        					$meta_vl=($wc_version==0 ? $order->billing_phone : $order->get_billing_phone());
        				}elseif($val=='vat')
        				{
        					$meta_vl=($wc_version==0 ? $order->billing_vat : get_post_meta($order_id,'_billing_vat',true));
        				}elseif($val=='ssn')
        				{
        					$meta_vl=($wc_version==0 ? $order->billing_ssn : get_post_meta($order_id,'_billing_ssn',true));
        				}elseif($val=='cus_note')
        				{
        					$meta_vl=($wc_version==0 ? $order->customer_note : $order->get_customer_note());
        				}
        				$extra_fields[$val]=$meta_vl;
        			}else
        			{
        				//check meta key exists, and user created field exists
         				if(isset($user_created_fields[$val]))
        				{
        					$label=$user_created_fields[$val];
        					if(get_post_meta($order_id,'_billing_'.$val,true))
							{
								$extra_fields[$label]=get_post_meta($order_id,'_billing_'.$val,true);
							}
							if(get_post_meta($order_id,$val,true))
							{
								$extra_fields[$label]=get_post_meta($order_id,$val,true);
							}elseif(get_post_meta($order_id,'_'.$val,true))
							{
								$extra_fields[$label]=get_post_meta($order_id,'_'.$val,true);
							}
        				}
        			}       			
        		}
        	}

        	//shipping method
        	$order_shipping =($wc_version==0 ? $order->shipping_method : $order->get_shipping_method());
        	if(get_post_meta($order_id, '_tracking_provider', true) || $order_shipping)
        	{
        		$find_replace['[wfte_shipping_method]']=apply_filters('wf_pklist_alter_shipping_method',$order_shipping,$template_type,$order);
        	}else
        	{
        		$find_replace['[wfte_shipping_method]']='';
        	}

        	//tracking number
        	$tracking_key=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_tracking_number');
        	$tracking_data=apply_filters('wf_pklist_tracking_data_key',$tracking_key,$template_type,$order);
        	$tracking_details=get_post_meta($order_id,($tracking_key!='' ? $tracking_data : '_tracking_number'),true);
        	if($tracking_details)
        	{
        		$find_replace['[wfte_tracking_number]']=apply_filters('wf_pklist_alter_tracking_details',$tracking_details,$template_type,$order);    		
        	}else
        	{
        		$find_replace['[wfte_tracking_number]']='';
        	}

        	//filter to alter extra fields
        	$extra_fields=apply_filters('wf_pklist_alter_additional_fields',$extra_fields,$template_type,$order);
        	
        	$find_replace['[wfte_vat_number]']=isset($extra_fields['vat']) ? $extra_fields['vat'] : '';
        	$find_replace['[wfte_ssn_number]']=isset($extra_fields['ssn']) ? $extra_fields['ssn'] : '';
        	$find_replace['[wfte_email]']=isset($extra_fields['email']) ? $extra_fields['email'] : '';
        	$find_replace['[wfte_tel]']=isset($extra_fields['contact_number']) ? $extra_fields['contact_number'] : '';

        	$default_fields_placeholder=array(
        		'vat'=>'vat_number',
        		'ssn'=>'ssn_number',
        		'contact_number'=>'tel',
        	);

        	//extra fields
        	$ex_html='';
        	if(is_array($extra_fields))
        	{
	        	foreach($extra_fields as $ex_key=>$ex_vl)
	        	{
	        		if(!in_array($ex_key,$default_fields)) //not default fields like vat,ssn
        			{
        				if(is_string($ex_vl) && trim($ex_vl)!="")
        				{
        					$ex_html.='<div class="wfte_extra_fields">
					            <span>'.__(ucfirst($ex_key), 'wf-woocommerce-packing-list').':</span>
					            <span>'.__($ex_vl,'wf-woocommerce-packing-list').'</span>
					          </div>';
        				}
	        		}else 
	        		{
	        			$placeholder_key=isset($default_fields_placeholder[$ex_key]) ? $default_fields_placeholder[$ex_key] : $ex_key;
	        			$placeholder='[wfte_'.$placeholder_key.']';
	        			if(strpos($html,$placeholder)===false) //default fields that have no placeholder
	        			{
	        				if(trim($ex_vl)!="")
	        				{
	        					$ex_html.='<div class="wfte_extra_fields">
						            <span>'.__($default_fields_label[$ex_key], 'wf-woocommerce-packing-list').':</span>
						            <span>'.__($ex_vl,'wf-woocommerce-packing-list').'</span>
						          </div>';
	        				}
	        			}
	        		}
	        	}
        	}
        	$find_replace['[wfte_extra_fields]']=$ex_html;

        	$order_item_meta_data='';
        	$order_item_meta_data=apply_filters('wf_pklist_order_additional_item_meta', $order_item_meta_data, $template_type, $order);
        	$find_replace['[wfte_order_item_meta]']=$order_item_meta_data;
		}
		return $find_replace;
	}
	public static function set_logo($find_replace,$template_type)
	{
		//module settings are saved under module id
		$module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);

		$the_options=Wf_Woocommerce_Packing_List::get_settings($module_id);
		$the_options_main=Wf_Woocommerce_Packing_List::get_settings();
		$find_replace['[wfte_company_logo_url]']='';
		if(isset($the_options['woocommerce_wf_packinglist_logo']) && $the_options['woocommerce_wf_packinglist_logo']!="")
		{
			$find_replace['[wfte_company_logo_url]']=$the_options['woocommerce_wf_packinglist_logo'];
		}else
		{ 
			if($the_options_main['woocommerce_wf_packinglist_logo']!="")
			{
				$find_replace['[wfte_company_logo_url]']=$the_options_main['woocommerce_wf_packinglist_logo'];
			}				
		}
		$find_replace['[wfte_company_name]']=$the_options_main['woocommerce_wf_packinglist_companyname'];
		return $find_replace;
	}

	/**
	 * Get shipping address
	 *
	 * @param String $template_type Document type eg:invoice
	 * @param Object $order Order object 
	 * @return String billing address
	 */
	protected static function get_shipping_address($template_type,$order=null)
	{
		if(!is_null($order))
        {
			$the_options=Wf_Woocommerce_Packing_List::get_settings();
			$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
	        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
	        $shipping_address = array();
	        $countries = new WC_Countries;
	        $shipping_country = get_post_meta($order_id, '_shipping_country', true);
	        $shipping_state = get_post_meta($order_id, '_shipping_state', true);
	        $shipping_state_full = ( $shipping_country && $shipping_state && isset($countries->states[$shipping_country][$shipping_state]) ) ? $countries->states[$shipping_country][$shipping_state] : $shipping_state;
	        $shipping_country_full = ( $shipping_country && isset($countries->countries[$shipping_country]) ) ? $countries->countries[$shipping_country] : $shipping_country;
	        
	        $shipping_address=array(
	        	'first_name'=>$order->shipping_first_name,
	        	'last_name'=>$order->shipping_last_name,
	        	'company'=>$order->shipping_company,
	        	'address_1'=>$order->shipping_address_1,
	        	'address_2'=>$order->shipping_address_2,
	        	'city'=>$order->shipping_city,
	        	'state'=>($the_options['woocommerce_wf_state_code_disable']=='yes' ? $shipping_state_full : $shipping_state),
	        	'country'=>$shipping_country_full,
	        	'postcode'=>$order->shipping_postcode,
	        );
	        $shipping_address=apply_filters('wf_pklist_alter_shipping_address',$shipping_address,$template_type,$order);

	        $shipping_address['first_name']=(isset($shipping_address['first_name']) ? $shipping_address['first_name'] : '').' '.(isset($shipping_address['last_name']) ? $shipping_address['last_name'] : ''); 
	        unset($shipping_address['last_name']);
	        if(trim($shipping_address['first_name'])==""){ unset($shipping_address['first_name']); }

	        $shipping_address=self::merge_city_state_zip($shipping_address);

	        $shipping_addr_vals=is_array($shipping_address) ? array_filter(array_values($shipping_address)) : array();
	    	return implode("<br />",$shipping_addr_vals);
	    }else
	    {
	    	return '';
	    }
	}
	
	/**
	*
	* @since 4.0.0  Merge City State Zip code to one line
	* @since 4.0.2  Preserves the array key order while merging
	* @param array address
	* @return array merged address
	*/
	protected static function merge_city_state_zip($address)
	{
		//return $address; //disabled
		$arr=array();
		$to_merge=array('city','state','postcode');
		foreach($address as $k=>$v)
		{
			if(in_array($k,$to_merge))
			{
				$arr[]=$v;
			}
		}
		unset($address['state']);
		unset($address['postcode']);
		$address['city']=implode(", ",array_filter(array_values($arr)));
		return $address;
	}

	public static function set_shipping_address($find_replace,$template_type,$order=null)
	{
		if(!is_null($order))
        {
			$shipping_address=self::get_shipping_address($template_type,$order);
        	$shipping_address=trim($shipping_address)=="" ? self::get_billing_address($template_type,$order) : $shipping_address;
	    	$find_replace['[wfte_shipping_address]']=$shipping_address;
	    }else
	    {
	    	$find_replace['[wfte_shipping_address]']='';
	    }
	    return $find_replace;
	}
	
	/**
	 * Get billing address
	 *
	 * @param String $template_type Document type eg:invoice
	 * @param Object $order Order object 
	 * @return String billing address
	 */
	protected static function get_billing_address($template_type, $order=null)
	{
		if(!is_null($order))
        {
			$the_options=Wf_Woocommerce_Packing_List::get_settings();
			$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
			$order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();     
	        $countries = new WC_Countries;  
	        $billing_country = get_post_meta($order_id, '_billing_country', true);   
	        $billing_state = get_post_meta($order_id, '_billing_state', true);
	        $billing_state_full = ( $billing_country && $billing_state && isset($countries->states[$billing_country][$billing_state]) ) ? $countries->states[$billing_country][$billing_state] : $billing_state;
	        $billing_country_full = ( $billing_country && isset($countries->countries[$billing_country]) ) ? $countries->countries[$billing_country] : $billing_country;
	        
	        $billing_address=array(
	        	'first_name'=>$order->billing_first_name,
	        	'last_name'=>$order->billing_last_name,
	        	'company'=>$order->billing_company,
	        	'address_1'=>$order->billing_address_1,
	        	'address_2'=>$order->billing_address_2,
	        	'city'=>$order->billing_city,
	        	'state'=>($the_options['woocommerce_wf_state_code_disable']=='yes' ? $billing_state_full : $billing_state),
	        	'country'=>$billing_country_full,
	        	'postcode'=>$order->billing_postcode,
	        );
	        $billing_address=apply_filters('wf_pklist_alter_billing_address',$billing_address,$template_type,$order);

	        $billing_address['first_name']=(isset($billing_address['first_name']) ? $billing_address['first_name'] : '').' '.(isset($billing_address['last_name']) ? $billing_address['last_name'] : ''); 
	        unset($billing_address['last_name']);
	        if(trim($billing_address['first_name'])==""){ unset($billing_address['first_name']); }

	        $billing_address=self::merge_city_state_zip($billing_address);
	        $billing_addr_vals=is_array($billing_address) ? array_filter(array_values($billing_address)) : array();
	        return implode("<br />",$billing_addr_vals);
	    }else
	    {
	    	return '';
	    }
	}
	public static function set_billing_address($find_replace,$template_type,$order=null)
	{
		if(!is_null($order))
        {
			$billing_address=self::get_billing_address($template_type,$order);
        	$find_replace['[wfte_billing_address]']=$billing_address;
	    }else
	    {
	    	$find_replace['[wfte_billing_address]']='';
	    }
	    return $find_replace;
	}
	public static function set_shipping_from_address($find_replace,$template_type,$order=null)
	{
		$the_options=Wf_Woocommerce_Packing_List::get_settings();
		
        $country_selected=$the_options['wf_country'];
        $country_arr=explode(":",$country_selected);
        $country=isset($country_arr[0]) ? $country_arr[0] : '';
        $state=isset($country_arr[1]) ? $country_arr[1] : '';
        $countries=new WC_Countries; 
        $fromaddress=array(
        	'name'=>$the_options['woocommerce_wf_packinglist_sender_name'],
        	'address_line1'=>$the_options['woocommerce_wf_packinglist_sender_address_line1'],
        	'address_line2'=>$the_options['woocommerce_wf_packinglist_sender_address_line2'],
        	'city'=>$the_options['woocommerce_wf_packinglist_sender_city'],
        	'state'=>$state,
        	'country'=>(isset($countries->countries[$country]) ? $countries->countries[$country] : ''),
        	'postcode'=>$the_options['woocommerce_wf_packinglist_sender_postalcode'],
        	'contact_number'=>$the_options['woocommerce_wf_packinglist_sender_contact_number'],
        	'vat'=>$the_options['woocommerce_wf_packinglist_sender_vat'],
        );
              
        //display state name instead of state code   
        if($the_options['woocommerce_wf_state_code_disable']=='yes')
        {
        	$fromaddress['state']=isset($countries->states[$country]) && isset($countries->states[$country][$state]) ? $countries->states[$country][$state] : '';
        }
        $returnaddress=$fromaddress; //not affect from address filter to return address
        if(!is_null($order))
        {
        	$order=( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
        	$fromaddress=apply_filters('wf_pklist_alter_shipping_from_address',$fromaddress,$template_type,$order);
        	$returnaddress=apply_filters('wf_pklist_alter_shipping_return_address',$returnaddress,$template_type,$order);
        }
        $fromaddress=self::merge_city_state_zip($fromaddress);
        $returnaddress=self::merge_city_state_zip($returnaddress);

        $from_addr_vals=is_array($fromaddress) ? array_filter(array_values($fromaddress)) : array();
        $return_addr_vals=is_array($returnaddress) ? array_filter(array_values($returnaddress)) : array();
        $find_replace['[wfte_from_address]']=implode("<br />",$from_addr_vals);
        $find_replace['[wfte_return_address]']=implode("<br />",$return_addr_vals);
		return $find_replace;
	}

	/**
	*  	Get variation data, meta data
	*	@since 4.0.0
	*	@since 4.0.2 [Bug fix] Showing meta data key instead of meta data label
	*	@since 4.0.4 Added compatiblity to handle meta with empty keys and duplicate meta keys
	*/
	public static function get_order_line_item_variation_data($order_item,$item_id,$_product,$order, $template_type)
	{  
        if (WC()->version > '2.7.0') {
            global $wpdb;
            $meta_value_data = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value, meta_id, order_item_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d
        ORDER BY meta_id", absint($item_id)), ARRAY_A);

        }

        $variation = '';
        $meta_data = array();
        if($metadata = ((WC()->version < '2.7.0') ? $order->has_meta($item_id) : $meta_value_data)) 
        {
	        
	        foreach ($metadata as $meta) 
            { 
                // Skip hidden core fields
                if (in_array($meta['meta_key'], array(
                            '_qty',
                            '_reduced_stock',
                            '_tax_class',
                            '_product_id',
                            '_variation_id',
                            '_line_subtotal',
                            '_line_subtotal_tax',
                            '_line_total',
                            '_line_tax',
                            'method_id',
                            'cost',
                            '_refunded_item_id',
                        ))) {
                    continue;
                }
                
                // Skip serialised meta
                if (is_serialized($meta['meta_value'])) {
                    continue;
                }

                // Get attribute data
                if (taxonomy_exists(wc_sanitize_taxonomy_name($meta['meta_key']))) 
                {
                    $term = get_term_by('slug', $meta['meta_value'], wc_sanitize_taxonomy_name($meta['meta_key']));
                    $meta_key = wc_attribute_label(wc_sanitize_taxonomy_name($meta['meta_key']));
                    $meta_value = isset($term->name) ? $term->name : $meta['meta_value'];
                    $meta_data[$meta_key] = $meta_value;
                }else
                {
                	$meta_data[$meta['meta_key']] = $meta['meta_value'];
                    $meta_data[$meta['meta_key']] = apply_filters('wf_pklist_alter_meta_value',$meta['meta_value'],$meta_data, $meta['meta_key']);
                }
            }
            
            // [Bug fix] Showing meta data key instead of meta data label
            if(method_exists($order_item,'get_formatted_meta_data'))
            {
                foreach($order_item->get_formatted_meta_data() as $meta_id=>$meta)
                {
					if(!isset($meta_data[$meta->display_key]))
                    {
                        if(isset($meta_data[$meta->key]))
                        {
							$val_backup=$meta_data[$meta->key];
                            unset($meta_data[$meta->key]);
                            $meta_data[$meta->display_key]=$val_backup;
                        }else
                        {
							if($meta->display_key=="")
							{
								$meta_data[]=strip_tags($meta->display_value);
							}else
							{
								$meta_data[$meta->display_key]=trim(strip_tags($meta->display_value));
							}
                        }
                    }else
					{
						if(html_entity_decode(trim(strip_tags($meta_data[$meta->display_key])))!=html_entity_decode(trim(strip_tags($meta->display_value)))) /* same key but value different */
						{							
							$meta_data[]=array($meta->display_key, trim(strip_tags($meta->display_value)));
						}
					}
                }
            }

            $meta_data = apply_filters('wf_pklist_modify_meta_data', $meta_data);
            $variation='';
            foreach ($meta_data as $id => $value) 
            {
                if($value != '')
                {
					if(intval($id)===$id) //numeric array
					{
						if(is_array($value))
						{
							$current_item=wp_kses_post(rawurldecode($value[0])) . ' : ' . wp_kses_post(rawurldecode($value[1])) . ' ';
						}else
						{
							$current_item=wp_kses_post(rawurldecode($value)) . ' ';
						}
					}else
					{
						$current_item= wp_kses_post(rawurldecode($id)) . ' : ' . wp_kses_post(rawurldecode($value)) . ' ';
					}              	
                	$variation.= apply_filters('wf_alter_line_item_variation_data', $current_item, $meta_data, $id, $value);
                }
            }
        }
        return $variation;
    }
    private static function wf_is_multi($array)
    {
	    $multi_check = array_filter($array,'is_array');
	    if(count($multi_check)>0) return true;
	    return false;
    }

    /**
    *	Convert number to words
    *	@author hunkriyaz <Github>
    *	@since 4.0.2
    *
    */
    public static function convert_number_to_words($number)
    {
	    $hyphen      = '-';
	    $conjunction = ' and ';
	    $separator   = ', ';
	    $negative    = 'negative ';
	    $decimal     = ' point ';
	    $dictionary  = array(
	        0                   => 'zero',
	        1                   => 'one',
	        2                   => 'two',
	        3                   => 'three',
	        4                   => 'four',
	        5                   => 'five',
	        6                   => 'six',
	        7                   => 'seven',
	        8                   => 'eight',
	        9                   => 'nine',
	        10                  => 'ten',
	        11                  => 'eleven',
	        12                  => 'twelve',
	        13                  => 'thirteen',
	        14                  => 'fourteen',
	        15                  => 'fifteen',
	        16                  => 'sixteen',
	        17                  => 'seventeen',
	        18                  => 'eighteen',
	        19                  => 'nineteen',
	        20                  => 'twenty',
	        30                  => 'thirty',
	        40                  => 'fourty',
	        50                  => 'fifty',
	        60                  => 'sixty',
	        70                  => 'seventy',
	        80                  => 'eighty',
	        90                  => 'ninety',
	        100                 => 'hundred',
	        1000                => 'thousand',
	        1000000             => 'million',
	        1000000000          => 'billion',
	        1000000000000       => 'trillion',
	        1000000000000000    => 'quadrillion',
	        1000000000000000000 => 'quintillion'
	    );
	    if (!is_numeric($number)) {
	        return false;
	    }
	    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
	        // overflow
	        /* 
	        trigger_error(
	            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
	            E_USER_WARNING
	        ); */
	        return false;
	    }
	    if ($number < 0) {
	        return $negative . self::convert_number_to_words(abs($number));
	    }
	    $string = $fraction = null;
	    if (strpos($number, '.') !== false) {
	        list($number, $fraction) = explode('.', $number);
	    }
	    switch (true) {
	        case $number < 21:
	            $string = $dictionary[$number];
	            break;
	        case $number < 100:
	            $tens   = ((int) ($number / 10)) * 10;
	            $units  = $number % 10;
	            $string = $dictionary[$tens];
	            if ($units) {
	                $string .= $hyphen . $dictionary[$units];
	            }
	            break;
	        case $number < 1000:
	            $hundreds  = $number / 100;
	            $remainder = $number % 100;
	            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	            if ($remainder) {
	                $string .= $conjunction . self::convert_number_to_words($remainder);
	            }
	            break;
	        default:
	            $baseUnit = pow(1000, floor(log($number, 1000)));
	            $numBaseUnits = (int) ($number / $baseUnit);
	            $remainder = $number % $baseUnit;
	            $string = self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
	            if ($remainder) {
	                $string .= $remainder < 100 ? $conjunction : $separator;
	                $string .= self::convert_number_to_words($remainder);
	            }
	            break;
	    }
	    if (null !== $fraction && is_numeric($fraction)) {
	        $string .= $decimal;
	        $words = array();
	        foreach (str_split((string) $fraction) as $number) {
	            $words[] = $dictionary[$number];
	        }
	        $string .= implode(' ', $words);
	    }
	    return $string;
	} 

    /**
    *	Hide the empty placeholders in the template HTML
    *	@since 4.0.0
    *	@since 4.0.2	added wfte_weight in defult hide list
    */
    public static function hide_empty_elements($find_replace,$html,$template_type)
    {
    	$hide_on_empty_fields=array('wfte_vat_number','wfte_ssn_number','wfte_email','wfte_tel','wfte_shipping_method','wfte_tracking_number','wfte_footer','wfte_return_policy',
    		'wfte_product_table_coupon',
			'wfte_product_table_fee',
			'wfte_product_table_total_tax',
			'wfte_product_table_order_discount',
			'wfte_product_table_cart_discount',
			'wfte_product_table_shipping',
			'wfte_order_item_meta',
			'wfte_weight',
			'wfte_total_in_words',
		);
		$hide_on_empty_fields=apply_filters('wf_pklist_alter_hide_empty',$hide_on_empty_fields,$template_type);
    	foreach ($hide_on_empty_fields as $key => $value)
    	{
    		if(isset($find_replace['['.$value.']']))
	    	{
	    		if($find_replace['['.$value.']']=="")
	    		{
	    			$html=self::addClass($value,$html,self::TO_HIDE_CSS);
	    		}
	    	}else
	    	{
	    		$find_replace['['.$value.']']='';
	    		$html=self::addClass($value,$html,self::TO_HIDE_CSS);
	    	}
    	}
    	return $html;
    }
    public static function getElmByClass($elm_class,$html)
    {
    	$matches=array();
    	$re = '/<[^>]*class\s*=\s*["\'](.*?[^"\']*)'.$elm_class.'(.*?[^"\']*)["\'][^>]*>/m';
		if(preg_match($re,$html,$matches))
		{
		  return $matches;
		}else
		{
			return false;
		}
    }
    private static function filterCssClasses($class)
    {
    	$class_arr=explode(" ",$class);
    	return array_unique(array_filter($class_arr));
    }
	private static function removeClass($elm_class,$html,$remove_class)
    {
    	$match=self::getElmByClass($elm_class,$html);
    	if($match) //found
    	{
    		$elm_class=$match[1].$elm_class.$match[2];
    		$new_class_arr=self::filterCssClasses($elm_class);
			foreach(array_keys($new_class_arr,$remove_class) as $key) {
			    unset($new_class_arr[$key]);
			}
			$new_class=implode(" ",$new_class_arr);
    		return str_replace($elm_class,$new_class,$html);
    	}
    	return $html;
    }

    /**
    *	Add class to element
    *	@since 4.0.0
    *	@param	string $elm_class CSS class to select
    *	@param  string $html HTML to serach
    *	@param 	string $new_class new CSS class to add
    */
    public static function addClass($elm_class,$html,$new_class)
    {
    	$match=self::getElmByClass($elm_class,$html);
    	if($match) //found
    	{ 
    		$elm_class=$match[1].$elm_class.$match[2];
    		$new_class_arr=self::filterCssClasses($elm_class.' '.$new_class);
			$new_class=implode(" ",$new_class_arr);
    		return str_replace($elm_class,$new_class,$html);
    	}
    	return $html;
    }

    /**
    * @since 4.0.8 
    * Get total count of enabled table columns
    */
    private static function get_total_table_columms_enabled($columns_list_arr)
    {
    	$total=0;
    	foreach ($columns_list_arr as $key => $value) 
    	{
    		if(substr($key, 0, 1)!='-')
    		{
    			$total++;
    		}
    	}
    	return $total;
    }

    public static function get_template_html_attr_vl($html,$attr,$default='')
	{
		$match_arr=array();
		$out=$default;
		if(preg_match('/'.$attr.'="(.*?)"/s',$html,$match_arr))
		{
			$out=$match_arr[1];
			$out=($out=='' ? $default : $out);
		}
		return $out;
	}

	/* 
	* Add dummy data for customizer design view
	* @return array
	*/
	public static function dummy_data_for_customize($find_replace,$template_type,$html)
	{
		$find_replace['[wfte_invoice_number]']=123456;
		$find_replace['[wfte_order_number]']=123456;

		$order_date_format=self::get_template_html_attr_vl($html,'data-order_date-format','m/d/Y');
		$find_replace['[wfte_order_date]']=date($order_date_format);

		$invoice_date_format=self::get_template_html_attr_vl($html,'data-invoice_date-format','m/d/Y');
		$find_replace['[wfte_invoice_date]']=date($invoice_date_format);

		$dispatch_date_format=self::get_template_html_attr_vl($html,'data-dispatch_date-format','m/d/Y');
		$find_replace['[wfte_dispatch_date]']=date($dispatch_date_format);
		
		//Dummy billing addresss
		$find_replace['[wfte_billing_address]']='Webtoffee <br>20 Maple Avenue <br>San Pedro <br>California <br>United States (US) <br>90731 <br>';
		
		//Dummy shipping addresss
		$find_replace['[wfte_shipping_address]']='Webtoffee <br>20 Maple Avenue <br>San Pedro <br>California <br>United States (US) <br>90731 <br>';
		
		$find_replace['[wfte_vat_number]']='123456';
    	$find_replace['[wfte_ssn_number]']='SSN123456';
    	$find_replace['[wfte_email]']='info@example.com';
    	$find_replace['[wfte_tel]']='+1 123 456';
    	$find_replace['[wfte_shipping_method]']='DHL';
    	$find_replace['[wfte_tracking_number]']='123456';
    	$find_replace['[wfte_order_item_meta]']='';
    	$find_replace['[wfte_extra_fields]']='';
		$find_replace['[wfte_product_table_subtotal]']='$100.00';
		$find_replace['[wfte_product_table_shipping]']='$0.00';
		$find_replace['[wfte_product_table_cart_discount]']='$0.00';
		$find_replace['[wfte_product_table_order_discount]']='$0.00';
		$find_replace['[wfte_product_table_total_tax]']='$0.00';
		$find_replace['[wfte_product_table_fee]']='$0.00';
		$find_replace['[wfte_product_table_payment_method]']='PayPal';
		$find_replace['[wfte_product_table_payment_total]']='$100.00';
		$find_replace['[wfte_product_table_coupon]']='{ABCD100}';
		$find_replace['[wfte_barcode_url]']='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAAAeAQMAAACrPfpdAAAABlBMVEX///8AAABVwtN+AAAAAXRSTlMAQObYZgAAABdJREFUGJVj+MzDfPg8P/NnG4ZRFgEWAHrncvdCJcw9AAAAAElFTkSuQmCC';
		
		$find_replace['[wfte_return_policy]']='Mauris dignissim neque ut sapien vulputate, eu semper tellus porttitor. Cras porta lectus id augue interdum egestas. Suspendisse potenti. Phasellus mollis porttitor enim sit amet fringilla. Nulla sed ligula venenatis, rutrum lectus vel';
		$find_replace['[wfte_footer]']='Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc nec vehicula purus. Mauris tempor nec ipsum ac tempus. Aenean vehicula porttitor tortor, et interdum tellus fermentum at. Fusce pellentesque justo rhoncus';
		$find_replace['[wfte_special_notes]']='Special notes: consectetur adipiscing elit. Nunc nec vehicula purus. ';
		$find_replace['[wfte_transport_terms]']='Transport Terms: Nunc nec vehicula purus. Mauris tempor nec ipsum ac tempus.';
		$find_replace['[wfte_sale_terms]']='Sale terms: et interdum tellus fermentum at. Fusce pellentesque justo rhoncus';
		//on package type documents
		$find_replace['[wfte_box_name]']='';
		$find_replace['[wfte_qr_code]']='';
		$find_replace['[wfte_total_in_words]']=self::convert_number_to_words(100);
		$find_replace['[wfte_printed_on]']=self::get_printed_on_date($html);

		$find_replace=apply_filters('wf_pklist_alter_dummy_data_for_customize',$find_replace,$template_type,$html);

		$tax_items_match=array();
		if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_tax_items\b[^"]*"[^>]*>(.*?)<\/tr>/s',$html,$tax_items_match))
		{
			$find_replace[$tax_items_match[0]]='';
		}
		return $find_replace;
	}
}
