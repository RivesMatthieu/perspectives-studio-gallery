<?php
/**
 * Credit Note section of the plugin
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Creditnote
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='creditnote';
    private $customizer=null;
    private $seq_number=null;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);
		add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
		add_filter('wf_module_non_options_fields',array($this,'get_non_options_fields'),10,2);
		add_filter('wf_module_non_disable_fields',array($this,'get_non_disable_fields'),10,2);
		
		//hook to add which fiedls to convert
		add_filter('wf_module_convert_to_design_view_html',array($this,'convert_to_design_view_html'),10,3);

		//hook to generate template html
		add_filter('wf_module_generate_template_html',array($this,'generate_template_html'),10,6);

		//hide empty fields on template
		add_filter('wf_pklist_alter_hide_empty',array($this,'hide_empty_elements'),10,6);

		add_action('wt_print_doc',array($this,'print_it'),10,2);

		add_filter('wt_email_attachments', array($this,'add_email_attachments'),10,4);	

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

		//initializing Sequential Number
		$this->seq_number=Wf_Woocommerce_Packing_List::load_modules('sequential-number');

		add_filter('wt_admin_menu', array($this,'add_admin_pages'),10,1);
		add_filter('wt_print_docdata_metabox',array($this,'add_docdata_metabox'),10,3);
		add_filter('wt_print_actions', array($this,'add_print_actions'),10,4);				

		add_filter('wt_pklist_alter_tooltip_data', array($this,'register_tooltips'),1);


		/** 
		* @since 4.0.5 declaring multi select form fields in settings form 
		*/
		add_filter('wt_pklist_intl_alter_multi_select_fields', array($this,'alter_multi_select_fields'), 10, 2);
		
		/** 
		* @since 4.0.5 Declaring validation rule for form fields in settings form 
		*/
		add_filter('wt_pklist_intl_alter_validation_rule', array($this,'alter_validation_rule'), 10, 2);

	}

	/**
	* 	@since 4.0.5
	* 	Declaring validation rule for form fields in settings form
	*/
	public function alter_validation_rule($arr, $base_id)
	{
		if($base_id == $this->module_id)
		{
			$arr=array(
				'wf_'.$this->module_base.'_contactno_email'=>array('type'=>'text_arr'),
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array('type'=>'text_arr')
			);

			if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
			{
				//sequential number validation rule
				$seq_arr=Wf_Woocommerce_Packing_List_Sequential_Number::get_validation_rule();
				$seq_arr=(!is_array($seq_arr) ? array() : $seq_arr);
				$arr=array_merge($arr, $seq_arr);
			}
		}
		return $arr;
	}

	/**
	* 	@since 4.0.5
	* 	Declaring multi select form fields in settings form
	*/
	public function alter_multi_select_fields($arr, $base_id)
	{
		if($base_id==$this->module_id)
		{
			$arr=array(
	        	'wf_'.$this->module_base.'_contactno_email'=>array(),
				'wf_'.$this->module_base.'_product_meta_fields'=>array()
	        );
		}
		return $arr;
	}


	/**
	* Get creditnote date
	* @since  	4.0.4
	* @return mixed
	*/
    public static function get_creditnote_date($order_id, $date_format, $order, $recent=false)
    {
    	if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
	    {
	    	if(is_a($order, 'WC_Order'))
	    	{
	    		$creditnote_date_arr=array();
	    		foreach ($order->get_refunds() as $ref_order) 
	    		{
	    			$ref_id = (WC()->version < '2.7.0') ? $ref_order->id : $ref_order->get_id();
	    			$creditnote_date_arr[]=self::get_creditnote_date($ref_id, $date_format, $ref_order, $recent);
	    			if($recent){
	    				break;
	    			}
	    		}
	    		$creditnote_date_arr=array_filter($creditnote_date_arr);
	    		return implode(", ",$creditnote_date_arr);
	    	}else
	    	{
	    		return Wf_Woocommerce_Packing_List_Sequential_Number::get_sequential_date($order_id, 'wf_creditnote_date', $date_format, $order);
	    	}
	    }else
	    {
	    	return '';
	    }
    }

	/**
	* 	Function to generate credit note number
	* 	@since 4.0.4
	* 	@return mixed
	* 	@param object $order Order object
	* 	@param boolean $force_generate Force generate if not exists
	* 	@param boolean $recent Only one recent record
	*/
    public static function generate_creditnote_number($order, $force_generate=true, $recent=false) 
    {
	    if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
	    {
	    	if(is_a($order, 'WC_Order'))
	    	{
	    		$creditnote_num_arr=array();
	    		foreach ($order->get_refunds() as $ref_order) 
	    		{
	    			$creditnote_num_arr[]=self::generate_creditnote_number($ref_order, $force_generate);
	    			if($recent){
	    				break;
	    			}
	    		}
	    		$creditnote_num_arr=array_filter($creditnote_num_arr);
	    		return implode(", ",$creditnote_num_arr);
	    	}else
	    	{
	    		return Wf_Woocommerce_Packing_List_Sequential_Number::generate_sequential_number($order, self::$module_id_static, array('number'=>'wf_creditnote_number', 'date'=>'wf_creditnote_date', 'enable'=>''), $force_generate);
	    	}
	    }else
	    {
	    	return '';
	    }
	}

	/**
	* 	@since 4.0.4
	* 	Hook the tooltip data to main tooltip array
	*/
	public function register_tooltips($tooltip_arr)
	{
		include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
		$tooltip_arr[$this->module_id]=$arr;
		return $tooltip_arr;
	}

	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			
		}
		return $hide_on_empty_fields;
	}

	/**
	 *  Items needed to be converted to design view
	 */
	public function convert_to_design_view_html($find_replace,$html,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_billing_address($find_replace,$template_type);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_charge_fields($find_replace,$template_type,$html);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html);
			$find_replace=$this->set_design_view_data($find_replace, $html, $template_type);
		}
		return $find_replace;
	}

	/**
	*	@since 4.0.4	set refund rows in product table
	*	
	*/
	private function set_refund_entries($find_replace, $html, $template_type, $order)
	{
		$refund_items_match=array();
		if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_refund_items\b[^"]*"[^>]*>(.*?)<\/tr>/s',$html,$refund_items_match))
		{
			$refund_items_html='';
			$refund_items_row_html=isset($refund_items_match[0]) ? $refund_items_match[0] : '';
			if(!is_null($order) && $refund_items_row_html!='')
			{
				$refund_data_arr=$order->get_refunds();
				if(!empty($refund_data_arr))
				{
					$wc_version=WC()->version;
					$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
					$user_currency=get_post_meta($order_id,'_order_currency',true);
					foreach($refund_data_arr as $refund_data)
					{	
						$refund_label=esc_html($refund_data->get_reason());
						$refund_label=($refund_label=='' ? __('Refund', 'wf-woocommerce-packing-list') : $refund_label);
						$refund_label.=' ('. self::generate_creditnote_number($refund_data) .')';
						$refund_label=apply_filters('wf_pklist_alter_refunditem_label', $refund_label, $refund_data, $template_type, $order);
	                    $refund_amount=apply_filters('wf_pklist_alter_refunditem_amount', $refund_data->get_amount(), $refund_data, $template_type, $order);
	                    $refund_amount='-'.wc_price($refund_amount, array('currency'=>$user_currency));
	                    $refund_items_html.=str_replace(array('[wfte_product_table_refund_item_label]', '[wfte_product_table_refund_item]'), array($refund_label, $refund_amount), $refund_items_row_html);
					}
				}
			}
			$find_replace[$refund_items_match[0]]=$refund_items_html;
		}
		return $find_replace;
	}

	public function set_design_view_data($find_replace, $html, $template_type)
	{
		$find_replace=$this->set_refund_entries($find_replace, $html, $template_type, null);
		$find_replace['[wfte_creditnote_number]']=123456;
		return $find_replace;
	}

	/**
	 *  Items needed to be converted to HTML for print/download
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{
			//Generate invoice number while printing Credit note
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order);	
			}			
			$find_replace=$this->set_other_data($find_replace,$template_type,$html,$order);

			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_billing_address($find_replace,$template_type,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html,$order);
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_charge_fields($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_order_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_fields($find_replace,$template_type,$html,$order);			
		}
		return $find_replace;
	}

	public function set_other_data($find_replace, $template_type, $html, $order)
	{
		add_filter('wf_pklist_alter_item_quantiy', array($this, 'alter_quantity_column'), 1, 5);
		//add_filter('wf_pklist_alter_item_total_formated', array($this, 'alter_total_price_column'), 1, 6);
		$find_replace=$this->set_refund_entries($find_replace, $html, $template_type, $order);
		$find_replace['[wfte_creditnote_number]']=self::generate_creditnote_number($order, true, true);
		return $find_replace; 
	}

	/**
	*	@since 4.0.4
	*	Alter total price of order item if the item is refunded
	*	
	*/
	public function alter_total_price_column($product_total_formated, $template_type, $product_total, $_product, $order_item, $order)
	{
		$item_id = $order_item->get_id();
		$new_total=$order->get_total_refunded_for_item($item_id);
		if($new_total>0)
		{
			$wc_version=WC()->version;
			$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
			$user_currency=get_post_meta($order_id,'_order_currency',true);
			$new_total=wc_price(($product_total-$new_total), array('currency'=>$user_currency));
			$product_total_formated='<del>'.$product_total_formated.'</del> &nbsp; <ins>'.$new_total.'</ins>';
		}
		return $product_total_formated;
	}

	/**
	*	@since 4.0.4
	*	Alter quantity of order item if the item is refunded
	*	
	*/
	public function alter_quantity_column($qty, $template_type, $_product, $order_item, $order)
	{
		$item_id = $order_item->get_id();
		$new_qty=$order->get_qty_refunded_for_item($item_id);
		if($new_qty<0)
		{
			$qty='<del>'.$qty.'</del> &nbsp; <ins>'.($qty+$new_qty).'</ins>';
		}
		return $qty;
	}

	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'doc_title'=>__('Document title','wf-woocommerce-packing-list'),
				'company_logo'=>__('Company Logo','wf-woocommerce-packing-list'),
				'order_date'=>__('Order Date','wf-woocommerce-packing-list'),
				'invoice_number'=>__('Invoice Number','wf-woocommerce-packing-list'),		
				'from_address'=>__('From Address','wf-woocommerce-packing-list'),
				'billing_address'=>__('Billing Address','wf-woocommerce-packing-list'),
				'shipping_address'=>__('Shipping Address','wf-woocommerce-packing-list'),
				'return_address'=>__('Return Address','wf-woocommerce-packing-list'),
				'email'=>__('Email Field','wf-woocommerce-packing-list'),
				'tel'=>__('Tel Field','wf-woocommerce-packing-list'),
				'shipping_method'=>__('Shipping Method','wf-woocommerce-packing-list'),
				'tracking_number'=>__('Tracking Number','wf-woocommerce-packing-list'),
				'product_table'=>__('Product Table','wf-woocommerce-packing-list'),
				'product_table_subtotal'=>__('Sub Total','wf-woocommerce-packing-list'),
				'product_table_shipping'=>__('Shipping','wf-woocommerce-packing-list'),
				'product_table_cart_discount'=>__('Cart Discount','wf-woocommerce-packing-list'),
				'product_table_order_discount'=>__('Order Discount','wf-woocommerce-packing-list'),
				'product_table_total_tax'=>__('Total Tax','wf-woocommerce-packing-list'),
				'product_table_fee'=>__('Fee','wf-woocommerce-packing-list'),
				'product_table_coupon'=>__('Coupon info','wf-woocommerce-packing-list'),
				'product_table_payment_method'=>__('Payment Method','wf-woocommerce-packing-list'),
				'product_table_payment_total'=>__('Total','wf-woocommerce-packing-list'),
				'footer'=>__('Footer','wf-woocommerce-packing-list'),
				'return_policy'=>__('Return Policy','wf-woocommerce-packing-list'),
			);
		}
		return $settings;
	}

	/*
	* These are the fields that have no customizable options, Just on/off
	* 
	*/
	public function get_non_options_fields($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'footer',
				'return_policy',
			);
		}
		return $settings;
	}

	/*
	* These are the fields that are not switchable
	* 
	*/
	public function get_non_disable_fields($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				
			);
		}
		return $settings;
	}
	public function default_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			$settings=array(
				'wf_'.$this->module_base.'_contactno_email'=>array('email','contact_number'),
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array(),
	        	'woocommerce_wf_add_creditnote_in_mail'=>'Yes',
			);
			if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
			{
				//sequential number settings
				$seq_settings=Wf_Woocommerce_Packing_List_Sequential_Number::get_sequential_field_default_settings();
				$seq_settings=(!is_array($seq_settings) ? array() : $seq_settings);
				$settings=array_merge($settings, $seq_settings);
			}
			return $settings;
		}else
		{
			return $settings;
		}
	}
	public function add_admin_pages($menus)
	{
		$menus[]=array(
			'submenu',
			WF_PKLIST_POST_TYPE,
			__('Credit Note','wf-woocommerce-packing-list'),
			__('Credit Note','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_creditnote']=__('Print Credit Note','wf-woocommerce-packing-list');
		return $actions;
	}

	private function generate_print_button_data($order, $order_id, $button_location="detail_page")
	{ 
		if($button_location=='detail_page')
		{
			$args=array(
				'button_type'=>'dropdown',
				'button_key'=>'creditnote_actions', //unique if multiple on same page
				'button_location'=>$button_location,
				'action'=>'',
				'label'=>__('Credit Note','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print/Download Credit Note','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0, //always 0
				'items'=>array(
					array(  
						'action'=>'print_creditnote',
						'label'=>__('Print','wf-woocommerce-packing-list'),
						'tooltip'=>__('Print Credit Note','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,						
					),
					array(
						'action'=>'download_creditnote',
						'label'=>__('Download','wf-woocommerce-packing-list'),
						'tooltip'=>__('Download Credit Note','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,
					)
				),
			);
		}else
		{
			$args=array(
				'action'=>'print_creditnote',
				'label'=>__('Credit Note','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Credit Note','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0,
				'button_location'=>$button_location,
			);
		}
		return $args;
	}

	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{
		if(self::generate_creditnote_number($order, false)!=='')
		{
			$btn_data=$this->generate_print_button_data($order, $order_id, $button_location);
			if($btn_data)
			{
				$item_arr[]=$btn_data;
			}
		}else
		{
			$refunds=$order->get_refunds();
			if($refunds) //refund data exists but creditnote number not generated.
			{
				//generate credit note number
				self::generate_creditnote_number($order, true);
				$btn_data=$this->generate_print_button_data($order, $order_id, $button_location);
				if($btn_data)
				{
					$item_arr[]=$btn_data;
				}
			}
		}
		return $item_arr;
	}


	public function add_email_attachments($attachments, $order, $order_id, $email_class_id)
	{ 
		$attach_to_mail_for=array('customer_partially_refunded_order', 'customer_refunded_order');
		$attach_to_mail_for=apply_filters('wf_pklist_alter_'.$this->module_base.'_attachment_mail_type', $attach_to_mail_for, $order_id, $email_class_id, $order);
		
		if(in_array($email_class_id, $attach_to_mail_for)) 
		{                    
           	if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_creditnote_in_mail', $this->module_id)== "Yes")
           	{          		
           		if(!is_null($this->customizer))
		        { 
		        	$order_ids=array($order_id);
		        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
		        	$this->customizer->template_for_pdf=true;
		        	$html=$this->generate_order_template($order_ids,$pdf_name);
		        	$attachments[]=$this->customizer->generate_template_pdf($html, $this->module_base, $pdf_name, 'attach');
		        }
           	}
        }
        return $attachments;
	}

	/**
	* 	@since 4.0.4
	*	Print credit note number details
	*/
	public function add_docdata_metabox($data_arr, $order, $order_id)
	{
		$refunds=$order->get_refunds();
		if($refunds)
		{
			//dummy array
			$data_arr[]=array(
				'label'=>'',
				'value'=>'',
			);
				
			foreach($refunds as $ref_order)
			{
				$creditnote_number=self::generate_creditnote_number($ref_order, false);
				$data_arr[]=array(
					'label'=>__('Credit Note Number','wf-woocommerce-packing-list'),
					'value'=>$creditnote_number,
				);

				$ref_id = (WC()->version < '2.7.0') ? $ref_order->id : $ref_order->get_id();
				$creditnote_date=self::get_creditnote_date($ref_id, get_option( 'date_format' ), $ref_order);
				$data_arr[]=array(
					'label'=>__('Credit Note Date','wf-woocommerce-packing-list'),
					'value'=>$creditnote_date,
				);
			}
		}
		return $data_arr;
	}

	
	public function admin_settings_page()
	{
		$order_statuses = wc_get_order_statuses();
		wp_enqueue_script('wc-enhanced-select');
		wp_enqueue_style('woocommerce_admin_styles',WC()->plugin_url().'/assets/css/admin.css');
		wp_enqueue_media();
		wp_enqueue_script($this->module_id,plugin_dir_url( __FILE__ ).'assets/js/main.js',array('jquery'),WF_PKLIST_VERSION);
		$params=array(
			'nonces' => array(
	            'main'=>wp_create_nonce($this->module_id),
	        ),
	        'ajax_url' => admin_url('admin-ajax.php'),
		);
		wp_localize_script($this->module_id,$this->module_id,$params);
		$the_options=Wf_Woocommerce_Packing_List::get_settings($this->module_id);
		
		
	    //initializing necessary modules, the argument must be current module name/folder
	    if(!is_null($this->customizer))
		{
			$this->customizer->init($this->module_base);
		}

		//initializing necessary modules, the argument must be current module name/folder
	    if(!is_null($this->seq_number))
		{
			$this->seq_number->init($this->module_base, __('Credit note'));
		}
		include(plugin_dir_path( __FILE__ ).'views/creditnote-admin-settings.php');
	}
	
	/* 
	* Print_window for invoice
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    { 
    	if($action=='print_creditnote' || $action=='download_creditnote')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
	        	$this->customizer->template_for_pdf=false;	        	
	        	$html=$this->generate_order_template($order_ids,$pdf_name);
				if($action=='download_creditnote')
	        	{
	        		$this->customizer->generate_template_pdf($html,$this->module_base,$pdf_name,'download');
	        	}else
	        	{
	        		echo $html;
	        	}
	        }else
	        {
	        	_e('Customizer module is not active.');
	        }
	        exit();
    	}
    }
    public function generate_order_template($orders,$page_title)
    {
    	$template_type=$this->module_base;
    	//taking active template html
    	$html=$this->customizer->get_template_html($template_type);
    	$style_blocks=$this->customizer->get_style_blocks($html);
    	$html=$this->customizer->remove_style_blocks($html,$style_blocks);
    	$out='';
    	if($html!="")
    	{
    		$number_of_orders=count($orders);
			$order_inc=0;
			foreach($orders as $order_id)
			{
				$order_inc++;
				$order=( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);

				$out.=$this->customizer->generate_template_html($html,$template_type,$order);
				if($number_of_orders>1 && $order_inc<$number_of_orders)
				{
                	$out.='<p class="pagebreak"></p>';
	            }else
	            {
	                //$out.='<p class="no-page-break"></p>';
	            }
			}
			$out=$this->customizer->append_style_blocks($out,$style_blocks);
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }
}
new Wf_Woocommerce_Packing_List_Creditnote();