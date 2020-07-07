<?php
/**
 * Picklist section of the plugin
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Picklist
{
	public $module_id='';
	public $module_base='picklist';
    private $customizer=null;
    public static $order_ids=array();
    public static $order_numbers=array();
    public static $attachment_files=array();
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
		
		add_filter('wf_pklist_alter_dummy_data_for_customize',array($this,'dummy_data_for_customize'),10,3);

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


		/** 
		* @since 4.0.8 Separate email option 
		*/
		$vl=Wf_Woocommerce_Packing_List::get_option('wt_pklist_separate_email', $this->module_id);
		if($vl=='Yes')
		{
			define('WF_PKLIST_PICKLIST_EMAIL_TEMPLATE_PATH',untrailingslashit( plugin_dir_path( __FILE__ ) ).'/templates/');
			add_filter('woocommerce_email_classes',array($this,'add_email_class'));
			add_action('woocommerce_order_actions', array($this, 'add_email_order_action'));
			add_action('woocommerce_order_action_wf_pklist_send_'.$this->module_base.'_email',array($this,'send_separate_email'));
		}
		

		/** 
		* @since 4.0.8 Add email attachment
		*/
		add_filter('wt_email_attachments', array($this,'add_email_attachments'), 10, 4);
	}

	/**
	*	Add email attachment
	*	@since 4.0.8
	*/
	public function add_email_attachments($attachments, $order, $order_id, $email_class_id)
	{ 
		$attach_to_mail_for=array('new_order', 'customer_completed_order', 'customer_invoice', 'customer_on_hold_order', 'customer_processing_order');
		$attach_to_mail_for=apply_filters('wf_pklist_alter_'.$this->module_base.'_attachment_mail_type', $attach_to_mail_for, $order_id, $email_class_id, $order);
		
		$is_attach=false;
		if(in_array($email_class_id, $attach_to_mail_for)) 
		{
			/* check order statuses to generate picklist */
			$generate_picklist_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $this->module_id);
			if(in_array('wc-'.$order->get_status(), $generate_picklist_for))
			{
				$is_attach=true;
			}
		}

		/* separate email */
		if($email_class_id=='wt_pklist_'.$this->module_base.'_email')
		{
			$is_attach=true;
		}

		if($is_attach) 
		{                    
           	if(!is_null($this->customizer))
	        { 
	        	$order_ids=array($order_id);
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base, $order_ids);

	        	/* check the PDF was already generated in this hook */
	        	$attachment_file=$this->customizer->is_pdf_generated(self::$attachment_files, $pdf_name);

	        	if(!$attachment_file)
	        	{
	        		$this->customizer->template_for_pdf=true;
	        		$html=$this->generate_order_template($order_ids, $pdf_name);
	        		$attachment_file=$this->customizer->generate_template_pdf($html, $this->module_base, $pdf_name, 'attach');
	        		self::$attachment_files[]=$attachment_file;
	        	}	        	
	        	$attachments[]=$attachment_file;
	        }
        }
        return $attachments;
	}

	/**
	 * 	Send separate email
	 *	@since 4.0.8
	 * 	@param \WC_Order $order
	 */
	function send_separate_email($order)
	{	    
	    $message = sprintf(__( 'Picklist email send by %s.', 'wf-woocommerce-packing-list'), wp_get_current_user()->display_name );
	    $order->add_order_note($message);

	    $wc_version=WC()->version;
		$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();

	    WC()->mailer()->emails['Wf_Woocommerce_Packing_List_Picklist_Email']->trigger($order_id, $order);
	}

	/**
	 * 	Add Picklist email option to order actions select box on edit order page
	 *	@since 4.0.8
	 * 	@param array $actions order actions array to display
	 * 	@return array - updated actions
	 */
	function add_email_order_action($actions)
	{
	    $actions['wf_pklist_send_'.$this->module_base.'_email']=__('Send Picklist email', 'wf-woocommerce-packing-list');
	    return $actions;
	}


	/**
	 * 	Add Picklist email to the WooCommerce Email list
	 *	@since 4.0.8
	 * 	@param array $email_classes email classes array
	 * 	@return array - new class list
	 */
	public function add_email_class($email_classes)
	{
		include_once plugin_dir_path(__FILE__)."classes/class-".$this->module_base."-email.php";
		$email_classes['Wf_Woocommerce_Packing_List_Picklist_Email'] = new Wf_Woocommerce_Packing_List_Picklist_Email();
		return $email_classes;
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
	        	'wf_'.$this->module_base.'_product_meta_fields'=>array('type'=>'text_arr'),
	        	'woocommerce_wf_attach_'.$this->module_base=>array('type'=>'text_arr'), 
	        	'woocommerce_wf_generate_for_orderstatus'=>array('type'=>'text_arr'), 
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
				'wf_'.$this->module_base.'_product_meta_fields'=>array(),
				'woocommerce_wf_attach_'.$this->module_base=>array(),
				'woocommerce_wf_generate_for_orderstatus'=>array(),	        	
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
			$hide_on_empty_fields[]='wfte_box_name';
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
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html);
		}
		return $find_replace;
	}

	/**
	 *  Items needed to be converted to HTML for print
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{			
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html,$order,$box_packing,$order_package);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);	
			$find_replace=self::set_other_data($find_replace,$template_type,$html,$order,$box_packing,$order_package);	
		}
		return $find_replace;
	}

	public static function set_other_data($find_replace, $template_type, $html, $order, $box_packing=null, $order_package=null)
	{ 				
		$find_replace['[wfte_order_count]']=count(self::$order_numbers);
    	$find_replace['[wfte_order_number_list]']=implode(", ", self::$order_numbers);
		return $find_replace;
	}

	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'company_logo'=>__('Company Logo','wf-woocommerce-packing-list'),
				'from_address'=>__('From Address','wf-woocommerce-packing-list'),
				'product_table'=>__('Product Table','wf-woocommerce-packing-list'),
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
				'footer',
			);
		}
		return $settings;
	}

	/*
	* These are the fields that are switchable
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
				'wf_woocommerce_product_category_wise_splitting'=>'Yes',
				'wf_woocommerce_product_order_wise_splitting'=>'No',
				'wf_'.$this->module_base.'_product_meta_fields'=>array(),
				'woocommerce_wf_packinglist_variation_data'=>'Yes',
				'woocommerce_wf_packinglist_exclude_virtual_items'=>'No',
				'woocommerce_wf_attach_'.$this->module_base=>array(),
				'woocommerce_wf_generate_for_orderstatus'=>array(),
				'wt_pklist_separate_email'=>'No',
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
			__('Picklist','wf-woocommerce-packing-list'),
			__('Picklist','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_picklist']=__('Print Picklist','wf-woocommerce-packing-list');
		return $actions;
	}

	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{
		if($button_location=='detail_page')
		{
			$item_arr[]=array(
				'button_type'=>'dropdown',
				'button_key'=>'picklist_actions', //unique if multiple on same page
				'button_location'=>$button_location,
				'action'=>'',
				'label'=>__('Picklist','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print/Download Picklist','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0, //always 0
				'items'=>array(
					array(  
						'action'=>'print_picklist',
						'label'=>__('Print','wf-woocommerce-packing-list'),
						'tooltip'=>__('Print Picklist','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,						
					),
					array(
						'action'=>'download_picklist',
						'label'=>__('Download','wf-woocommerce-packing-list'),
						'tooltip'=>__('Download Picklist','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,
					)
				),
			);
		}else
		{
			$item_arr[]=array(
				'action'=>'print_picklist',
				'label'=>__('Picklist','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Picklist','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0,
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

		$email_settings_path=admin_url('admin.php?page=wc-settings&tab=email&section=wf_woocommerce_packing_list_'.$this->module_base.'_email');

		include(plugin_dir_path( __FILE__ ).'views/admin-settings.php');
	}
	
	/* 
	* Print_window for packinglist
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    {
    	if($action=='print_picklist' || $action=='download_picklist')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
	        	$this->customizer->template_for_pdf=($action=='download_picklist' ? true : false);	        	
	        	$html=$this->generate_order_template($order_ids, $pdf_name);				
				if($action=='download_picklist')
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
    	self::$order_ids=$orders;
		self::$order_numbers=array();
    	if($html!="")
    	{
    		if (!class_exists('Wf_Woocommerce_Packing_List_Box_packing')) {
		        include_once WF_PKLIST_PLUGIN_PATH.'includes/class-wf-woocommerce-packing-list-box_packing.php';
		    }
	        $box_packing=new Wf_Woocommerce_Packing_List_Box_packing();
	        
	        $order_package_arr=array();
	        foreach($orders as $order_id)
	        {
	        	$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
				$order_packages=$box_packing->create_order_package($order, $template_type);
				self::$order_numbers[]=$order->get_order_number();
				if(!empty($order_packages)) 
				{
					foreach($order_packages as $order_package_id =>$order_package)
					{
						foreach($order_package as $id=>$item)
	            		{
	            			$exclude=0;
	            			if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_exclude_virtual_items',$this->module_id)=='Yes') //
	            			{
	            				$_product=wc_get_product($item['id']);            				
	            				if($_product->is_virtual('yes'))
	            				{
	            					$exclude=1;
	            				}
	            			}
	            			if($exclude==0)
	            			{
	            				$item['order']=$order;
	            				$order_package_arr[]=$item;
	            			}
	            		}
					}
				}else
				{
					wp_die(__("Unable to print Picklist. Please check the items in the order.",'wf-woocommerce-packing-list'), "", array());
				}
	        }
			$out=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package_arr);
			$out=$this->customizer->append_style_blocks($out,$style_blocks);
			//adding header and footer
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }

    /**
    * @since 4.0.3 
    * Adding dummy data for customize view for custom fields in picklist
    */
    public function dummy_data_for_customize($find_replace,$template_type,$html)
    {
    	$find_replace['[wfte_order_count]']=2;
    	$find_replace['[wfte_order_number_list]']="1234, 4321";
    	return $find_replace;
    }
}
new Wf_Woocommerce_Packing_List_Picklist();