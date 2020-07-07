<?php
/**
 * Packinglist section of the plugin
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Shippinglabel
{
	public $module_id='';
	public $module_base='shippinglabel';
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
		
		//add fields to customizer panel
		add_filter('wf_pklist_alter_customize_inputs',array($this,'alter_customize_inputs'),10,3);

		add_action('wt_print_doc',array($this,'print_it'),10,2);

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

		add_filter('wt_admin_menu',array($this,'add_admin_pages'),10,1);

		add_filter('wt_print_actions',array($this,'add_print_actions'),10,4);

		add_filter('wt_print_bulk_actions',array($this,'add_bulk_print_buttons'));
		add_filter('wt_frontend_print_actions',array($this,'add_frontend_print_buttons'),10,3);				
		add_filter('wt_email_print_actions',array($this,'add_email_print_buttons'),10,4);

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
	        	'woocommerce_wf_attach_'.$this->module_base=>array('type'=>'text_arr'),
	        	'wf_shipping_label_column_number'=>array('type'=>'int'),
	        	'woocommerce_wf_packinglist_label_size'=>array('type'=>'int'),
	        	'wf_custom_label_size_width'=>array('type'=>'float'),
	        	'wf_custom_label_size_height'=>array('type'=>'float'),
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
	        	'woocommerce_wf_attach_'.$this->module_base=>array(),
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

	public function alter_customize_inputs($fields,$type,$template_type)
	{
		if($template_type==$this->module_base)
		{
			if($type=='shipping_details')
			{
				$fields=array(			
					array(
						'label'=>__('Font size','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type,
						'unit'=>'px',
						'width'=>'49%',
					),
					array(
						'label'=>__('Order number label','wf-woocommerce-packing-list'),
						'css_prop'=>'html',
						'trgt_elm'=>'order_number_label',
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Weight label','wf-woocommerce-packing-list'),
						'css_prop'=>'html',
						'trgt_elm'=>'weight_label',
						'width'=>'49%',						
					),
					array(
						'label'=>__('Ship date label','wf-woocommerce-packing-list'),
						'css_prop'=>'html',
						'trgt_elm'=>'ship_date_label',
						'width'=>'49%',
						'float'=>'right',
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
				);
			}
			elseif($type=='from_address' || $type=='shipping_address')
			{
				$fields=array(
					array(
						'label'=>__('Title','wf-woocommerce-packing-list'),
						'css_prop'=>'html',
						'trgt_elm'=>$type.'_label',
					),
					array(
						'label'=>__('Title font size','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type.'_label',
						'width'=>'49%',
					),
					array(
						'label'=>__('Address font size','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type.'_val',
						'width'=>'49%',
						'float'=>'right',
					),
				);
			}
			elseif($type=='tracking_number' || $type=='tel')
			{
				$fields=array(
					array(
						'label'=>__('Title','wf-woocommerce-packing-list'),
						'css_prop'=>'html',
						'trgt_elm'=>$type.'_label',
					),
					array(
						'label'=>__('Title font size','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type,
					)
				);
			}
		}
		return $fields;
	}

	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$hide_on_empty_fields[]='wfte_qr_code';
			$hide_on_empty_fields[]='wfte_box_name';
			$hide_on_empty_fields[]='wfte_ship_date';
			$hide_on_empty_fields[]='wfte_weight';
			$hide_on_empty_fields[]='wfte_barcode';
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
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type);	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html);
			$find_replace=$this->extra_fields_dummy_data($find_replace,$html,$template_type);
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
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);					
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::package_doc_items($find_replace,$template_type,$order,$box_packing,$order_package);	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_order_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_fields($find_replace,$template_type,$html,$order);
		}
		return $find_replace;
	}

	/**
	 *  Dummy data for extra fields on template
	 */
	private function extra_fields_dummy_data($find_replace,$html,$template_type)
	{
		$find_replace['[wfte_weight]']='10 Kg';
		$find_replace['[wfte_ship_date]']=date('d-m-Y');
		$find_replace['[wfte_additional_data]']='';
		return $find_replace;
	}

	/**
	 *  Which items need enable in right customization panel
	 */
	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'company_logo'=>__('Company Logo','wf-woocommerce-packing-list'),
				'from_address'=>__('From Address','wf-woocommerce-packing-list'),
				'shipping_address'=>__('To Address','wf-woocommerce-packing-list'),				
				'shipping_details'=>__('Shipping Details','wf-woocommerce-packing-list'),
				'tracking_number'=>__('Tracking Number','wf-woocommerce-packing-list'),				
				'tel'=>__('Tel Field','wf-woocommerce-packing-list'),
				'barcode'=>__('Bar Code','wf-woocommerce-packing-list'),
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
				'barcode',
				'footer',
				'return_policy',
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
				'from_address',
				'shipping_address',
				'shipping_details',
			);
		}
		return $settings;
	}

	public function default_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'woocommerce_wf_enable_multiple_shipping_label'=>'No', //Enable multiple labels in one page
				'wf_shipping_label_column_number'=>2, //2 column
				'woocommerce_wf_packinglist_label_size'=>2, //full page
				'wf_custom_label_size_width'=>3,
				'wf_custom_label_size_height'=>4,
				'wf_'.$this->module_base.'_contactno_email'=>array('email','contact_number'),
				'woocommerce_wf_attach_'.$this->module_base=>array(),
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
			__('Shipping Label','wf-woocommerce-packing-list'),
			__('Shipping Label','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_shippinglabel']=__('Print Shipping Label','wf-woocommerce-packing-list');
		return $actions;
	}
	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{
		if($button_location=='detail_page')
		{
			$data_ar=array(
				'button_type'=>'dropdown',
				'button_key'=>'shippinglabel_actions', //unique if multiple on same page
				'button_location'=>$button_location,
				'action'=>'',
				'label'=>__('Shipping Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Shipping Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0, //always 0
				'items'=>array(),
			);
			$data_ar['items'][]=array(  
				'action'=>'print_shippinglabel',
				'label'=>__('Print','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Shipping Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0,
				'button_location'=>$button_location,						
			);
			if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_enable_multiple_shipping_label', $this->module_id)!='Yes')
			{
				$data_ar['tooltip']=__('Print/Download Shipping Label','wf-woocommerce-packing-list');
				$data_ar['items'][]=array(
					'action'=>'download_shippinglabel',
					'label'=>__('Download','wf-woocommerce-packing-list'),
					'tooltip'=>__('Download Shipping Label','wf-woocommerce-packing-list'),
					'is_show_prompt'=>0,
					'button_location'=>$button_location,
				);
			}

			$item_arr[]=$data_ar;
		}else
		{
			$item_arr[]=array(
				'action'=>'print_shippinglabel',
				'label'=>__('Shipping Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Shipping Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0,
				'button_location'=>$button_location,
			);
		}
		return $item_arr;
	}
	public function add_email_attachments($attachments,$order,$order_id,$status)
	{
		//add code here
        return $attachments;
	}
	public function add_email_print_buttons($html, $order, $order_id, $email_obj)
	{
		$show_print_button_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_attach_'.$this->module_base,$this->module_id);
        if(in_array('wc-'.$order->get_status(),$show_print_button_for))
        {
            Wf_Woocommerce_Packing_List::generate_print_button_for_user($order,$order_id,'print_shippinglabel',esc_html__('Print Shipping Label','wf-woocommerce-packing-list'),true); 
        }
	    return $html;
	}
	public function add_frontend_print_buttons($html,$order,$order_id)
	{
		return $html;
	}

	/**
	* Admin settings page
	* 
	*/
	public function admin_settings_page()
	{
		$order_statuses = wc_get_order_statuses();
		wp_enqueue_script('wc-enhanced-select');
		wp_enqueue_style('woocommerce_admin_styles',WC()->plugin_url().'/assets/css/admin.css');
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
		include(plugin_dir_path( __FILE__ ).'views/admin-settings.php');
	}
	
	
	/* 
	* Print_window for shippinglabel
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    {
    	if($action=='print_shippinglabel' || $action=='download_shippinglabel')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);

	        	//add custom size css here.
	        	if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_label_size',$this->module_id)==1) 
	        	{
	        		$this->customizer->custom_css.='
	        		.wfte_custom_shipping_size{
	        			width:'.Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_width',$this->module_id).'in !important;
	        			min-height:'.Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_height',$this->module_id).'in !important;
	        		}
	        		.wfte_main{ display:inline-block;}
	        		';
	        	}
	        	//RTL enabled
	        	if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_rtl_support')=='Yes')
	        	{
	        		$this->customizer->custom_css.='';
	        	}
	        	$this->customizer->template_for_pdf=($action=='download_shippinglabel' ? true : false);
	        	$html=$this->generate_order_template($order_ids,$pdf_name);
	        	if($action=='download_shippinglabel')
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
    	if(Wf_Woocommerce_Packing_List::is_from_address_available()===false) 
    	{
    		wp_die(__("Please add shipping from address in the plugin's general settings.",'wf-woocommerce-packing-list'), "", array());
        }

    	$template_type=$this->module_base;
    	//taking active template html
    	$html=$this->customizer->get_template_html($template_type);
    	$style_blocks=$this->customizer->get_style_blocks($html);
    	$html=$this->customizer->remove_style_blocks($html,$style_blocks);
    	$out='<style type="text/css">
    	.wfte_main{ margin:5px;}
    	div{ page-break-inside:avoid; }
    	</style>';
    	$out_arr=array();
    	if($html!="")
    	{
    		$is_single_page_print=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_enable_multiple_shipping_label',$this->module_id);
    		$label_column_number=Wf_Woocommerce_Packing_List::get_option('wf_shipping_label_column_number',$this->module_id);
			if((int) $label_column_number!=$label_column_number || (int) $label_column_number<=0)
			{
                $label_column_number=4;
            }

            //box packing
    		if (!class_exists('Wf_Woocommerce_Packing_List_Box_packing')) {
		        include_once WF_PKLIST_PLUGIN_PATH.'includes/class-wf-woocommerce-packing-list-box_packing.php';
		    }
	        $box_packing=new Wf_Woocommerce_Packing_List_Box_packing();
	        $order_pack_inc=0;
	        if($is_single_page_print=='Yes') //when paper size is not fit to handle labels, then shrink it or keep dimension, Default: shrink
			{
				$keep_label_dimension=false;
				$keep_label_dimension=apply_filters('wf_pklist_label_keep_dimension',$keep_label_dimension,$template_type);
			}
	        foreach ($orders as $order_id)
	        {
	        	$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
				$order_packages=null;
				$order_packages=$box_packing->create_order_package($order, $template_type);
				$number_of_order_package=count($order_packages);
				if(!empty($order_packages)) 
				{
					foreach ($order_packages as $order_package_id => $order_package)
					{
						if($is_single_page_print=='Yes')
						{
							if(($order_pack_inc%$label_column_number)==0)
							{
								if($order_pack_inc>0) //not starting of loop
								{
									$out.='</div>'; 
								}
								$flex_wrap=$keep_label_dimension ? 'wrap' : 'nowrap';
								$out.='<div style="align-items:start; display:flex; flex-direction:row; flex-wrap:'.$flex_wrap.'; align-content:flex-start; align-items:stretch;">'; //comment this line to give preference to label size
							}
						}
						$order_pack_inc++;
						$order=( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);						
						if($is_single_page_print=='No')
						{
							$out_arr[]=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);
						}else
						{
							$out.=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);	
						}						
					}
				}else
				{
					wp_die(__("Unable to print Packing slip. Please check the items in the order.",'wf-woocommerce-packing-list'), "", array());
				}
			}
			if($is_single_page_print=='Yes')
			{
				if($order_pack_inc>0) //items exists
				{
					$out.='</div>';
				}
			}else
			{
				$out=implode('<p class="pagebreak"></p>',$out_arr).'<p class="no-page-break">';
			}
			$out=$this->customizer->append_style_blocks($out,$style_blocks);
			//adding header and footer
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }
}
new Wf_Woocommerce_Packing_List_Shippinglabel();