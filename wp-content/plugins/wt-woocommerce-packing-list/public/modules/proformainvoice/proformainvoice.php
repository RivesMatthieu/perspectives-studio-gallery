<?php
/**
 * Proforma Invoice section of the plugin
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Proformainvoice
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='proformainvoice';
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

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

		//initializing Sequential Number
		$this->seq_number=Wf_Woocommerce_Packing_List::load_modules('sequential-number');

		add_filter('wt_admin_menu',array($this,'add_admin_pages'),10,1);
		add_filter('wt_print_docdata_metabox',array($this,'add_docdata_metabox'),10,3);
		add_filter('wt_print_actions',array($this,'add_print_actions'),10,4);				
		
		add_filter('wt_email_attachments',array($this,'add_email_attachments'),10,4);

		add_filter('wt_pklist_alter_tooltip_data',array($this,'register_tooltips'),1);
		add_filter('wf_pklist_alter_dummy_data_for_customize',array($this,'dummy_data_for_customize'),10,3);
		add_filter('wt_frontend_print_actions',array($this,'add_frontend_print_buttons'),10,3);	
		add_filter('wt_email_print_actions',array($this,'add_email_print_buttons'),10,4);

		//hook to wc status changed for generating invoice number on order status change, (If user set the order status)
		add_filter('wf_pklist_enable_sequential_number_on_status_change',array($this,'generate_invoice_number_on_order_status_change'),10,2);

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
				'woocommerce_wf_attach_'.$this->module_base=>array('type'=>'text_arr'),  
				'wf_'.$this->module_base.'_contactno_email'=>array('type'=>'text_arr'),
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array('type'=>'text_arr'),
	        	'woocommerce_wf_generate_for_orderstatus'=>array('type'=>'text_arr'),	        	      	
	        	'woocommerce_wf_packinglist_footer'=>array('type'=>'textarea'),
	        	'woocommerce_wf_packinglist_special_notes'=>array('type'=>'textarea'),
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
				'woocommerce_wf_attach_'.$this->module_base=>array(),
	        	'wf_'.$this->module_base.'_contactno_email'=>array(),
				'wf_'.$this->module_base.'_product_meta_fields'=>array(),
	        	'woocommerce_wf_generate_for_orderstatus'=>array(),	        	
	        );
		}
		return $arr;
	}


	/**
	*	@since 4.0.4	Generate invoice number on order status, If user set status to generate invoice number
	*	
	*/
	public function generate_invoice_number_on_order_status_change($module_id_arr, $order_id)
	{
		$module_id_arr[$this->module_id]=array(__CLASS__,'generate_invoice_number');
		return $module_id_arr;
	}

	public function add_email_print_buttons($html,$order,$order_id, $email_obj)
	{
		return $this->add_print_button($html, $order, $order_id, true);
	}
	public function add_frontend_print_buttons($html, $order,$order_id)
	{
		return $this->add_print_button($html, $order, $order_id);
	}

	public function add_print_button($html, $order, $order_id, $email_button=false)
	{
		$show_on_frontend=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_frontend_info',$this->module_id);
		if($show_on_frontend=='Yes')
		{
			$show_print_button_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_attach_'.$this->module_base, $this->module_id);
	        if(in_array('wc-'.$order->get_status(), $show_print_button_for))
	        {
	            Wf_Woocommerce_Packing_List::generate_print_button_for_user($order,$order_id, 'print_'.$this->module_base, esc_html__('Print Proforma Invoice', 'wf-woocommerce-packing-list'), $email_button); 
	        }
	    }
	    return $html;
	}

	/**
    * @since 4.0.4
    * Adding dummy data for customize view for custom fields in picklist
    */
    public function dummy_data_for_customize($find_replace,$template_type,$html)
    {
    	$find_replace['[wfte_proforma_invoice_number]']=123456;
    	$invoice_date_format=Wf_Woocommerce_Packing_List_CustomizerLib::get_template_html_attr_vl($html,'data-proforma_invoice_date-format','m/d/Y');
    	$find_replace['[wfte_proforma_invoice_date]']=date($invoice_date_format);
    	return $find_replace;
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

	/**
	*	@since 4.0.8 added email class id checking and filter to alter email class id
	*/
	public function add_email_attachments($attachments, $order, $order_id, $email_class_id)
	{ 
		if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_'.$this->module_base.'_in_mail',$this->module_id)== "Yes")
        {
        	/* check order email types */		
			$attach_to_mail_for=array('new_order', 'customer_completed_order', 'customer_invoice', 'customer_on_hold_order', 'customer_processing_order');
			$attach_to_mail_for=apply_filters('wf_pklist_alter_'.$this->module_base.'_attachment_mail_type', $attach_to_mail_for, $order_id, $email_class_id, $order);

			/* check order statuses to generate invoice */
			$generate_invoice_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $this->module_id);

			if(in_array('wc-'.$order->get_status(), $generate_invoice_for) && in_array($email_class_id, $attach_to_mail_for)) 
			{                              	 
           		if(!is_null($this->customizer))
		        { 
		        	$order_ids=array($order_id);
		        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
		        	$this->customizer->template_for_pdf=true;
		        	$html=$this->generate_order_template($order_ids,$pdf_name);
		        	$attachments[]=$this->customizer->generate_template_pdf($html,$this->module_base, $pdf_name, 'attach');
		        }
           	}
        }
        return $attachments;
	}

	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$hide_on_empty_fields[]='wfte_special_notes';
			$hide_on_empty_fields[]='wfte_transport_terms';
			$hide_on_empty_fields[]='wfte_sale_terms';
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
			//Generate Proforma Invoice number while printing Proforma Invoice
			self::generate_invoice_number($order);			

			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_billing_address($find_replace,$template_type,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html,$order);
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_charge_fields($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_order_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_fields($find_replace,$template_type,$html,$order);
			$find_replace=$this->set_other_data($find_replace,$template_type,$html,$order);
		}
		return $find_replace;
	}

	public function set_other_data($find_replace,$template_type,$html,$order)
	{ 	
		if(!is_null($this->seq_number))
		{
			$find_replace['[wfte_proforma_invoice_number]']=self::generate_invoice_number($order);

			//invoice date
			$invoice_date_match=array();
			$invoice_date_format='m/d/Y';
			if(preg_match('/data-proforma_invoice_date-format="(.*?)"/s',$html,$invoice_date_match))
			{
				$invoice_date_format=$invoice_date_match[1];
			}
			$wc_version=WC()->version;
			$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
			//must call this line after `generate_sequential_number` call
			$invoice_date=self::get_invoice_date($order_id,$invoice_date_format,$order);
			$invoice_date=apply_filters('wf_pklist_alter_proforma_invoice_date',$invoice_date,$template_type,$order);
			$find_replace['[wfte_proforma_invoice_date]']=$invoice_date;
		}else
		{
			$find_replace['[wfte_proforma_invoice_number]']='';
			$find_replace['[wfte_proforma_invoice_date]']='';
		}
		return $find_replace;
	}

	/**
	* Get proforma invoice date
	* @since  	4.0.4
	* @return mixed
	*/
    public static function get_invoice_date($order_id, $date_format, $order)
    {
    	if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
	    {
    		return Wf_Woocommerce_Packing_List_Sequential_Number::get_sequential_date($order_id, 'wf_proforma_invoice_date', $date_format, $order);
    	}else
    	{
    		return '';
    	}
    }

    /**
	* 	Function to generate proforma invoice number
	* 	@since 4.0.4
	* 	@return mixed
	*/
    public static function generate_invoice_number($order, $force_generate=true) 
    {
	    if(Wf_Woocommerce_Packing_List_Admin::module_exists('sequential-number'))
	    {
	    	return Wf_Woocommerce_Packing_List_Sequential_Number::generate_sequential_number($order, self::$module_id_static, array('number'=>'wf_proforma_invoice_number', 'date'=>'wf_proforma_invoice_date', 'enable'=>''), $force_generate);
	    }else
	    {
	    	return '';
	    }
	}

	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'doc_title'=>__('Document title','wf-woocommerce-packing-list'),
				'company_logo'=>__('Company Logo','wf-woocommerce-packing-list'),
				'proforma_invoice_number'=>__('Proforma Invoice Number','wf-woocommerce-packing-list'),
				'proforma_invoice_date'=>__('Proforma Invoice Date','wf-woocommerce-packing-list'),
				'order_number'=>__('Order Number','wf-woocommerce-packing-list'),
				'order_date'=>__('Order Date','wf-woocommerce-packing-list'),			
				'from_address'=>__('From Address','wf-woocommerce-packing-list'),
				'billing_address'=>__('Billing Address','wf-woocommerce-packing-list'),
				'shipping_address'=>__('Shipping Address','wf-woocommerce-packing-list'),
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
				'transport_terms'=>__('Transport terms','wf-woocommerce-packing-list'),
				'sale_terms'=>__('Sale terms','wf-woocommerce-packing-list'),
				'special_notes'=>__('Special notes','wf-woocommerce-packing-list'),
				'footer'=>__('Footer','wf-woocommerce-packing-list'),
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
				'footer','special_notes','transport_terms','sale_terms'
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
	        	'woocommerce_wf_attach_'.$this->module_base=>array(),
	        	'woocommerce_wf_packinglist_footer'=>'',
	        	'woocommerce_wf_add_'.$this->module_base.'_in_mail'=>'No',
	        	'wt_pklist_show_individual_tax_column'=>'No',
	        	'woocommerce_wf_packinglist_special_notes'=>'',
				'woocommerce_wf_packinglist_frontend_info'=>'No',
				'woocommerce_wf_generate_for_orderstatus'=> array(),
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
			__('Proforma Invoice','wf-woocommerce-packing-list'),
			__('Proforma Invoice','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_proformainvoice']=__('Print Proforma Invoice','wf-woocommerce-packing-list');
		return $actions;
	}

	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{		
		$invoice_number=self::generate_invoice_number($order,false);
		$generate_invoice_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
		$is_show=0;
		$is_show_prompt=1;

		if(in_array(get_post_status($order_id), $generate_invoice_for) || !empty($invoice_number))
        {
        	$is_show_prompt=0;
        	$is_show=1;
		}else
		{
			if(empty($invoice_number))
			{
				$is_show_prompt=1;
				$is_show=1;
			}
		}
		if($is_show==1)
		{
			//$is_show_prompt value is also using as prompt box text
			$is_show_prompt=($is_show_prompt==1 ? __('Proforma Invoice','wf-woocommerce-packing-list') : $is_show_prompt);

			//for print button
			$btn_args=array(  
				'action'=>'print_proformainvoice',
				'tooltip'=>__('Print Proforma Invoice','wf-woocommerce-packing-list'),
				'is_show_prompt'=>$is_show_prompt,
				'button_location'=>$button_location,						
			);

			//for download button
			$btn_args_dw=array(
				'action'=>'download_proformainvoice',
				'tooltip'=>__('Download Proforma Invoice','wf-woocommerce-packing-list'),
				'is_show_prompt'=>$is_show_prompt,
				'button_location'=>$button_location,
			);

			if($button_location=='detail_page')
			{
				$btn_args['label']=__('Print','wf-woocommerce-packing-list');
				$btn_args_dw['label']=__('Download','wf-woocommerce-packing-list');
				$item_arr[]=array(
					'button_type'=>'dropdown',
					'button_key'=>'proformainvoice_actions', //unique if multiple on same page
					'button_location'=>$button_location,
					'action'=>'',
					'label'=>__('Proforma Invoice','wf-woocommerce-packing-list'),
					'tooltip'=>__('Print/Download Proforma Invoice','wf-woocommerce-packing-list'),
					'is_show_prompt'=>0, //always 0
					'items'=>array(
						$btn_args,
						$btn_args_dw
					),
				);
			}else
			{
				$btn_args['label']=__('Print Proforma Invoice','wf-woocommerce-packing-list');
				$btn_args_dw['label']=__('Download Proforma Invoice','wf-woocommerce-packing-list');
				$item_arr[]=$btn_args;
				$item_arr[]=$btn_args_dw;			
			}
		}
		return $item_arr;
	}

	/**
	* 	@since 4.0.4
	*	Print invoice number details
	*/
	public function add_docdata_metabox($data_arr, $order, $order_id)
	{
		$invoice_number=self::generate_invoice_number($order, false);
		if($invoice_number!="")
		{
			if(count($data_arr)>0) //if previous item exists
			{
				//dummy array
				$data_arr[]=array(
					'label'=>'',
					'value'=>'',
				);
			}
				
			$data_arr[]=array(
				'label'=>__('Proforma Invoice Number','wf-woocommerce-packing-list'),
				'value'=>$invoice_number,
			);

			$invoice_date=self::get_invoice_date($order_id, get_option( 'date_format' ), $order);
			$data_arr[]=array(
				'label'=>__('Proforma Invoice Date','wf-woocommerce-packing-list'),
				'value'=>$invoice_date,
			);
		}
		return $data_arr;
	}

	public function admin_settings_page()
	{
		$order_statuses = wc_get_order_statuses();
		$wf_generate_invoice_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $this->module_id);
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
			$this->seq_number->init($this->module_base, __('Proforma Invoice'));
		}

		include(plugin_dir_path( __FILE__ ).'views/proformainvoice-admin-settings.php');
	}
	
	/* 
	* Print_window for invoice
	* @param $orders : order ids
	*/    
    public function print_it($order_ids, $action) 
    { 
    	if($action=='print_proformainvoice' || $action=='download_proformainvoice')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
	        	$this->customizer->template_for_pdf=($action=='download_proformainvoice' ? true : false);	        	
	        	$html=$this->generate_order_template($order_ids,$pdf_name);				
				if($action=='download_proformainvoice')
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
new Wf_Woocommerce_Packing_List_Proformainvoice();