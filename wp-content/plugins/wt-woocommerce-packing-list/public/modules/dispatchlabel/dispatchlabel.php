<?php
/**
 * Dispatch Label section of the plugin
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Dispatchlabel
{
	public $module_id='';
	public $module_base='dispatchlabel';
    private $customizer=null;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
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

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

		add_filter('wt_admin_menu',array($this,'add_admin_pages'),10,1);
		add_filter('wt_print_actions',array($this,'add_print_actions'),10,4);
		add_filter('wt_print_bulk_actions',array($this,'add_bulk_print_buttons'));				

		add_filter('wt_pklist_alter_tooltip_data',array($this,'register_tooltips'),1);

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
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array('type'=>'text_arr'),
			);
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
				'wf_'.$this->module_base.'_product_meta_fields'=>array(),
	        );
		}
		return $arr;
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
			$hide_on_empty_fields[]='wfte_dispatch_date';
			$hide_on_empty_fields[]='wfte_return_address';
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
		}
		return $find_replace;
	}

	/**
	 *  Items needed to be converted to HTML for print/download
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{
			//Generate invoice number while printing Dispatch label
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order);	
			}			

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

	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'company_logo'=>__('Company Logo','wf-woocommerce-packing-list'),
				'order_date'=>__('Order Date','wf-woocommerce-packing-list'),
				'invoice_number'=>__('Invoice Number','wf-woocommerce-packing-list'),
				'dispatch_date'=>__('Dispatch Date','wf-woocommerce-packing-list'),			
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
			return array(
				'wf_'.$this->module_base.'_contactno_email'=>array('email','contact_number'),
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array(),
			);
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
			__('Dispatch Label','wf-woocommerce-packing-list'),
			__('Dispatch Label','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_dispatchlabel']=__('Print Dispatch Label','wf-woocommerce-packing-list');
		return $actions;
	}
	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{
		$is_show_prompt=1;
		if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
		{
			$invoice_number=Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,false);
			if(!empty($invoice_number))
	        {
	        	$is_show_prompt=0;
			}
		}else
		{
			$invoice_number='';
			$is_show_prompt=0;
		}

		if($button_location=='detail_page')
		{
			$item_arr[]=array(
				'button_type'=>'dropdown',
				'button_key'=>'dispatchlabel_actions', //unique if multiple on same page
				'button_location'=>$button_location,
				'action'=>'',
				'label'=>__('Dispatch Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print/Download Dispatch Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0, //always 0
				'items'=>array(
					array(  
						'action'=>'print_dispatchlabel',
						'label'=>__('Print','wf-woocommerce-packing-list'),
						'tooltip'=>__('Print Dispatch Label','wf-woocommerce-packing-list'),
						'is_show_prompt'=>$is_show_prompt,
						'button_location'=>$button_location,						
					),
					array(
						'action'=>'download_dispatchlabel',
						'label'=>__('Download','wf-woocommerce-packing-list'),
						'tooltip'=>__('Download Dispatch Label','wf-woocommerce-packing-list'),
						'is_show_prompt'=>$is_show_prompt,
						'button_location'=>$button_location,
					)
				),
			);
		}else
		{
			$item_arr[]=array(
				'action'=>'print_dispatchlabel',
				'label'=>__('Dispatch Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Dispatch Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>$is_show_prompt,
				'button_location'=>$button_location,
			);
		}
		return $item_arr;
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
		include(plugin_dir_path( __FILE__ ).'views/dispatchlabel-admin-settings.php');
	}
	
	/* 
	* Print_window for invoice
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    { 
    	if($action=='print_dispatchlabel' || $action=='download_dispatchlabel')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
	        	$this->customizer->template_for_pdf=($action=='download_dispatchlabel' ? true : false);	        	
	        	$html=$this->generate_order_template($order_ids,$pdf_name);				
				if($action=='download_dispatchlabel')
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
new Wf_Woocommerce_Packing_List_Dispatchlabel();