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

class Wf_Woocommerce_Packing_List_Addresslabel
{
	public $module_id='';
	public $module_base='addresslabel';
    private $customizer=null;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);
		add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
		add_filter('wf_module_non_customizable_items',array($this,'get_non_customizable_items'),10,2);
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
	
	}

	public function alter_customize_inputs($fields,$type,$template_type)
	{
		if($template_type==$this->module_base)
		{
			if($type=='addresslabel_templates')
			{
				include plugin_dir_path(__FILE__).'data/data.templates.php';
				$select_arr=array(''=>'--'.__('Select one','wf-woocommerce-packing-list').'--');
				if(isset($template_arr) && is_array($template_arr))
				{
					foreach($template_arr as $template_key=>$template_item)
					{
						$select_arr[$template_key]=$template_item['title'];
					}
				}				
				$fields=array(			
					array(
						'label'=>__('Layouts','wf-woocommerce-packing-list'),
						'type'=>'select',
						'event_class'=>'wf_cst_change_addrlabel',
						'select_options'=>$select_arr,
					),
				);
			}
			elseif($type=='addresslabel_data')
			{
				$fields=array(			
					array(
						'label'=>__('Width','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-width',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_w_in',
						'width'=>'49%',
					),
					array(
						'label'=>__('Height','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-height',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_h_in',
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Rows','wf-woocommerce-packing-list'),
						'css_prop'=>'attr-data-rows',
						'trgt_elm'=>$type,						
						'width'=>'49%',
					),
					array(
						'label'=>__('Columns','wf-woocommerce-packing-list'),
						'css_prop'=>'attr-data-columns',
						'trgt_elm'=>$type,
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Margin top','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-margin-top',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_mtop_in',
						'width'=>'49%',
					),
					array(
						'label'=>__('Margin right','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-margin-right',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_mright_in',
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Margin bottom','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-margin-bottom',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_mbottom_in',
						'width'=>'49%',
					),
					array(
						'label'=>__('Margin left','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-margin-left',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_mleft_in',
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Column spacing','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-column-spacing',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_colsp_in',
						'width'=>'49%',
					),
					array(
						'label'=>__('Rows spacing','wf-woocommerce-packing-list'),
						'type'=>'text_inputgrp',
						'unit'=>'in',
						'addonblock'=>'in',
						'css_prop'=>'attr-data-row-spacing',
						'trgt_elm'=>$type,
						'preview_elm'=>'addr_rowsp_in',
						'width'=>'49%',
						'float'=>'right',
					),
					array(
						'label'=>__('Description','wf-woocommerce-packing-list'),
						'css_prop'=>'attr-data-description',
						'trgt_elm'=>$type,
						'preview_elm'=>'',
					),
				);
			}
		}
		return $fields;
	}

	/**
	 *  Which elements needs to be hidden when its content is empty
	 */
	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$hide_on_empty_fields[]='wfte_addresslabel_extradata';
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
			$find_replace=array(); //remove all placeholders
			$find_replace[$html]=$this->generate_preview_html($html);
		}
		return $find_replace;
	}

	public static function get_template_html_attr_vl($html,$attr,$default=1)
	{
		$match_arr=array();
		$out=$default;
		if(preg_match('/'.$attr.'="(.*?)"/s',$html,$match_arr))
		{
			$out=$match_arr[1]*1;
			$out=($out==0 ? $default : $out);
		}
		return $out;
	}

	/**
	 *  Generate preview HTML from template data
	 */
	protected function generate_preview_html($html)
	{
		$div_attr_match=array();
		$div_attr='';
		if(preg_match('/<div(.*?)>/s',$html,$div_attr_match))
		{
			$div_attr=$div_attr_match[1];
		}

		$row_count=floor($this->get_template_html_attr_vl($html,'data-rows'));
		$col_count=floor($this->get_template_html_attr_vl($html,'data-columns'));
		$col_w=$this->get_template_html_attr_vl($html,'data-width');
		$col_h=$this->get_template_html_attr_vl($html,'data-height');
		$col_mt=$this->get_template_html_attr_vl($html,'data-margin-top',0);
		$col_mr=$this->get_template_html_attr_vl($html,'data-margin-right',0);
		$col_mb=$this->get_template_html_attr_vl($html,'data-margin-bottom',0);
		$col_ml=$this->get_template_html_attr_vl($html,'data-margin-left',0);
		$col_cs=$this->get_template_html_attr_vl($html,'data-column-spacing',0);
		$col_rs=$this->get_template_html_attr_vl($html,'data-row-spacing',0);

		//column/font size  calculation
		$base_column=3;
		$base_font=10;
		$base_line_height=18;
		$base_mtop=60;
		$base_cmtop=6;
		$base_cstop=20; //row space
		$base_csleft=-90;
		$base_csinleft=-2;
		$base_cspw=150;
		$current_ratio=$base_column/$col_count;
		$current_font=round($base_font*$current_ratio);
		$current_line_height=round($base_line_height*$current_ratio);
		$current_mtop=round($base_mtop*$current_ratio);
		$current_cmtop=round($base_cmtop*$current_ratio);
		$current_cstop=round($base_cstop*$current_ratio);
		$current_csleft=round($base_csleft*$current_ratio);
		$current_csinleft=round($base_csinleft*$current_ratio);
		$current_cspw=round($base_cspw*$current_ratio);

		$out='
		<style type="text/css">
		.wfte_addresslabel_data{ width:100%;}
		.wfte_addresslabel_data td{ font-size:'.$current_font.'px;}
		.wfte_shipping_address_main{ margin:3%; text-align:center;border:solid 1px #ccc; padding:5px;}
		.wfte_shipping_address{ display:inline-block; text-align:left; line-height:'.$current_line_height.'px;}

		
		.wfte_unit_boxvl{background:#fff; padding:0px 2px; display:inline-block; font-style:italic; color:grey; line-height:'.$current_line_height.'px;}

		.wfte_addr_w{ margin-top:-15px; margin-left:0px; width:inherit; text-align:center; }

		.wfte_addr_h{ margin-left:-3px; margin-top:'.$current_mtop.'px; display:inline-block; position:absolute;}

		.wfte_addr_rowsp{margin-top:'.$current_cmtop.'px; position:absolute;}
		.wfte_addr_rowsp_in{ text-align:left; background:none;}

		.wfte_addresslabel_data tr:last-child .wfte_addr_colsp{ display:none;}
		.wfte_addresslabel_data tr td:first-child .wfte_addr_colsp{ display:none;}

		.wfte_addr_colsp{transform: rotate(270deg); position:absolute; margin-top:'.$current_cstop.'px; margin-left:'.$current_csleft.'px; }
		.wfte_addr_colsp_in{width:'.$current_cspw.'px; margin-left:'.$current_csinleft.'px; background:none; text-align:right;}

		.wfte_addresslabel_data tr td:first-child .wfte_addr_rowsp{ display:none;}
		.wfte_addresslabel_data tr:last-child .wfte_addr_rowsp{ display:none;}

		.wfte_addr_mleft{transform: rotate(270deg); position:absolute; margin-left:-80px; margin-top:80px;  }
		.wfte_addr_mleft_in{ width:150px; text-align:right; background:none;}

		.wfte_addr_mtop{position:absolute; margin-left:20px; margin-top:-10px;}
		.wfte_addr_mtop_in{ background:none;}

		.wfte_addr_mright{ width:100%; text-align:right; }
		.wfte_addr_mright_in{ position:absolute; width:150px; text-align:right; transform:rotate(90deg); margin-left:-70px; margin-top:-105px; background:none;}
		
		.wfte_addr_mbottom{ width:100%; text-align:right; }
		.wfte_addr_mbottom_in{position:absolute; width:150px; text-align:right; margin-left:-164px; margin-top:-4px; background:none;}
		</style>

		<div class="wfte_addr_mtop"><span class="wfte_unit_boxvl wfte_addr_mtop_in">'.$col_mt.' in</span></div>
		<div class="wfte_addr_mleft"><span class="wfte_unit_boxvl wfte_addr_mleft_in">'.$col_ml.' in</span></div>
		<table '.$div_attr.'>';
		for($i=0; $i<$row_count; $i++)
		{
			$out.='<tr>';
			for($j=0; $j<$col_count; $j++)
			{
				$out.='<td>
					<div class="wfte_addr_h"><span class="wfte_unit_boxvl wfte_addr_h_in">'.$col_h.' in</span></div>
					<div class="wfte_shipping_address_main">
						<div class="wfte_addr_w"><span class="wfte_unit_boxvl wfte_addr_w_in">'.$col_w.' in</span></div>
						<div class="wfte_shipping_address">
							[wfte_shipping_address]
						</div>
						<div class="wfte_addr_rowsp"><span class="wfte_unit_boxvl wfte_addr_rowsp_in">'.$col_rs.' in</span></div>
						<div class="wfte_addr_colsp"><span class="wfte_unit_boxvl wfte_addr_colsp_in">'.$col_cs.' in</span></div>
					</div>
				</td>';
			}
			$out.='</tr>';
		}
		$out.='</table>
		<div class="wfte_addr_mbottom"><span class="wfte_unit_boxvl wfte_addr_mbottom_in">'.$col_mb.' in</span></div>
		<div class="wfte_addr_mright"><span class="wfte_unit_boxvl wfte_addr_mright_in">'.$col_mr.' in</span></div>';
		return $out;
	}

	/**
	 *  Items needed to be converted to HTML for print
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);
			$wfte_addresslabel_extradata='';
			$find_replace['[wfte_addresslabel_extradata]']=apply_filters('wf_pklist_alter_addresslabel_extradata',$wfte_addresslabel_extradata, $order);			
		}
		return $find_replace;
	}

	/**
	 *  Dummy data for extra fields on template
	 *  In some documents there will be some custom fields
	 */
	private function extra_fields_dummy_data($find_replace,$html,$template_type)
	{
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
				'addresslabel_templates'=>__('Layouts','wf-woocommerce-packing-list'),
				'addresslabel_data'=>__('Properties','wf-woocommerce-packing-list'),
			);
		}
		return $settings;
	}

	/**
	 *  We can add items to right panel. Which are not/yes for customization. 
	 *  Customizable fields need an HTML dom in template HTML. But here not required
	 */
	public function get_non_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			return array(
				'addresslabel_templates'
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
				'addresslabel_templates',
				'addresslabel_data',
			);
		}
		return $settings;
	}

	public function default_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array();
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
			__('Address Label','wf-woocommerce-packing-list'),
			__('Address Label','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			$this->module_id,
			array($this,'admin_settings_page')
		);
		return $menus;
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_addresslabel']=__('Print Address Label','wf-woocommerce-packing-list');
		return $actions;
	}

	public function add_print_actions($item_arr, $order, $order_id, $button_location)
	{
		if($button_location=='detail_page')
		{
			$item_arr[]=array(
				'button_type'=>'dropdown',
				'button_key'=>'addresslabel_actions', //unique if multiple on same page
				'button_location'=>$button_location,
				'action'=>'',
				'label'=>__('Address Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print/Download Address Label','wf-woocommerce-packing-list'),
				'is_show_prompt'=>0, //always 0
				'items'=>array(
					array(  
						'action'=>'print_addresslabel',
						'label'=>__('Print','wf-woocommerce-packing-list'),
						'tooltip'=>__('Print Address Label','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,						
					),
					array(
						'action'=>'download_addresslabel',
						'label'=>__('Download','wf-woocommerce-packing-list'),
						'tooltip'=>__('Download Address Label','wf-woocommerce-packing-list'),
						'is_show_prompt'=>0,
						'button_location'=>$button_location,
					)
				),
			);
		}else
		{
			$item_arr[]=array(
				'action'=>'print_addresslabel',
				'label'=>__('Address Label','wf-woocommerce-packing-list'),
				'tooltip'=>__('Print Address Label','wf-woocommerce-packing-list'),
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
	public function add_frontend_print_buttons($html,$order,$order_id)
	{
		return $html;
	}
	public function admin_settings_page()
	{
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
			$this->customizer->enable_code_view=false;
			$this->customizer->open_first_panel=true;
			$this->customizer->init($this->module_base);
		}
		include(plugin_dir_path( __FILE__ ).'views/admin-settings.php');
	}
	
	
	/* 
	* Print_window for address label
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    {
    	if($action=='print_addresslabel' || $action=='download_addresslabel')
    	{   
    		$allowed = true;
    		$allowed=apply_filters('wf_address_label_privilege', $allowed, $order_ids);
    		if(!$allowed)
    		{
	            wp_die(__('You do not have sufficient permissions to access this page.','wf-woocommerce-packing-list'));
	        }

    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);
	        	$this->customizer->template_for_pdf=($action=='download_addresslabel' ? true : false);
	        	$html=$this->generate_order_template($order_ids,$pdf_name);
	        	if($action=='download_addresslabel')
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
    	$out='';
    	if($html!="")
    	{
    		$number_of_orders=count($orders);
			$order_inc=0;

			//process template data====
			$row_count=floor($this->get_template_html_attr_vl($html,'data-rows'));
			$col_count=floor($this->get_template_html_attr_vl($html,'data-columns'));
			$total_col=($row_count*$col_count);
			$col_w=$this->get_template_html_attr_vl($html,'data-width');
			$col_h=$this->get_template_html_attr_vl($html,'data-height');
			$col_mt=$this->get_template_html_attr_vl($html,'data-margin-top',0);
			$col_mr=$this->get_template_html_attr_vl($html,'data-margin-right',0);
			$col_mb=$this->get_template_html_attr_vl($html,'data-margin-bottom',0);
			$col_ml=$this->get_template_html_attr_vl($html,'data-margin-left',0);
			$col_cs=$this->get_template_html_attr_vl($html,'data-column-spacing',0);
			$col_rs=$this->get_template_html_attr_vl($html,'data-row-spacing',0);
			
			$keep_label_dimension=false;
			$keep_label_dimension=apply_filters('wf_pklist_label_keep_dimension',$keep_label_dimension,$template_type);
			$flex_wrap=$keep_label_dimension ? 'wrap' : 'nowrap';

			$out='';

			/* add custom CSS  */
			$this->customizer->custom_css.='.wfte_addr_page{  width:auto; height:auto; 
				padding-left:'.$col_ml.'in; padding-right:'.$col_mr.'in; padding-top:'.$col_mt.'in; padding-bottom:'.$col_mb.'in; }
			.wfte_zero_dv{ margin:0px; padding:0px; border:0px;}
			.wfte_addr_rowsp{ width:100%; height:0px; }
			.wfte_addr_colsp{ float:left; width:'.$col_cs.'in; height:'.$col_h.'in; }
			.wfte_addr_row{ align-items:start; display:flex; flex-direction:row; flex-wrap:'.$flex_wrap.'; justify-content:flex-start; align-items:stretch;}
			.wfte_addr_col{ float:left; width:'.$col_w.'in; min-height:'.$col_h.'in; border:solid 1px #000; 
			box-sizing:border-box; padding:10px; overflow:hidden; font-size:12px; margin-bottom:'.$col_rs.'in; }
			.wfte_addr_row:last-child .wfte_addr_col{ margin-bottom:0px; }
			.wfte_shipping_address{ display:inline-block; text-align:start; width:100%;}
			.div{ page-break-inside:avoid; }';

			$rowsp='<div class="wfte_addr_rowsp wfte_zero_dv"></div>';
			$colsp='<div class="wfte_addr_colsp wfte_zero_dv"></div>';
			$clearfix='<div class="clearfix"></div>';

			$page_count=ceil($number_of_orders/$total_col); //total pages to print

			for($p=0; $p<$page_count; $p++)
			{
				$out.='<div class="wfte_rtl_main wfte_addr_page">';
				for($i=0; $i<$row_count; $i++)
				{
					$out.='<div class="wfte_addr_row">';
					for($j=0; $j<$col_count; $j++)
					{
						if(isset($orders[$order_inc])) //order is not outof bound
						{
							$order_id=$orders[$order_inc];
							$order=( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
							$column_html='<div class="wfte_addr_col">
								<div class="wfte_shipping_address">
									[wfte_shipping_address]
									[wfte_addresslabel_extradata]
								</div>
							</div>';
							$out.=$this->customizer->generate_template_html($column_html,$template_type,$order);
							if(($j+1)<$col_count) //loop is not in final round, so add column spacing
							{
								$out.=$colsp;
							}
						}else
						{
							//orders finished. add a loop break here
							$out.='</div>'; //closing for row
							$out.='</div>'; //closing for page
							break 3;
						}
						$order_inc++; //must be below
					}
					$out.='</div>';
					if(($i+1)<$row_count) //loop is not in final round, so add row spacing
					{
						$out.=$clearfix.$rowsp.$clearfix;
					}
				}
				$out.='</div>';
				//more than one page and loop is not in final round
				if($page_count>1 && ($p+1)<$page_count)
				{
					$out.='<p class="pagebreak"></p>'.$clearfix;
				}
			}
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }
}
new Wf_Woocommerce_Packing_List_Addresslabel();