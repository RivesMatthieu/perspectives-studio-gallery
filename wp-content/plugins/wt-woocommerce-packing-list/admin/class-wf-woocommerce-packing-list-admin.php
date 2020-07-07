<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      4.0.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/admin
 * @author     WebToffee <info@webtoffee.com>
 */
class Wf_Woocommerce_Packing_List_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/*
	 * module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 */
	public static $modules=array(
		'customizer',
		'sequential-number',
	);

	public static $existing_modules=array();

	public $bulk_actions=array();

	public static $tooltip_arr=array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wf-woocommerce-packing-list-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wf-woocommerce-packing-list-admin.js', array( 'jquery','wp-color-picker','jquery-tiptip'), $this->version, false );
		//order list page bulk action filter
		$this->bulk_actions=apply_filters('wt_print_bulk_actions',$this->bulk_actions);

		$params=array(
			'nonces' => array(
		            'wf_packlist' => wp_create_nonce(WF_PKLIST_PLUGIN_NAME),
		     ),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'no_image'=>Wf_Woocommerce_Packing_List::$no_image,
			'bulk_actions'=>array_keys($this->bulk_actions),
			'print_action_url'=>admin_url('?print_packinglist=true'),
			'msgs'=>array(
				'settings_success'=>__('Settings updated.','wf-woocommerce-packing-list'),
				'all_fields_mandatory'=>__('All fields are mandatory','wf-woocommerce-packing-list'),
				'enter_mandatory_fields'=>__('Please enter mandatory fields','wf-woocommerce-packing-list'),
				'settings_error'=>__('Unable to update Settings.','wf-woocommerce-packing-list'),
				'select_orders_first'=>__('You have to select order(s) first!','wf-woocommerce-packing-list'),
				'invoice_not_gen_bulk'=>__('One or more order do not have invoice generated. Generate manually?','wf-woocommerce-packing-list'),
				'error'=>__('Sorry, something went wrong.','wf-woocommerce-packing-list'),
				'please_wait'=>__('Please wait','wf-woocommerce-packing-list'),
				'sure'=>__("You can't undo this action. Are you sure?",'wf-woocommerce-packing-list'),
			)
		);
		wp_localize_script($this->plugin_name, 'wf_pklist_params', $params);

	}

	/**
	 * Function to add Items to Orders Bulk action dropdown
	 *
	 * @since    4.0.0
	 */
	public function alter_bulk_action($actions)
	{
        return array_merge($actions,$this->bulk_actions);
	}
	

	/**
	 * Function to add custom fields in checkout page
	 *
	 * @since    4.0.0
	 * @since    4.0.3 is_required and placeholder options added
	 */
	public function add_checkout_fields($fields) 
	{
		//user selected fields to show
		$user_selected_data_flds=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields');
        if(is_array($user_selected_data_flds) && count(array_filter($user_selected_data_flds))>0)
        {
            $default_data_flds=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
            $user_created_data_flds=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
            
            /* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
            $user_created_data_flds=Wf_Woocommerce_Packing_List::process_checkout_fields($user_created_data_flds);
		    $data_flds=array_merge($default_data_flds,$user_created_data_flds);

		    $priority_inc=110; //110 is the last item(billing email priority so our fields will be after that.)
		    $additional_checkout_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
            foreach($user_selected_data_flds as $value)
            {
            	$priority_inc++;
                if(isset($data_flds[$value])) //field exists in the user selected,default field list
                {
	                $add_data=isset($additional_checkout_field_options[$value]) ? $additional_checkout_field_options[$value] : array();
	                $is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);
	                $placeholder=(isset($add_data['placeholder']) ? $add_data['placeholder'] : 'Enter '.$data_flds[$value]);
	                $title=(isset($add_data['title']) && trim($add_data['title'])!="" ? $add_data['title'] : $data_flds[$value]);

	                $fields['billing']['billing_' . $value] = array(
	                    'type' => 'text',
	                    'label' => __($title,'woocommerce'),
	                    'placeholder' => _x($placeholder, 'placeholder','woocommerce'),
	                    'required' =>$is_required,
	                    'class' => array('form-row-wide', 'align-left'),
	                    'clear' => true,
	                    'priority'=>$priority_inc,
	                );
            	}
            }
        }
		return $fields;
	}

	/**
	 * Function to add print button in order list page action column
	 *
	 * @since    4.0.0
	 */
	public function add_print_action_button($actions,$order)
	{
        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
        $wf_pklist_print_options=array(
            array(
                'name' => '',
                'action' => 'wf_pklist_print_document',
                'url' => sprintf('#%s', $order_id)
            ),
        );
        return array_merge($actions, $wf_pklist_print_options);
    } 

    /**
	 * Function to add email attachments to order email
	 *
	 * @since    4.0.0
	 * @since    4.0.1 added compatibility admin created orders `is_a` checking added
	 */
	public function add_email_attachments($attachments, $status=null, $order=null)
	{
		if(is_object($order) && is_a($order,'WC_Order') && isset($status))
		{
            $order=( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
			$order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
			$attachments=apply_filters('wt_email_attachments', $attachments, $order, $order_id, $status);
        }
		return $attachments;
	}
   
    /**
	 * Function to add action buttons in order email
	 *
	 *  @since    4.0.0
	 *	@since 	  4.0.8 	[Bug fix] Print button missing in email 
	 */
	public function add_email_print_actions($order, $sent_to_admin, $plain_text, $email )
	{
		if(is_object($order) && is_a($order, 'WC_Order'))
		{
			$order=( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
			$order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
			$html='';
			$html=apply_filters('wt_email_print_actions', $html, $order, $order_id, $email );	
		}
	}

    /**
	 * Function to add action buttons in user dashboard order list page
	 *
	 * @since    4.0.0
	 */
	public function add_fontend_print_actions($order)
	{
		$order=( WC()->version < '2.7.0' ) ? new WC_Order($order) : new wf_order($order);
		$order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
		$html='';
		$html=apply_filters('wt_frontend_print_actions', $html, $order, $order_id);	
	}

	public static function get_print_url($order_id, $action)
	{
		$url=wp_nonce_url(admin_url('?print_packinglist=true&post='.($order_id).'&type='.$action), WF_PKLIST_PLUGIN_NAME);
		$url=(isset($_GET['debug']) ? $url.'&debug' : $url);
		return $url;
	}

	public static function generate_print_button_html($btn_arr, $order, $order_id, $button_location)
	{
		/* filter for customers to alter buttons */
		$btn_arr=apply_filters('wt_pklist_alter_print_actions',$btn_arr, $order, $order_id, $button_location);

		foreach($btn_arr as $btn_key=>$args)
		{
			$action=$args['action'];
			$label=$args['label'];
			$is_show_prompt=$args['is_show_prompt'];
			$tooltip=(isset($args['tooltip']) ? $args['tooltip'] : $label);
			$button_location=(isset($args['button_location']) ? $args['button_location'] : 'detail_page');

			$url=self::get_print_url($order_id, $action);

			$href_attr='';
			$onclick='';
			$confirmation_clss='';
			if($is_show_prompt!==0) //$is_show_prompt variable is a string then it will set as warning msg title
			{
				$confirmation_clss='wf_pklist_confirm_'.$action;
				$onclick='onclick=" return wf_Confirm_Notice_for_Manually_Creating_Invoicenumbers(\''.$url.'\',\''.$is_show_prompt.'\');"';
			}else
			{
				$href_attr=' href="'.$url.'"';
			}
			if($button_location == "detail_page")
	        {
	        	$button_type=(isset($args['button_type']) ? $args['button_type'] : 'normal');
	        	$button_key=(isset($args['button_key']) ? $args['button_key'] : 'button_key_'.$btn_key);
	        ?>
				<tr>
					<td>
						<?php
						if($button_type=='dropdown')
						{
						?>
							<div class="wt_pklist_dropdown">
							  <button class="button wt_pklist_drp_menu" type="button"><?php echo $label;?></button>
							  <div class="wt_pklist_dropdown_content">
							    <?php
								foreach($args['items'] as $btnkk => $btnvv)
								{
									$action=$btnvv['action'];
									$label=$btnvv['label'];
									$tooltip=(isset($btnvv['tooltip']) ? $btnvv['tooltip'] : $label);
									$is_show_prompt=$btnvv['is_show_prompt'];
									
									$url=self::get_print_url($order_id, $action);
									
									$href_attr='';
									$onclick='';
									$confirmation_clss='';
									if($is_show_prompt!==0) //$is_show_prompt variable is a string then it will set as warning msg title
									{
										$confirmation_clss='wf_pklist_confirm_'.$action;
										$onclick='onclick=" return wf_Confirm_Notice_for_Manually_Creating_Invoicenumbers(\''.$url.'\',\''.$is_show_prompt.'\');"';
									}else
									{
										$href_attr=' href="'.$url.'"';
									}
									?>
									<a <?php echo $onclick;?> <?php echo $href_attr;?> target="_blank"> <?php echo $btnvv['label'];?></a>
									<?php
								}
								?>
							  </div>
							</div>
						<?php
						}else
						{
						?>
						<a class="button tips wf-packing-list-link" <?php echo $onclick;?> <?php echo $href_attr;?> target="_blank" data-tip="<?php echo esc_attr($tooltip);?>" >
							<?php echo $label;?>
						</a>
						<?php
						}
						?>
					</td>
				</tr>
			<?php
	        }elseif($button_location=="list_page")
	        {
	        ?>
				<li>
					<a class="<?php echo $confirmation_clss;?> tips" data-id="<?php echo $order_id;?>" <?php echo $onclick;?> <?php echo $href_attr;?> target="_blank" data-tip="<?php echo esc_attr($tooltip);?>"><?php echo $label;?></a>
				</li>
			<?php
	        }
	    }
	}

	/**
	 * Function to add action buttons in order list page
	 *
	 * @since    4.0.0
	 */
	public function add_print_actions($column)
	{
		global $post, $woocommerce, $the_order;
		if($column=='order_actions' || $column=='wc_actions')
		{
			$order = ( WC()->version < '2.7.0' ) ? new WC_Order($post->ID) : new wf_order($post->ID);
            $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
			$html='';
			?>
			<div id="wf_pklist_print_document-<?php echo $order_id;?>" class="wf-pklist-print-tooltip-order-actions">				
				<div class="wf-pklist-print-tooltip-content">
                    <ul>
                    <?php
					$btn_arr=array();
					$btn_arr=apply_filters('wt_print_actions', $btn_arr, $order, $order_id, 'list_page');
					self::generate_print_button_html($btn_arr, $order, $order_id, 'list_page'); //generate buttons
					?>
					</ul>
                </div>
                <div class="wf_arrow"></div>	
			</div>
			<?php
		}
		return $column;
	}

	/**
	 * Registers meta box and printing options
	 *
	 * @since    4.0.0
	 */
	public function add_meta_boxes()
	{
		add_meta_box('woocommerce-packinglist-box', __('Invoice/Packing','wf-woocommerce-packing-list'), array($this,'create_metabox_content'),'shop_order', 'side', 'default');
	}

	/**
	 * Add plugin action links
	 *
	 * @param array $links links array
	 */
	public function plugin_action_links($links) 
	{
	   $links[] = '<a href="'.admin_url('admin.php?page='.WF_PKLIST_POST_TYPE).'">'.__('Settings','wf-woocommerce-packing-list').'</a>';
	   $links[] = '<a href="https://www.webtoffee.com/category/documentation/print-invoices-packing-list-labels-for-woocommerce/" target="_blank">'.__('Documentation','wf-woocommerce-packing-list').'</a>';
	   $links[] = '<a href="https://www.webtoffee.com/support/" target="_blank">'.__('Support','wf-woocommerce-packing-list').'</a>';
	   return $links;
	}


	/**
	 *	@since  4.0.0  create content for metabox
	 *	@since  4.0.4  added separate section for document details and print actions
	 * 
	 */
	public function create_metabox_content()
	{
		global $post;
        $order = ( WC()->version < '2.7.0' ) ? new WC_Order($post->ID) : new wf_order($post->ID);
        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
		?>
		<table class="wf_invoice_metabox" style="width:100%;">			
			<?php
			$data_arr=array();
			$data_arr=apply_filters('wt_print_docdata_metabox',$data_arr, $order, $order_id);
			if(count($data_arr)>0)
			{
			?>
			<tr>
				<td style="font-weight:bold;">
					<h4 style="margin:0px; padding-top:5px; padding-bottom:3px; border-bottom:dashed 1px #ccc;"><?php _e('Document details','wf-woocommerce-packing-list'); ?></h4>
				</td>
			</tr>
			<tr>
				<td style="padding-bottom:10px;">
					<?php
					
					foreach($data_arr as $datav)
					{
						echo '<span style="font-weight:500;">';
						echo ($datav['label']!="" ? $datav['label'].': ' : '');
						echo '</span>';
						echo $datav['value'].'<br />';
					}
					?>
				</td>
			</tr>
			<?php
			}
			?>
			<tr>
				<td>
					<h4 style="margin:0px; padding-top:5px; padding-bottom:3px; border-bottom:dashed 1px #ccc;"><?php _e('Print/Download','wf-woocommerce-packing-list'); ?></h4>
				</td>
			</tr>
			<tr>
				<td style="height:3px; font-size:0px; line-height:0px;"></td>
			</tr>
			<?php
			$btn_arr=array();
			$btn_arr=apply_filters('wt_print_actions', $btn_arr, $order, $order_id, 'detail_page');
			self::generate_print_button_html($btn_arr, $order, $order_id, 'detail_page'); //generate buttons
			?>
		</table>
		<?php
	}


	/**
	 * Registers menu options
	 * Hooked into admin_menu
	 *
	 * @since    1.0.0
	 */
	public function admin_menu()
	{
		$menus=array(
			array(
			'menu',
			__('General Settings','wf-woocommerce-packing-list'),
			__('Invoice/Packing','wf-woocommerce-packing-list'),
			'manage_woocommerce',
			WF_PKLIST_POST_TYPE,
			array($this,'admin_settings_page'),
			'dashicons-media-text',
			56
			)
		);
		$menus=apply_filters('wt_admin_menu',$menus);
		if(count($menus)>0)
		{
			add_submenu_page(WF_PKLIST_POST_TYPE,__('General Settings','wf-woocommerce-packing-list'),__('General Settings','wf-woocommerce-packing-list'), "manage_woocommerce",WF_PKLIST_POST_TYPE,array($this,'admin_settings_page'));
			foreach($menus as $menu)
			{
				if($menu[0]=='submenu')
				{
					add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6]);
				}else
				{
					add_menu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6],$menu[7]);	
				}
			}
		}

		if(function_exists('remove_submenu_page')){
			//remove_submenu_page(WF_PKLIST_POST_TYPE,WF_PKLIST_POST_TYPE);
		}
	}

	/**
	* @since 4.0.5 
	* Is user allowed 
	*/
	public static function check_write_access($nonce_id='')
	{
		$er=true;
		//checkes user is logged in
    	if(!is_user_logged_in())
    	{
    		$er=false;
    	}

    	if($er===true) //no error then proceed
    	{
    		$nonce=sanitize_text_field($_REQUEST['_wpnonce']);
    		$nonce=(is_array($nonce) ? $nonce[0] : $nonce);
    		$nonce_id=($nonce_id=="" ? WF_PKLIST_PLUGIN_NAME : $nonce_id);
    		if(!(wp_verify_nonce($nonce, $nonce_id)))
	        {
	            $er=false;
	        }else
	        {
	        	if(!Wf_Woocommerce_Packing_List_Admin::check_role_access()) //Check access
	            {
	            	$er=false;
	            }
	        }
    	}
    	return $er;
	}

	/**
	* @since 4.0.5 
	* Is user allowed 
	*/
	public static function check_role_access()
	{
		$admin_print_role_access=array('manage_options', 'manage_woocommerce');
    	$admin_print_role_access=apply_filters('wf_pklist_alter_admin_print_role_access', $admin_print_role_access);  
    	$admin_print_role_access=(!is_array($admin_print_role_access) ? array() : $admin_print_role_access);
    	$is_allowed=false;
    	foreach($admin_print_role_access as $role) //checking access
    	{
    		if(current_user_can($role)) //any of the role is okay then allow to print
    		{
    			$is_allowed=true;
    			break;
    		}
    	}
    	return $is_allowed;
	}

	/**
	 * function to render printing window
	 *
	 */
    public function print_window() 
    {       
        $attachments = array();
        if(isset($_GET['print_packinglist'])) 
        {
        	//checkes user is logged in
        	if(!is_user_logged_in())
        	{
        		auth_redirect();
        	}
        	$not_allowed_msg=__('You are not allowed to view this page.','wf-woocommerce-packing-list');
        	$not_allowed_title=__('Access denied !!!.','wf-woocommerce-packing-list');

            $client = false;
            //	to check current user has rights to get invoice and packing list
            if(!isset($_GET['attaching_pdf']))
            {
	            $nonce=isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : ''; 
	            if(!(wp_verify_nonce($nonce,WF_PKLIST_PLUGIN_NAME)))
	            {
	                wp_die($not_allowed_msg,$not_allowed_title);
	            }else
	            {
	            	if(!self::check_role_access()) //Check access
	                {
	                	wp_die($not_allowed_msg,$not_allowed_title);
	                }
	            	$orders = explode(',', $_GET['post']);
	            }
        	}else 
        	{
        		// to get the orders number
	            if(isset($_GET['email']) && isset($_GET['post']) && isset($_GET['user_print']))
	            {
	                $email_data_get =Wf_Woocommerce_Packing_List::wf_decode($_GET['email']);
	                $order_data_get =Wf_Woocommerce_Packing_List::wf_decode($_GET['post']);
	                $order_data = wc_get_order($order_data_get);
	                if(!$order_data)
	                {
	                	wp_die($not_allowed_msg,$not_allowed_title);
	                }
	                $logged_in_userid=get_current_user_id();
	                $order_user_id=((WC()->version < '2.7.0') ? $order_data->user_id : $order_data->get_user_id());
	                if($logged_in_userid!=$order_user_id) //the current order not belongs to the current logged in user
	                { 
	  	             	if(!self::check_role_access()) //Check access
	                	{
	                		wp_die($not_allowed_msg,$not_allowed_title);
	                	}
	                }

	                //checks the email parameters belongs to the given order
	                if($email_data_get === ((WC()->version < '2.7.0') ? $order_data->billing_email : $order_data->get_billing_email())) 
	                {
	                    $orders=explode(",",$order_data_get); //must be an array
	                }else
	                {
	                    wp_die($not_allowed_msg,$not_allowed_title);
	                }
	            }else
	            {
	            	wp_die($not_allowed_msg,$not_allowed_title);
	            }
        	} 

            $orders=array_values(array_filter($orders));
            $orders=$this->verify_order_ids($orders);
            if(count($orders)>0)
            {
	            remove_action('wp_footer', 'wp_admin_bar_render', 1000);
	            $action = (isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '');
	            //action for modules to hook print function
	            do_action('wt_print_doc',$orders,$action);
	        }
            exit();
        }
    }

    /**
	* Check for valid order ids
	* @since 4.0.2
	* @since 4.0.3 Added compatiblity for `Sequential Order Numbers for WooCommerce`
	*/
    public static function verify_order_ids($order_ids)
    {
    	$out=array();
    	foreach ($order_ids as $order_id)
    	{
    		if(wc_get_order($order_id)===false)
    		{
    			/* compatibility for sequential order number */
    			$order_data=wc_get_orders(
    				array(
    					'limit' => 1,
    					'return' => 'ids',
    					'meta_query'=>array(
    						'key'=>'_order_number',
    						'value'=>$order_id,
    					)
    			));
    			if($order_data!=false && is_array($order_data) && count($order_data)==1)
    			{
    				$order_id=(int) $order_data[0];
    				if($order_id>0 && wc_get_order($order_id)!=false)
    				{
    					$out[]=$order_id;
    				}
    			}
    		}else
    		{
    			$out[]=$order_id;
    		}
    	}
    	return $out;
    }

    /**
	* Ajax hook to load address from woo
	* @since 4.0.2
	*/
    public function load_address_from_woo()
    {
    	if(!self::check_write_access()) 
		{
			exit();
		}
    	$out=array(
    		'status'=>1,
    		'address_line1'=>get_option('woocommerce_store_address'),
    		'address_line2'=>get_option('woocommerce_store_address_2'),
    		'city'=>get_option('woocommerce_store_city'),
    		'country'=>get_option('woocommerce_default_country'),
    		'postalcode'=>get_option('woocommerce_store_postcode'),
    	);
    	echo json_encode($out);
    	exit();
    }

    /**
	* Ajax function to list additional checkout fields
	* @since 4.0.3
	* @since 4.0.5 Role checking and nonce checking added
	*/
    public function checkout_field_list_view()
    {
    	if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
    		exit();
    	}
    	$user_selected_add_fields = array();	                
        $add_checkout_data_flds=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
        $user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');

        /* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
        $user_created=Wf_Woocommerce_Packing_List::process_checkout_fields($user_created);

        /* user selected fields */
        $vl=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields'); 
        $user_selected_arr=$vl && is_array($vl) ? $vl : array();
        $additional_checkout_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
        
        //delete action
        if(isset($_POST['wf_delete_custom_field'])) 
	    {
	    	$data_key=sanitize_text_field($_POST['wf_delete_custom_field']);
	    	unset($user_created[$data_key]); //remove from field list
	    	Wf_Woocommerce_Packing_List::update_option('wf_additional_checkout_data_fields',$user_created);

	    	unset($additional_checkout_field_options[$data_key]); //remove from field additional options
	    	Wf_Woocommerce_Packing_List::update_option('wt_additional_checkout_field_options',$additional_checkout_field_options);
	    	
	    	//remove from user selected array
	    	if(($delete_key=array_search($data_key,$user_selected_arr))!==false)
	    	{
			    unset($user_selected_arr[$delete_key]);
			    Wf_Woocommerce_Packing_List::update_option('wf_invoice_additional_checkout_data_fields',$user_selected_arr);
			}
	    }

	    $fields=array_merge($add_checkout_data_flds,$user_created);
    	foreach($fields as $key=>$field)
		{
			$add_data=isset($additional_checkout_field_options[$key]) ? $additional_checkout_field_options[$key] : array();
			$is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);
			$placeholder=(isset($add_data['placeholder']) ? $add_data['placeholder'] : '');

			/* we are giving option to edit title of builtin items */
			$field=(isset($add_data['title']) && trim($add_data['title'])!="" ? $add_data['title'] : $field);

			$is_required_display=($is_required>0 ? ' <span style="color:red;">*</span>' : '');
			$placeholder_display='<br /><i style="color:#666;">'.($placeholder!="" ? $placeholder : '&nbsp;').'</i>';
			
			$is_builtin=(isset($add_checkout_data_flds[$key]) ? 1 : 0);
			$delete_btn='<span title="'.__('Delete').'" class="dashicons dashicons-trash wt_pklist_checkout_field_delete '.($is_builtin==1 ? 'disabled_btn' : '').'"></span>';
			$edit_btn='<span title="'.__('Edit').'" class="dashicons dashicons-edit wt_pklist_checkout_field_edit"></span>';
			
			//$delete_btn=($is_builtin==1 ? '' : $delete_btn); 
			$is_selected=(in_array($key,$user_selected_arr) ? '<span class="dashicons dashicons-yes-alt" style="color:green; float:right;"></span>' : '');
			$is_selected='';
			?>
			<div class="wt_pklist_checkout_field_item" data-key="<?php echo $key;?>" data-builtin="<?php echo $is_builtin;?>"><?php echo $edit_btn.$delete_btn.$is_selected.$field.' ('.$key.') '.$is_required_display.$placeholder_display;?>
				<div class="wt_pklist_checkout_item_title" style="display:none;"><?php echo $field;?></div>				
				<div class="wt_pklist_checkout_item_placeholder" style="display:none;"><?php echo $placeholder;?></div>				
				<div class="wt_pklist_checkout_item_is_required" style="display:none;"><?php echo $is_required;?></div>				
			</div>
			<?php
		}
		exit();
    }

	/**
	* Get all templates.
	* @since 4.0.2
	*/
	private function get_all_templates()
	{
		global $wpdb;
		$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
		$qry="SELECT * FROM $table_name";
		return $wpdb->get_results($qry,ARRAY_A);
	}

	/**
	* Form action for debug settings tab
	* @since 4.0.2
	*/
	public function debug_save()
	{	
		if(isset($_POST['wt_pklist_export_settings_btn']))
		{
			if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
	    	{
	    		return;
	    	}

	        //module state
	        $module_status=get_option('wt_pklist_common_modules');
	        $settings=array();

	        //enabling all modules otherwise settings export will not work properly
            $module_list=array(
                'invoice'=>1,
                'packinglist'=>1,
                'shippinglabel'=>1,
                'deliverynote'=>1,
                'dispathlabel'=>1,
                'addresslabel'=>1,
                'creditnote'=>1,
                'picklist'=>1,
                'proformainvoice'=>1,
            );
            update_option('wt_pklist_common_modules',$module_list);
            //=======================================

            foreach ($module_list as $key => $value)
            {
            	$module_id=Wf_Woocommerce_Packing_List::get_module_id($key);
            	$settings[$key]=Wf_Woocommerce_Packing_List::get_settings($module_id);
            }
            //general settings
            $settings['main']=Wf_Woocommerce_Packing_List::get_settings();

            //restoring module state
            update_option('wt_pklist_common_modules', $module_status);

            $out=array(
            	'plugin_version'=>WF_PKLIST_VERSION,
            	'settings'=>$settings,
            	'module_status'=>$module_status,
            	'template_data'=>$this->get_all_templates(),
            );

			header('Content-Type: application/json');
			header('Content-disposition: attachment; filename="wt_pklist_settings.json"');
			echo json_encode($out);
			exit();
		}

		
		if(isset($_POST['wt_pklist_import_settings_btn']))
		{
			if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
	    	{
	    		return;
	    	}

			if(!empty($_FILES['wt_pklist_import_settings_json']['tmp_name'])) 
			{
				$filename=$_FILES['wt_pklist_import_settings_json']['tmp_name'];
				$json_file=@fopen($filename,'r');
				$json_data=fread($json_file,filesize($filename));
				$json_data_arr=json_decode($json_data,true);

				//module state
	        	$module_status=get_option('wt_pklist_common_modules');
				//enabling all modules otherwise settings import will not work properly
	            $module_list=array(
	                'invoice'=>1,
	                'packinglist'=>1,
	                'shippinglabel'=>1,
	                'deliverynote'=>1,
	                'dispathlabel'=>1,
	                'addresslabel'=>1,
	                'creditnote'=>1,
                	'picklist'=>1,
                	'proformainvoice'=>1,
	            );
	            update_option('wt_pklist_common_modules', $module_list);
	            if(isset($json_data_arr['settings']))
	            {
	            	$settings=$json_data_arr['settings'];
	            	foreach ($module_list as $key => $value)
		            {
		            	if(isset($settings[$key]))
		            	{
		            		$module_id=Wf_Woocommerce_Packing_List::get_module_id($key);
		            		Wf_Woocommerce_Packing_List::update_settings($settings[$key],$module_id);
		            	}
		            }
		            //general settings
		            if(isset($settings['main']))
			        {
		            	Wf_Woocommerce_Packing_List::update_settings($settings['main']);
		            }
	            }
            
	            //module status
	            if(isset($json_data_arr['module_status']))
	            {
	            	update_option('wt_pklist_common_modules',$json_data_arr['module_status']);
	            }else
	            {
	            	//restoring module state
	            	update_option('wt_pklist_common_modules',$module_status);
	            }            

	            //template data
				if(isset($json_data_arr['template_data']))
	            {
	            	if(is_array($json_data_arr['template_data']))
	            	{
	            		global $wpdb;
	            		$db_vl=array();
	            		foreach($json_data_arr['template_data'] as $td)
	            		{
	            			$db_vl[]=$wpdb->prepare("(%s,%s,%d,%d,%s,%d,%d)",
	            				$td['template_name'],
	            				$td['template_html'],
	            				$td['template_from'],
	            				$td['is_active'],
	            				$td['template_type'],
	            				$td['created_at'],
	            				$td['updated_at']);
	            		}
	            		if(count($db_vl)>0)
	            		{
	            			$table_name=$wpdb->prefix.Wf_Woocommerce_Packing_List::$template_data_tb;
	            			$wpdb->query("TRUNCATE `$table_name`"); //removing existing data
	            			$query="INSERT INTO `$table_name` (template_name,template_html,template_from,is_active,template_type,created_at,updated_at) VALUES ".implode(",",$db_vl);
							$wpdb->query($query);
	            		}
	            	}
	            }
			}
		}

		if(isset($_POST['wt_pklist_admin_modules_btn']))
		{
		    if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
	    	{
	    		return;
	    	}
	        
		    $wt_pklist_common_modules=get_option('wt_pklist_common_modules');
		    if($wt_pklist_common_modules===false)
		    {
		        $wt_pklist_common_modules=array();
		    }
		    if(isset($_POST['wt_pklist_common_modules']))
		    {
		        $wt_pklist_post=self::sanitize_text_arr($_POST['wt_pklist_common_modules']);
		        foreach($wt_pklist_common_modules as $k=>$v)
		        {
		            if(isset($wt_pklist_post[$k]) && $wt_pklist_post[$k]==1)
		            {
		                $wt_pklist_common_modules[$k]=1;
		            }else
		            {
		                $wt_pklist_common_modules[$k]=0;
		            }
		        }
		    }else
		    {
		    	foreach($wt_pklist_common_modules as $k=>$v)
		        {
					$wt_pklist_common_modules[$k]=0;
		        }
		    }

		    $wt_pklist_admin_modules=get_option('wt_pklist_admin_modules');
		    if($wt_pklist_admin_modules===false)
		    {
		        $wt_pklist_admin_modules=array();
		    }
		    if(isset($_POST['wt_pklist_admin_modules']))
		    {
		        $wt_pklist_post=self::sanitize_text_arr($_POST['wt_pklist_admin_modules']);
		        foreach($wt_pklist_admin_modules as $k=>$v)
		        {
		            if(isset($wt_pklist_post[$k]) && $wt_pklist_post[$k]==1)
		            {
		                $wt_pklist_admin_modules[$k]=1;
		            }else
		            {
		                $wt_pklist_admin_modules[$k]=0;
		            }
		        }
		    }else
		    {
		    	foreach($wt_pklist_admin_modules as $k=>$v)
		        {
					$wt_pklist_admin_modules[$k]=0;
		        }
		    }
		    update_option('wt_pklist_admin_modules',$wt_pklist_admin_modules);
		    update_option('wt_pklist_common_modules',$wt_pklist_common_modules);
		    wp_redirect($_SERVER['REQUEST_URI']); exit();
		}

		if(Wf_Woocommerce_Packing_List_Admin::check_role_access()) //Check access
	    {
			//module debug settings saving hook
	    	do_action('wt_pklist_module_save_debug_settings');
	    }
	}

	public static function sanitize_text_arr($arr, $type='text')
	{
		if(is_array($arr))
		{
			$out=array();
			foreach($arr as $k=>$arrv)
			{
				if(is_array($arrv))
				{
					$out[$k]=self::sanitize_text_arr($arrv, $type);
				}else
				{
					if($type=='int')
					{
						$out[$k]=intval($arrv);
					}else
					{
						$out[$k]=sanitize_text_field($arrv);
					}
				}
			}
			return $out;
		}else
		{
			if($type=='int')
			{
				return intval($arr);
			}else
			{
				return sanitize_text_field($arr);
			}
		}
	}

	/**
	 * Admin settings page
	 *
	 * @since    4.0.0
	 */
	public function admin_settings_page()
	{
		$the_options=Wf_Woocommerce_Packing_List::get_settings();
		$no_image=Wf_Woocommerce_Packing_List::$no_image;
		$order_statuses = wc_get_order_statuses();
		$wf_generate_invoice_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus');
		wp_enqueue_media();
		wp_enqueue_script('wc-enhanced-select');

		wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css');

		if(!Wf_Woocommerce_Packing_List_Admin::check_role_access()) 
    	{
    		wp_die(__('You are not allowed to view this page.','wf-woocommerce-packing-list'));
		}

		/* enable/disable modules */
		if(isset($_POST['wf_update_module_status']))
		{
			// Check nonce:
	        if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    		{
    			exit();
    		}

		    $wt_pklist_common_modules=get_option('wt_pklist_common_modules');
		    if($wt_pklist_common_modules===false)
		    {
		        $wt_pklist_common_modules=array();
		    }
		    if(isset($_POST['wt_pklist_common_modules']))
		    {
		        $wt_pklist_post=self::sanitize_text_arr($_POST['wt_pklist_common_modules']);
		        foreach($wt_pklist_common_modules as $k=>$v)
		        {
		            if(isset($wt_pklist_post[$k]) && $wt_pklist_post[$k]==1)
		            {
		                $wt_pklist_common_modules[$k]=1;
		            }else
		            {
		                $wt_pklist_common_modules[$k]=0;
		            }
		        }
		    }else
		    {
		    	foreach($wt_pklist_common_modules as $k=>$v)
		        {
					$wt_pklist_common_modules[$k]=0;
		        }
		    }
		    update_option('wt_pklist_common_modules',$wt_pklist_common_modules);
		    wp_redirect($_SERVER['REQUEST_URI']); exit();
		}

		include WF_PKLIST_PLUGIN_PATH.'/admin/partials/wf-woocommerce-packing-list-admin-display.php';
	}

	public function validate_box_packing_field($value)
	{           
        $new_boxes = array();
        foreach ($value as $key => $value) {
            if ($value['length'] != '') {
                $value['enabled'] = isset($value['enabled']) ? true : false;
                $new_boxes[] = $value;
            }
        }
        return $new_boxes;
    }

    /**
    * 	@since 4.0.4
    * 	Set tooltip for form fields 
    */
    public static function set_tooltip($key,$base_id="",$custom_css="")
    {
    	$tooltip_text=self::get_tooltips($key,$base_id);
    	if($tooltip_text!="")
    	{
    		$tooltip_text='<span style="color:#16a7c5; '.($custom_css!="" ? $custom_css : 'margin-top:-1px; margin-left:2px; position:absolute;').'" class="dashicons dashicons-editor-help wt-tips" data-wt-tip="'.$tooltip_text.'"></span>';
    	}
    	return $tooltip_text;
    }

    /**
    * 	@since 4.0.4
    * 	Get tooltip config data for non form field items
    * 	@return array 'class': class name to enable tooltip, 'text': tooltip text including data attribute if not empty
    */
    public static function get_tooltip_configs($key,$base_id="")
    {
    	$out=array('class'=>'','text'=>'');
    	$text=self::get_tooltips($key,$base_id);
    	if($text!="")
    	{
    		$out['text']=' data-wt-tip="'.$text.'"';
    		$out['class']=' wt-tips';
    	}  	
    	return $out;
    }

    /**
    *	@since 4.0.4
	* 	This function will take tooltip data from modules and store ot 
	*
	*/
	public function register_tooltips()
	{
		include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
		self::$tooltip_arr=array(
			'main'=>$arr
		);
		/* hook for modules to register tooltip */
		self::$tooltip_arr=apply_filters('wt_pklist_alter_tooltip_data',self::$tooltip_arr);
	}

	/**
	* 	Get tooltips
	*	@since 4.0.4
	*	@param string $key array key for tooltip item
	*	@param string $base module base id
	* 	@return tooltip content, empty string if not found
	*/
	public static function get_tooltips($key,$base_id='')
	{
		$arr=($base_id!="" && isset(self::$tooltip_arr[$base_id]) ? self::$tooltip_arr[$base_id] : self::$tooltip_arr['main']);
		return (isset($arr[$key]) ? $arr[$key] : '');
	}

    /**
    * 	@since 4.0.0 create form fields
    * 	@since 4.0.4 Added tooltip function
    */
	public static function generate_form_field($args, $base='')
	{		
		if(is_array($args))
		{
			foreach ($args as $key => $value)
			{
				$tr_id=(isset($value['tr_id']) ? ' id="'.$value['tr_id'].'" ' : '');
				$tr_class=(isset($value['tr_class']) ? $value['tr_class'] : '');

				$type=(isset($value['type']) ? $value['type'] : 'text');
				$field_group_attr=(isset($value['field_group']) ? ' data-field-group="'.$value['field_group'].'" ' : '');
				$tr_class.=(isset($value['field_group']) ? ' wt_pklist_field_group_children ' : ''); //add an extra class to tr when field grouping enabled


				if($type=='field_group_head') //heading for field group
				{
					$visibility=(isset($value['show_on_default']) ? $value['show_on_default'] : 0);
				?>
					<tr <?php echo $tr_id.$field_group_attr;?> class="<?php echo $tr_class;?>">
						<td colspan="3" class="wt_pklist_field_group">
							<div class="wt_pklist_field_group_hd">
								<?php echo isset($value['head']) ? $value['head'] : ''; ?>
								<div class="wt_pklist_field_group_toggle_btn" data-id="<?php echo isset($value['group_id']) ? $value['group_id'] : ''; ?>" data-visibility="<?php echo $visibility; ?>"><span class="dashicons dashicons-arrow-<?php echo ($visibility==1 ? 'down' : 'right'); ?>"></span></div>
							</div>
							<div class="wt_pklist_field_group_content">
								<table></table>
							</div>
						</td>
					</tr>
				<?php
				}else
				{					
					$field_name=isset($value['field_name']) ? $value['field_name'] : $value['option_name'];

					$form_toggler_p_class="";
					$form_toggler_register="";
					$form_toggler_child="";
					if(isset($value['form_toggler']))
					{
						if($value['form_toggler']['type']=='parent')
						{
							$form_toggler_p_class="wf_form_toggle";
							$form_toggler_register=' wf_frm_tgl-target="'.$value['form_toggler']['target'].'"';
						}
						elseif($value['form_toggler']['type']=='child')
						{
							$form_toggler_child=' wf_frm_tgl-id="'.$value['form_toggler']['id'].'" wf_frm_tgl-val="'.$value['form_toggler']['val'].'" '.(isset($value['form_toggler']['chk']) ? 'wf_frm_tgl-chk="'.$value['form_toggler']['chk'].'"' : '').(isset($value['form_toggler']['lvl']) ? ' wf_frm_tgl-lvl="'.$value['form_toggler']['lvl'].'"' : '');	
						}else
						{
							$form_toggler_child=' wf_frm_tgl-id="'.$value['form_toggler']['id'].'" wf_frm_tgl-val="'.$value['form_toggler']['val'].'" '.(isset($value['form_toggler']['chk']) ? 'wf_frm_tgl-chk="'.$value['form_toggler']['chk'].'"' : '').(isset($value['form_toggler']['lvl']) ? ' wf_frm_tgl-lvl="'.$value['form_toggler']['lvl'].'"' : '');	
							$form_toggler_p_class="wf_form_toggle";
							$form_toggler_register=' wf_frm_tgl-target="'.$value['form_toggler']['target'].'"';				
						}
						
					}

					$fld_attr=(isset($value['attr']) ? $value['attr'] : '');
					$field_only=(isset($value['field_only']) ? $value['field_only'] : false);
					$non_field=(isset($value['non_field']) ? $value['non_field'] : false);
					if($field_only===false)
					{
						$tooltip_html=self::set_tooltip($field_name,$base);	
						?>
						<tr valign="top" <?php echo $tr_id.$field_group_attr;?> <?php echo $form_toggler_child; ?> class="<?php echo $tr_class;?>">
					        <th scope="row" >
					        	<label for="<?php echo $field_name;?>"><?php echo isset($value['label']) ? $value['label'] : ''; ?><?php echo $tooltip_html;?></label>
					        </th>
					        <td>
					   <?php
					}
					if($non_field===true) // not form field type. Eg: plain text
					{
						if($type=='plaintext')
						{
							echo (isset($value['text']) ? $value['text'] : '');
						}
					}else
					{
		        		$vl=Wf_Woocommerce_Packing_List::get_option($value['option_name'],$base);
		        		$vl=is_string($vl) ? stripslashes($vl) : $vl;
			        	if($type=='text')
						{
			        	?>
			            	<input type="text" <?php echo $fld_attr;?> name="<?php echo $field_name;?>" value="<?php echo $vl;?>" />
			            <?php
			        	}
			        	if($type=='number')
						{
						?>
			            	<input type="number" <?php echo $fld_attr;?> name="<?php echo $field_name;?>" value="<?php echo $vl;?>" />
			            <?php
						}
			        	elseif($type=='textarea')
						{
						?>
			            	<textarea <?php echo $fld_attr;?> name="<?php echo $field_name;?>"><?php echo $vl;?></textarea>
			            <?php
						}elseif($type=='order_st_multiselect') //order status multi select
						{
							$order_statuses=isset($value['order_statuses']) ? $value['order_statuses'] : array();
							$field_vl=isset($value['field_vl']) ? $value['field_vl'] : array();
						?>
							<input type="hidden" name="<?php echo $field_name;?>_hidden" value="1" />
							<select class="wc-enhanced-select" id='<?php echo $field_name;?>_st' data-placeholder='<?php _e('Choose Order Status','wf-woocommerce-packing-list');?>' name="<?php echo $field_name;?>[]" multiple="multiple" <?php echo $fld_attr;?>>
			                    <?php
			                    $Pdf_invoice=$vl ? $vl : array();
			                    foreach($field_vl as $inv_key => $inv_value) 
			                    {
			            			echo "<option value=$inv_value".(in_array($inv_value, $Pdf_invoice) ? ' selected="selected"' : '').">$order_statuses[$inv_value]</option>";
			                        
			                    }
			                    ?>
			                </select>
						<?php
						}elseif($type=='checkbox') //checkbox
						{
							$field_vl=isset($value['field_vl']) ? $value['field_vl'] : "1";
						?>
							<input class="<?php echo $form_toggler_p_class;?>" type="checkbox" value="<?php echo $field_vl;?>" id="<?php echo $field_name;?>" name="<?php echo $field_name;?>" <?php echo ($field_vl==$vl ? ' checked="checked"' : '') ?> <?php echo $form_toggler_register;?> <?php echo $fld_attr;?>>
							<?php
						}
						elseif($type=='radio') //radio button
						{
							$radio_fields=isset($value['radio_fields']) ? $value['radio_fields'] : array();
							foreach ($radio_fields as $rad_vl=>$rad_label) 
							{
							?>
							<span style="display:inline-block;"><input type="radio" id="<?php echo $field_name.'_'.$rad_vl;?>" name="<?php echo $field_name;?>" class="<?php echo $form_toggler_p_class;?>" <?php echo $form_toggler_register;?> value="<?php echo $rad_vl;?>" <?php echo ($vl==$rad_vl) ? ' checked="checked"' : ''; ?> <?php echo $fld_attr;?> /> <?php echo $rad_label; ?> </span>
							&nbsp;&nbsp;
							<?php
							}
							
						}elseif($type=='uploader') //uploader
						{
							?>
							<div class="wf_file_attacher_dv">
					            <input id="<?php echo $field_name; ?>"  type="text" name="<?php echo $field_name; ?>" value="<?php echo $vl; ?>" <?php echo $fld_attr;?>/>
								
								<input type="button" name="upload_image" class="wf_button button button-primary wf_file_attacher" wf_file_attacher_target="#<?php echo $field_name; ?>" value="<?php _e('Upload','wf-woocommerce-packing-list'); ?>" />
							</div>
							<img class="wf_image_preview_small" src="<?php echo $vl ? $vl : Wf_Woocommerce_Packing_List::$no_image; ?>" />
							<?php
						}elseif($type=='select') //select
						{
							$select_fields=isset($value['select_fields']) ? $value['select_fields'] : array();
							?>
							<select name="<?php echo $field_name;?>" id="<?php echo $field_name;?>" class="<?php echo $form_toggler_p_class;?>" <?php echo $form_toggler_register;?> <?php echo $fld_attr;?>>
							<?php
							foreach ($select_fields as $sel_vl=>$sel_label) 
							{
							?>
								<option value="<?php echo $sel_vl;?>" <?php echo ($vl==$sel_vl) ? ' selected="selected"' : ''; ?>><?php echo $sel_label; ?></option>
							<?php
							}
							?>
							</select>
							<?php
						}elseif($type=='additional_fields') //additional fields
						{
							$module_base=isset($value['module_base']) ? $value['module_base'] : '';
							
							$fields=array();
				            $add_data_flds=array_flip(Wf_Woocommerce_Packing_List::$default_additional_data_fields); 
				            $user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields');
				            if(is_array($user_created))  //user created
				            {
				                $fields=array_merge($add_data_flds,$user_created);
				            }else
				            {
				                $fields=$add_data_flds; //default
				            }

				            //additional checkout fields
			                $additional_checkout=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
			            	$additional_checkout=Wf_Woocommerce_Packing_List::process_checkout_fields($additional_checkout);
			            	$fields=array_merge($fields,$additional_checkout);

				            $user_selected_arr=$vl && is_array($vl) ? $vl : array();
							?>
							<div class="wf_select_multi">
								<input type="hidden" name="wf_<?php echo $module_base;?>_contactno_email_hidden" value="1" />
					            <select class="wc-enhanced-select" name="wf_<?php echo $module_base;?>_contactno_email[]" multiple="multiple">
					            <?php
					            
					            foreach ($fields as $id => $name) 
					            { 
					                ?>
					                <option value="<?php echo $id;?>" <?php echo in_array($id,$user_selected_arr) ? 'selected' : '';?>><?php echo $name;?></option>
					                <?php
					            }
					            ?>						 
					            </select>
					            <br>
					            <button type="button" class="button button-secondary" data-wf_popover="1" data-title="<?php _e('Checkout Meta Key Fetcher','wf-woocommerce-packing-list'); ?>" data-module-base="<?php echo $module_base;?>" data-content="<?php _e('Field Name','wf-woocommerce-packing-list'); ?>: <input type='text' name='wf_old_custom_field' style='width:100%'/> <br> <?php _e('Meta Key','wf-woocommerce-packing-list'); ?>: <input type='text' name='wf_old_custom_field_meta' style='width:100%'/> " style="margin-top:5px; margin-left:5px; float: right;">
					                <?php _e('Add Existing Order Meta Field','wf-woocommerce-packing-list'); ?>                       
					             </button>
					            <?php
					        	if(isset($value['help_text']))
								{
					            ?>
					            <span class="wf_form_help" style="display:inline;"><?php echo $value['help_text']; ?></span>
					            <?php
					            	unset($value['help_text']);
					        	}
					        	?>
					        </div>
							<?php
						}elseif($type=='product_meta') //Product Meta
						{
							$module_base=isset($value['module_base']) ? $value['module_base'] : '';
							?>
							<div class="wf_select_multi">
								<input type="hidden" name="wf_<?php echo $module_base;?>_product_meta_fields_hidden" value="1" />
					            <select class="wc-enhanced-select" name="wf_<?php echo $module_base;?>_product_meta_fields[]" multiple="multiple">
					                <?php
					                $user_selected_arr=$vl && is_array($vl) ? $vl : array();
					                $wf_product_meta_fields=Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
					                if (is_array($wf_product_meta_fields))
					                {
					                    foreach ($wf_product_meta_fields as $key => $val){
					                        echo '<option value="'.$key.'"'.(in_array($key,$user_selected_arr) ? ' selected="selected"' : '').'>' . $val . '</option>';
					                    }
					                }
					                ?>						 
					            </select>
					            <br>
					            <button type="button" class="button button-secondary" data-wf_popover="1" data-title="Product Meta Key Fetcher" data-module-base="<?php echo $module_base;?>" data-content="<?php _e('Field Name','wf-woocommerce-packing-list'); ?>: <input type='text' name='wf_old_product_custom_field' style='width:100%'/> <br> <?php _e('Meta Key','wf-woocommerce-packing-list'); ?>: <input type='text' name='wf_old_product_custom_field_meta' style='width:100%'/> " style="margin-top:5px; margin-left:5px; float: right;"><?php _e('Add Product Meta','wf-woocommerce-packing-list'); ?></button>
					        	<?php
					        	if(isset($value['help_text']))
								{
					            ?>
					            <span class="wf_form_help" style="display:inline;"><?php echo $value['help_text']; ?></span>
					            <?php
					            	unset($value['help_text']);
					        	}
					        	?>
					        </div>
							<?php
						}
					}
					if(isset($value['help_text']))
					{
		            ?>
		            <span class="wf_form_help"><?php echo $value['help_text']; ?></span>
		            <?php
		        	}
		        	if($field_only===false)
					{
		        	?>
					        </td>
					        <td></td>
					    </tr>
		    		<?php
		    		}
		    	}
	    	}
		}
	}

	/**
	 * Envelope settings tab content with tab div.
	 * relative path is not acceptable in view file
	 */
	public static function envelope_settings_tabcontent($target_id,$view_file="",$html="",$variables=array(),$need_submit_btn=0)
	{
		extract($variables);
	?>
		<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
			<?php
			if($view_file!="" && file_exists($view_file))
			{
				include_once $view_file;
			}else
			{
				echo $html;
			}
			?>
			<?php 
			if($need_submit_btn==1)
			{
				include plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME)."admin/views/admin-settings-save-button.php";
			}
			?>
		</div>
	<?php
	}

	/**
	 Registers modules: public+admin	 
	 */
	public function admin_modules()
	{ 
		$wt_pklist_admin_modules=get_option('wt_pklist_admin_modules');
		if($wt_pklist_admin_modules===false)
		{
			$wt_pklist_admin_modules=array();
		}
		foreach (self::$modules as $module) //loop through module list and include its file
		{
			$is_active=1;
			if(isset($wt_pklist_admin_modules[$module]))
			{
				$is_active=$wt_pklist_admin_modules[$module]; //checking module status
			}else
			{
				$wt_pklist_admin_modules[$module]=1; //default status is active
			}
			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file) && $is_active==1)
			{
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			}else
			{
				$wt_pklist_admin_modules[$module]=0;	
			}
		}
		$out=array();
		foreach($wt_pklist_admin_modules as $k=>$m)
		{
			if(in_array($k,self::$modules))
			{
				$out[$k]=$m;
			}
		}
		update_option('wt_pklist_admin_modules',$out);
	}

	public static function module_exists($module)
	{
		return in_array($module,self::$existing_modules);
	}
	
	/**
	*	@since 4.0.5 
	* 	Recursively calculating and retriveing total files in the plugin temp directory
	*	@since 4.0.6 [Bugfix] Error when temp directory does not exists
	*/
	public static function get_total_temp_files()
	{
		$file_count=0;
		$upload_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
		if(is_dir($upload_dir))
		{
			$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::LEAVES_ONLY);		
			foreach($files as $name=>$file)
			{
				if(!$file->isDir())
				{
					$file_name=$file->getFilename();
					$file_ext_arr=explode('.', $file_name);
					$file_ext=end($file_ext_arr);
					if($file_ext=='pdf') //we are creating pdf files as temp files
					{
						$file_count++;
					}
				}
			} 
		}	
		return $file_count;
	}

	/**
	*	@since 4.0.5 
	* 	Schedule temp file clearing
	*/
	public function schedule_temp_file_clearing()
	{
		$is_auto_clear=Wf_Woocommerce_Packing_List::get_option('wf_pklist_auto_temp_clear');
		
		/* interval in minutes */
		$is_auto_clear_interval=(int) Wf_Woocommerce_Packing_List::get_option('wf_pklist_auto_temp_clear_interval');

		if($is_auto_clear=='Yes' && $is_auto_clear_interval>0) //if auto clear enabled, and interval greater than zero
		{
			if(!wp_next_scheduled('wt_pklist_auto_clear_temp_files'))
			{
                $start_time=strtotime("now +{$is_auto_clear_interval} minutes");
                wp_schedule_event($start_time, 'wt_pklist_temp_clear_interval', 'wt_pklist_auto_clear_temp_files');
			}
		}else
		{
			if(wp_next_scheduled('wt_pklist_auto_clear_temp_files')) //its already scheduled then remove
			{
				$this->unschedule_temp_file_clearing();	
			}
		}
	}


	/**
	*	@since 4.0.5 
	* 	Unschedule temp file clearing
	*/
	public function unschedule_temp_file_clearing()
	{
		wp_clear_scheduled_hook('wt_pklist_auto_clear_temp_files');
	}

	/**
	*	@since 4.0.5 
	* 	Registering new time interval for temp file deleting cron
	*/
	public function cron_interval_for_temp($schedules)
	{	
		$is_auto_clear=Wf_Woocommerce_Packing_List::get_option('wf_pklist_auto_temp_clear');
		if($is_auto_clear=='Yes') //if auto clear enabled
		{
			/* interval in minutes */
			$is_auto_clear_interval=(int) Wf_Woocommerce_Packing_List::get_option('wf_pklist_auto_temp_clear_interval');
			if($is_auto_clear_interval>0)
			{
				$schedules['wt_pklist_temp_clear_interval'] = array(
			        'interval' => ($is_auto_clear_interval * 60),
			        'display'  => sprintf(__('Every %d minutes', 'wf-woocommerce-packing-list'), $is_auto_clear_interval),
			    );
			}
		}
		return $schedules;
	}

	/**
	*	@since 4.0.5 
	* 	Delete temp files in the plugin temp directory
	*/
	public function delete_temp_files_recrusively()
	{
		$backup_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
		if(is_dir($backup_dir))
		{
		    $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backup_dir), RecursiveIteratorIterator::LEAVES_ONLY);
			foreach($files as $name=>$file)
			{
				if(!$file->isDir())
				{
					$file_name=$file->getFilename();
					$file_ext_arr=explode('.', $file_name);
					$file_ext=end($file_ext_arr);
					if($file_ext=='pdf' || $file_ext=='zip') //temp pdf files and zip files
					{
						@unlink($file);
					}
				}
			}
		}
	}

	/**
	*	@since 4.0.5 
	* 	Ajax hook for deleting files in the plugin temp directory
	*/
	public function delete_all_temp()
	{
		$out=array('status'=>0, 'msg'=>__('Error', 'wf-woocommerce-packing-list'));

		// Check permission
	    if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
		{
	    	echo json_encode($out);
	    	exit();
	    }

	    /* recrusively delete files */
	    $this->delete_temp_files_recrusively();

		$out['status']=1;
		$out['msg']=__('Successfully cleared all temp files.', 'wf-woocommerce-packing-list');
		$out['extra_msg']=__('No files found.', 'wf-woocommerce-packing-list');
		echo json_encode($out);
		exit();
	}

	/**
	*  	Download temp zip file via a nonce URL
	*	@since 4.0.6
	*/
	public function download_temp_zip_file()
	{
		if(isset($_GET['wt_pklist_download_temp_zip']))
		{
			if(self::check_write_access()) /* check nonce and role */
			{
				$file_name=(isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '');
				if($file_name!="")
				{
					$file_arr=explode(".", $file_name);
					$file_ext=end($file_arr);
					if($file_ext=='zip') /* only zip files */
					{
						$backup_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
						$file_path=$backup_dir.'/'.$file_name;
						if(file_exists($file_path)) /* check existence of file */
						{							
							header('Pragma: public');
						    header('Expires: 0');
						    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						    header('Cache-Control: private', false);
						    header('Content-Transfer-Encoding: binary');
						    header('Content-Disposition: attachment; filename="'.$file_name.'";');
						    header('Content-Type: application/zip');
						    header('Content-Length: '.filesize($file_path));

						    $chunk_size=1024 * 1024;
						    $handle=@fopen($file_path, 'rb');
						    while(!feof($handle))
						    {
						        $buffer = fread($handle, $chunk_size);
						        echo $buffer;
						        ob_flush();
						        flush();
						    }
						    fclose($handle);
						    exit();
						}
					}
				}	
			}
		}
	}


	/**
	*	@since 4.0.5 
	* 	Download all files as zip in the plugin temp directory
	*	@since 4.0.6 Direct access to zip file blocked. Generates a nonce URL for download
	*/
	public function download_all_temp()
	{
		$out=array('status'=>0, 'msg'=>__('Error', 'wf-woocommerce-packing-list'), 'fileurl'=>'');

		// Check permission
	    if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
		{
	    	echo json_encode($out);
	    	exit();
	    } 


		$zip = new ZipArchive();
		$backup_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
		$backup_url=Wf_Woocommerce_Packing_List::get_temp_dir('url');
		$backup_file_name='wt_pklist_temp_backup.zip';
		$backup_file=$backup_dir.'/'.$backup_file_name;
		$backup_file_url=$backup_url.'/'.$backup_file_name;

        $zip->open($backup_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        if(is_dir($backup_dir))
		{
			$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backup_dir),RecursiveIteratorIterator::LEAVES_ONLY);
			foreach($files as $name=>$file)
			{
				// Skip directories (they would be added automatically if not empty)
				if(!$file->isDir())
				{
					$file_name=$file->getFilename();
					$file_ext_arr=explode('.', $file_name);
					$file_ext=end($file_ext_arr);
					if($file_ext=='pdf') //we are creating pdf files as temp files
					{
						$filePath=$file->getRealPath();
						$relativePath=substr($filePath, strlen($backup_dir)+1);			
						$zip->addFile($filePath, basename($backup_dir).'/'.$relativePath);
					}        				
				}
			}
		}
		$zip->close();

		$out['status']=1;
		$out['msg']='';
		$out['fileurl']=html_entity_decode(wp_nonce_url(admin_url('admin.php?wt_pklist_download_temp_zip=true&file='.$backup_file_name), WF_PKLIST_PLUGIN_NAME));
		echo json_encode($out);
		exit();
	}

	/**
	*	@since 4.0.5 
	* 	Settings validation function for modules and plugin settings
	*/
	public function validate_settings_data($val, $key, $validation_rule=array())
	{		
		if(isset($validation_rule[$key]) && is_array($validation_rule[$key])) /* rule declared/exists */
		{
			if(isset($validation_rule[$key]['type']))
			{
				if($validation_rule[$key]['type']=='text')
				{
					$val=sanitize_text_field($val);
				}elseif($validation_rule[$key]['type']=='text_arr')
				{
					$val=self::sanitize_text_arr($val);
				}elseif($validation_rule[$key]['type']=='int')
				{
					$val=intval($val);
				}elseif($validation_rule[$key]['type']=='float')
				{
					$val=floatval($val);
				}elseif($validation_rule[$key]['type']=='int_arr')
				{
					$val=self::sanitize_text_arr($val, 'int');
				}
				elseif($validation_rule[$key]['type']=='textarea')
				{
					$val=sanitize_textarea_field($val);
				}else
				{
					$val=sanitize_text_field($val);
				}
			}
		}else
		{
			$val=sanitize_text_field($val);
		}
		return $val;
	}

	/**
	* Fields like `Order meta fields`, `Product meta fields` etc have extra popup for saving item. Ajax hook
	* @since 4.0.0
	* @since 4.0.1 added separate fields for key and value for checkout fields and added compatibility to old users
	* @since 4.0.3 is_required and placeholder options added
	* @since 4.0.5 Combined independent hooks from each modules
	*/
	public static function advanced_settings($module_base='',$module_id='')
	{
		$out=array('key'=>'','val'=>'','success'=>false,'msg'=>__('Error','wf-woocommerce-packing-list'));
		$warn_msg=__('Please enter mandatory fields','wf-woocommerce-packing-list');
		
		$module_base=(isset($_POST['wf_settings_base']) ? sanitize_text_field($_POST['wf_settings_base']) : 'main');
		$module_id=($module_base=='main' ? '' : Wf_Woocommerce_Packing_List::get_module_id($module_base));
		if(Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
			if(isset($_POST['new_custom_click']))  
			{
			    //additional fields for checkout
				if(isset($_POST['wf_new_custom_field']) && isset($_POST['wf_new_custom_field_key'])) 
		        {
		        	if(trim($_POST['wf_new_custom_field'])!="" && trim($_POST['wf_new_custom_field_key'])!="")
		        	{
			        	$vl=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields');
			        	$user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');

			            /* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
			            $user_created=Wf_Woocommerce_Packing_List::process_checkout_fields($user_created);

			            $add_checkout_data_flds=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;

			            $new_mata_key=Wf_Woocommerce_Packing_List::process_checkout_key($_POST['wf_new_custom_field_key']);		            
			            $new_meta_vl=sanitize_text_field($_POST['wf_new_custom_field']);

			            /* check the new key is not a builtin or custom */
			            if(!isset($user_created[$new_mata_key]) && !isset($add_checkout_data_flds[$new_mata_key]))
			            {
				            $user_created[$new_mata_key]=$new_meta_vl;
				            Wf_Woocommerce_Packing_List::update_option('wf_additional_checkout_data_fields',$user_created);
				            
				            $user_selected_array = ($vl && $vl!= '') ? $vl : array();
				            if(!in_array($new_mata_key,$user_selected_array)) 
				            {
				                $user_selected_array[]=$new_mata_key;
				                Wf_Woocommerce_Packing_List::update_option('wf_invoice_additional_checkout_data_fields',$user_selected_array);		                
				            }
				            $action='add';		            
			        	}else
			        	{
			       			//editing...
			       			$action='edit';
			        	}
			        	$out=array('key'=>$new_mata_key,'val'=>$new_meta_vl.' ('.$new_mata_key.')'.($is_required==1 ? ' ('.__('required','wf-woocommerce-packing-list').')' : ''),'success'=>true,'action'=>$action);

			        	//add metakey extra information (required, placeholder etc)
			            $field_extra_info=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
		                $placeholder=(isset($_POST['wf_new_custom_field_placeholder']) ? sanitize_text_field($_POST['wf_new_custom_field_placeholder']) : '');
		                $is_required=(isset($_POST['wt_pklist_cst_chkout_required']) ? intval($_POST['wt_pklist_cst_chkout_required']) : 0);
		                $field_extra_info[$new_mata_key]=array('placeholder'=>$placeholder,'is_required'=>$is_required,'title'=>$new_meta_vl);
		                Wf_Woocommerce_Packing_List::update_option('wt_additional_checkout_field_options',$field_extra_info);

		        	}else
		        	{
		        		$out['msg']=$warn_msg;
		        	}
		        }

		        //additional fields on invoice,packingslip etc (This for modules)
		        if(isset($_POST['wf_old_custom_field']) && isset($_POST['wf_old_custom_field_meta']) && $module_base!='' && $module_id!="") 
		        {
		            if(trim($_POST['wf_old_custom_field'])!="" && trim($_POST['wf_old_custom_field_meta'])!="")
		        	{
			            $key=str_replace(' ', '_',sanitize_text_field($_POST['wf_old_custom_field_meta']));
			            $val=sanitize_text_field($_POST['wf_old_custom_field']);
			            $vl=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields'); //this is plugin main setting so no need to specify module base
			            $data_array =$vl && $vl!="" ? $vl : array();
			            $data_array[$key] = $val;
			            Wf_Woocommerce_Packing_List::update_option('wf_additional_data_fields',$data_array);

			            $vl=Wf_Woocommerce_Packing_List::get_option('wf_'.$module_base.'_contactno_email',$module_id);
			            $data_slected_array =$vl!= '' ? $vl : array();

			            if(!in_array($key,$data_slected_array)) 
			            {
			                $data_slected_array[] = $key;
			                Wf_Woocommerce_Packing_List::update_option('wf_'.$module_base.'_contactno_email',$data_slected_array,$module_id);
			                $out=array('key'=>$key,'val'=>$val,'success'=>true,'action'=>'add');
			            }
			        }else
			        {
			        	$out['msg']=$warn_msg;
			        }
		        }

		        //Product Meta Fields (This for modules)
		        if(isset($_POST['wf_old_product_custom_field']) && isset($_POST['wf_old_product_custom_field_meta']) && $module_base!='' && $module_id!="") 
		        {
		            if(trim($_POST['wf_old_product_custom_field'])!="" && trim($_POST['wf_old_product_custom_field_meta'])!="")
		        	{
			            $key=str_replace(' ', '_',sanitize_text_field($_POST['wf_old_product_custom_field_meta']));
			            $val=sanitize_text_field($_POST['wf_old_product_custom_field']);
			            $vl=Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
			            $data_array = $vl && $vl!="" ? $vl : array();
			            $data_array[$key] = $val;
			            Wf_Woocommerce_Packing_List::update_option('wf_product_meta_fields',$data_array);

			            $vl=Wf_Woocommerce_Packing_List::get_option('wf_'.$module_base.'_product_meta_fields',$module_id);
			            $data_slected_array =$vl && $vl!="" ? $vl : array();

			            if (!in_array($key, $data_slected_array)) {
			                $data_slected_array[] = $key;
			                Wf_Woocommerce_Packing_List::update_option('wf_'.$module_base.'_product_meta_fields',$data_slected_array,$module_id);
			                $out=array('key'=>$key,'val'=>$val,'success'=>true,'action'=>'add');
			            }
			        }else
			        {
			        	$out['msg']=$warn_msg;
			        }
		        }
		    }
		}
	    echo json_encode($out);
		exit();
	}

	/**
	*	@since 4.0.5 
	* 	Save admin settings and module settings ajax hook
	*/
	public function save_settings()
	{
		$out=array(
			'status'=>false,
			'msg'=>__('Error', 'wf-woocommerce-packing-list'),
		);

		$base=(isset($_POST['wf_settings_base']) ? sanitize_text_field($_POST['wf_settings_base']) : 'main');
		$base_id=($base=='main' ? '' : Wf_Woocommerce_Packing_List::get_module_id($base));
		if(Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
    		$the_options=Wf_Woocommerce_Packing_List::get_settings($base_id);
    		
    		//multi select form fields array. (It will not return a $_POST val if it's value is empty so we need to set default value)
	        $default_val_needed_fields=array(
	        	'wf_invoice_additional_checkout_data_fields'=>array(),
	        	'woocommerce_wf_attach_shipping_label'=>array(),
		    ); //this is for plugin settings default. Modules can alter

	        /* this is an internal filter */
	        $default_val_needed_fields=apply_filters('wt_pklist_intl_alter_multi_select_fields', $default_val_needed_fields, $base_id);

	        $validation_rule=array(
				'woocommerce_wf_generate_for_orderstatus'=>array('type'=>'text_arr'),		
	        	'woocommerce_wf_attach_shipping_label'=>array('type'=>'text_arr'),
				'wf_additional_data_fields'=>array('type'=>'text_arr'),
				'wf_product_meta_fields'=>array('type'=>'text_arr'),
				'woocommerce_wf_generate_for_taxstatus'=>array('type'=>'text_arr'),
				'wf_additional_checkout_data_fields'=>array('type'=>'text_arr'),
				'wf_invoice_additional_checkout_data_fields'=>array('type'=>'text_arr'),
				'woocommerce_wf_packinglist_footer'=>array('type'=>'textarea'),
				'woocommerce_wf_packinglist_special_notes'=>array('type'=>'textarea'),
				'woocommerce_wf_packinglist_return_policy'=>array('type'=>'textarea'),
				'woocommerce_wf_packinglist_transport_terms'=>array('type'=>'textarea'),
				'woocommerce_wf_packinglist_sale_terms'=>array('type'=>'textarea'),
				'woocommerce_wf_packinglist_boxes'=>array('type'=>'text_arr'),
				'wt_additional_checkout_field_options'=>array('type'=>'text_arr'),
				'wf_pklist_auto_temp_clear_interval'=>array('type'=>'int'),
		    ); //this is for plugin settings default. Modules can alter

	        $validation_rule=apply_filters('wt_pklist_intl_alter_validation_rule', $validation_rule, $base_id);	        

	        foreach($the_options as $key => $value) 
	        {
	            if(isset($_POST[$key]))
	            {
	            	$the_options[$key]=$this->validate_settings_data($_POST[$key], $key, $validation_rule);
	            	if($key=='woocommerce_wf_packinglist_boxes')
	            	{
	            		$the_options[$key]=$this->validate_box_packing_field($_POST[$key]);
	            	}
	            }else
	            {
	            	if(array_key_exists($key,$default_val_needed_fields))
	            	{
	            		/* Set a hidden field for every multi-select field in the form. This will be used to populate the multi-select field with an empty array when it does not have any value. */
	            		if(isset($_POST[$key.'_hidden']))
	            		{
	            			$the_options[$key]=$default_val_needed_fields[$key];
	            		}	
	            	}
	            }
	        }
	        Wf_Woocommerce_Packing_List::update_settings($the_options, $base_id);

	        do_action('wf_pklist_intl_after_setting_update', $the_options, $base_id);

	        $out['status']=true;
	        $out['msg']=__('Settings Updated', 'wf-woocommerce-packing-list');
    	}
		echo json_encode($out);
		exit();
	}

	/**
	*	@since 4.0.5 
	* 	Strip unwanted HTML from template HTML
	*/
	public static function strip_unwanted_tags($html)
	{
		$html=html_entity_decode(stripcslashes($html));
		$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
		$html = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);
		$html = preg_replace('#<audio(.*?)>(.*?)</audio>#is', '', $html);
		$html = preg_replace('#<video(.*?)>(.*?)</video>#is', '', $html);
		return $html;
	}
}
