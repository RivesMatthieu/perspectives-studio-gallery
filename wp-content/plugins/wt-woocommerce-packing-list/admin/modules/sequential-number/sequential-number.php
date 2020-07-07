<?php
/**
 * Template sequential number generator and processor
 *
 * @link       
 * @since 4.0.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wf_Woocommerce_Packing_List_Sequential_Number
{
	public $module_base='sequential-number';
	public static $module_id_static='';
	private $to_module='';
	private $to_module_id='';
	private static $to_module_title='';
	public static $return_dummy_invoice_number=false;  //it will return dummy invoice number if force generate is on
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		/* hook to woocommerce status changed to generate seq number on order status change */ 
		add_action('woocommerce_order_status_changed', array(__CLASS__,'generate_sequential_number_on_status_change'),10,3);

		/*  Update auto increment number after settings update */
		add_action('wf_pklist_intl_after_setting_update', array($this, 'after_setting_update'), 10, 2);
	}

	/**
	*	Update auto increment number after settings update
	*	@since 4.0.5
	*	
	*/
	public function after_setting_update($the_options, $base_id)
	{
		if(isset($_POST['update_sequential_number']))
		{
			if(sanitize_text_field($_POST['update_sequential_number'])==$base_id)
			{
				$this->set_current_sequential_autoinc_number($base_id);
			}
		}
	}


	/**
	*	Generate sequential number on order status change, If user set status to generate sequential number. 
	*	Other modules like invocie, proforma invoice can hook to this method.
	*	@since 4.0.4
	*	
	*/
	public static function generate_sequential_number_on_status_change($order_id,$old_status,$new_status)
	{
		if(!$order_id){
        	return;
    	}

    	$module_id_arr=array(); //taking module id to generate wc tankyou to hook number generation
    	$module_id_arr=apply_filters('wf_pklist_enable_sequential_number_on_status_change',$module_id_arr,$order_id);
    	$module_id_arr=(!is_array($module_id_arr) ? array() : $module_id_arr);
   	
		if(count($module_id_arr)>0)
		{
    		// Get an instance of the WC_Order object
        	$order=wc_get_order($order_id);
        	$status=get_post_status($order_id);

        	foreach($module_id_arr as $module_id=>$call_bck) 
        	{
        		$generate_invoice_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$module_id);
				$force_generate=in_array($status,$generate_invoice_for) ? true :false;		
				if($force_generate===true) //only generate if user set status to generate invoice
				{
					if(is_callable($call_bck))
					{
						call_user_func($call_bck,$order,$force_generate); //call module callback
					}
				}
        	}
    	}
	}

	/**
	 * 	@since 4.0.4 
	 * 	Initializing Sequential Number under module settings page hook
	 */
	public function init($base, $title)
	{
		$this->to_module = $base;
		$this->to_module_id = Wf_Woocommerce_Packing_List::get_module_id($base);
		self::$to_module_title = $title;

		add_filter('wf_pklist_module_settings_tabhead',array( __CLASS__,'settings_tabhead'));
		add_action('wf_pklist_module_out_settings_form',array($this,'out_settings_form'));
	}

	/**
	 *  @since 4.0.4
	 * 	Tab head for module settings page
	 **/
	public static function settings_tabhead($arr)
	{
		$added=0;
		$out_arr=array();
		$menu_pos_key='general';
		$tab_title=__(sprintf('%s number',self::$to_module_title),'wf-woocommerce-packing-list');
		foreach($arr as $k=>$v)
		{
			$out_arr[$k]=$v;
			if($k==$menu_pos_key && $added==0)
			{				
				$out_arr[self::$module_id_static]=$tab_title;
				$added=1;
			}
		}
		if($added==0){
			$out_arr[self::$module_id_static]=$tab_title;
		}
		return $out_arr;
	}

	/**
	 * Add seqential number tab
	 * @since 4.0.4
	 **/
	public function out_settings_form($args)
	{
		wp_enqueue_script($this->module_id,plugin_dir_url( __FILE__ ).'assets/js/sequential_number.js',array('jquery'),WF_PKLIST_VERSION);	
		$view_file=plugin_dir_path( __FILE__ ).'views/sequential-number.php';
		$params=array(
			'to_module'=>$this->to_module,
			'to_module_id'=>$this->to_module_id,
			'to_module_title'=>self::$to_module_title,
		);
		Wf_Woocommerce_Packing_List_Admin::envelope_settings_tabcontent($this->module_id,$view_file,'',$params,0);
	}

	/**
	* Get order date timestamp
	* @since 4.0.4
	* @return integer
	*/
    protected static function get_orderdate_timestamp($order_id)
    {
    	$order_date=get_the_date('Y-m-d h:i:s A',$order_id);
		return strtotime($order_date);
    }

    /**
	* Function to generate sequential number
	* @since 4.0.4
	* @return mixed
	*/
    public static function generate_sequential_number($order, $module_id, $keys= array('number'=>'wf_invoice_number', 'date'=>'wf_invoice_date','enable'=>''), $force_generate=true) 
    {
	    //if module (Eg: invoice) is disabled then force generate is always false, otherwise the value of argument
	    if($keys['enable']!="") //if module has such an option (Invoice module have that option)
	    {
	    	$force_generate=Wf_Woocommerce_Packing_List::get_option($keys['enable'], $module_id)=='No' ? false : $force_generate;
	    }
	    $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
	    $wf_invoice_id = get_post_meta($order_id, $keys['number'], true);
	    if(!empty($wf_invoice_id))
	    {
	    	/* order date as invoice date, adding compatibility with old orders  */
	    	$invoice_date=get_post_meta($order_id, $keys['date'], true);
	    	$invoice_date_hid=get_post_meta($order_id, '_'.$keys['date'], true);
	    	if(empty($invoice_date) && empty($invoice_date_hid))
	    	{
	    		/* set order date as invoice date */
	    		$order_date=self::get_orderdate_timestamp($order_id);
				update_post_meta($order_id, '_'.$keys['date'], $order_date);
	    	}else
	    	{
	    		if(!empty($invoice_date))
	    		{
	    			delete_post_meta($order_id, $keys['date']);
	    			update_post_meta($order_id, '_'.$keys['date'], $invoice_date);
	    		}
	    	}
	        return $wf_invoice_id;
	    }else
	    {
	    	if($force_generate===false)
	    	{
	    		if(self::$return_dummy_invoice_number)
	    		{
	    			return 123456;
	    		}else
	    		{
	    			return '';
	    		}
	    	}
	    }
	    if(self::$return_dummy_invoice_number)
	    {
	    	return 123456;
	    }
	    //$all_invoice_numbers =self::wf_get_all_invoice_numbers();
	    $wf_invoice_as_ordernumber =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_as_ordernumber', $module_id);
	    $generate_invoice_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $module_id);
	    if($wf_invoice_as_ordernumber == "Yes")
	    {
	    	if(is_a($order, 'WC_Order'))
	    	{
	    		$order_num=	$order->get_order_number();
	    	}else
	    	{
	    		$parent_id= $order->get_parent_id();
	    		$parent_order=( WC()->version < '2.7.0' ) ? new WC_Order($parent_id) : new wf_order($parent_id);
	    		$order_num=	$parent_order->get_order_number();
	    	}
	    	$inv_num= $order_num;	
	    }else
	    {
	    	$current_invoice_number =(int) Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_Current_Invoice_number', $module_id); 
	    	$inv_num=++$current_invoice_number;
	    	$padded_next_invoice_number=self::add_sequential_padding($inv_num, $module_id);
	        $postfix_prefix_padded_next_invoice_number=self::add_postfix_prefix($padded_next_invoice_number, $module_id, $order);
	        while(self::wf_is_sequential_number_exists($postfix_prefix_padded_next_invoice_number, $keys['number']))
            { 
                 $inv_num++;
                 $padded_next_invoice_number=self::add_sequential_padding($inv_num, $module_id);
                 $postfix_prefix_padded_next_invoice_number=self::add_postfix_prefix($padded_next_invoice_number, $module_id, $order);               
            }
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_Current_Invoice_number', $inv_num, $module_id);
	    }
	    $padded_invoice_number=self::add_sequential_padding($inv_num, $module_id);
        $invoice_number=self::add_postfix_prefix($padded_invoice_number, $module_id, $order);
        update_post_meta($order_id, $keys['number'], $invoice_number);

        $orderdate_as_invoicedate=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_orderdate_as_invoicedate', $module_id);
        $invoicedate=time();
        if($orderdate_as_invoicedate=='Yes')
        {
        	$invoicedate=self::get_orderdate_timestamp($order_id);
        }
        update_post_meta($order_id, '_'.$keys['date'], $invoicedate);      
        return $invoice_number;
	}

	/**
	* Get sequential number date (Eg: Invoice date)
	* @since 4.0.4
	* @return mixed
	*/
    public static function get_sequential_date($order_id, $key, $date_format, $order)
    {
    	$invoice_date=get_post_meta($order_id, '_'.$key, true);
    	if($invoice_date)
    	{
    		return (empty($invoice_date) ? '' : date($date_format, $invoice_date));
    	}else
    	{
    		if(self::$return_dummy_invoice_number)
	    	{
	    		return date($date_format);
	    	}else
	    	{
	    		return '';
	    	}
    	}
    }

    /** 
    *	@since 4.0.4
	* 	Get all sequential numbers
	* 	@return int
	*/
	public static function wf_get_all_sequential_numbers($key='wf_invoice_number') 
	{
        global $wpdb;
        $post_type = 'shop_order';

        $r = $wpdb->get_col($wpdb->prepare("
	    SELECT pm.meta_value FROM {$wpdb->postmeta} pm
	    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	    WHERE pm.meta_key = '%s' 
	    AND p.post_type = '%s'", $key, $post_type));
        return $r;
    }


    /** 
    *	@since 4.0.4
	* 	Check sequential number already exists
	* 	@return boolean
	*/
	public static function wf_is_sequential_number_exists($invoice_number, $key='wf_invoice_number') 
	{
		global $wpdb;
        $post_type = 'shop_order';

        $r = $wpdb->get_col($wpdb->prepare("
	    SELECT COUNT(pm.meta_value) AS inv_exists FROM {$wpdb->postmeta} pm
	    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	    WHERE pm.meta_key = '%s' 
	    AND p.post_type = '%s' AND pm.meta_value = '%s'", $key, $post_type,$invoice_number));
        return $r[0]>0 ? true : false;
	}

	/**
	*	@since 4.0.4
	* 	This function sets the autoincrement value while admin edits sequential number settings
	*/
	public function set_current_sequential_autoinc_number($module_id)
	{ 
		$wf_invoice_as_ordernumber =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_as_ordernumber', $module_id);
	    $generate_invoice_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $module_id);
	    if($wf_invoice_as_ordernumber == "Yes")
	    {
	    	return true; //no need to set a starting number	
	    }else
	    {
	    	$current_invoice_number =(int) Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_Current_Invoice_number', $module_id); 
	    	$inv_num=++$current_invoice_number;
	    	$padded_next_invoice_number=self::add_sequential_padding($inv_num,$module_id);
	        $postfix_prefix_padded_next_invoice_number=self::add_postfix_prefix($padded_next_invoice_number,$module_id);
	        while(self::wf_is_sequential_number_exists($postfix_prefix_padded_next_invoice_number))
            { 
                 $inv_num++;
                 $padded_next_invoice_number=self::add_sequential_padding($inv_num,$module_id);
                 $postfix_prefix_padded_next_invoice_number=self::add_postfix_prefix($padded_next_invoice_number,$module_id);               
            }
            //$inv_num is the next invoice number so next starting number will be one lesser than the $inv_num
            $inv_num=$inv_num-1;
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_Current_Invoice_number',$inv_num,$module_id);
            return true;
	    }
	    return false;
	}

	/**
	* 	@since 4.0.5			
	*	Declaring validation rule for form fields in settings form
	*
	*/
	public static function get_validation_rule()
	{
		return array(
			'woocommerce_wf_Current_Invoice_number'=>array('type'=>'int'),
			'woocommerce_wf_invoice_start_number'=>array('type'=>'int'),
			'woocommerce_wf_invoice_padding_number'=>array('type'=>'int'),
		);
	}

	/**
	* 	@since 4.0.4			
	*	Retriving default values for sequential number tab
	*
	*/
	public static function get_sequential_field_default_settings()
	{
		return array(
			'woocommerce_wf_invoice_number_format'=>"[number]",
			'woocommerce_wf_Current_Invoice_number'=>1,
			'woocommerce_wf_invoice_start_number'=>1,
			'woocommerce_wf_invoice_number_prefix'=>'',
			'woocommerce_wf_invoice_padding_number'=>0,
			'woocommerce_wf_invoice_number_postfix'=>'',
			'woocommerce_wf_invoice_as_ordernumber'=>"Yes",
			'woocommerce_wf_orderdate_as_invoicedate'=>"Yes",
		);
	}

	/**
	*	@since 4.0.4
	* 	Adding padding number to sequential number
	*/
	public static function add_sequential_padding($wf_invoice_number,$module_id) 
	{
        $padded_invoice_number = '';
        $padding_count =(int) Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_padding_number',$module_id)- strlen($wf_invoice_number);
        if ($padding_count > 0) {
            for ($i = 0; $i < $padding_count; $i++)
            {
                $padded_invoice_number .= '0';
            }
        }
        return $padded_invoice_number.$wf_invoice_number;
    }

    
    /**
    *   @since 4.0.4
	* 	Replace date shortcode from sequential number prefix/postfix data
	*	@since 4.0.5 [Bugfix] WP not accepting date format without separator. Added a fixed date format for WP function. 
	* 	@return string
	*/
    public static function get_shortcode_replaced_date($shortcode_text, $order=null) 
	{	
	    preg_match_all("/\[([^\]]*)\]/", $shortcode_text, $matches);
	    if(!empty($matches[1]))
	    { 
	        foreach($matches[1] as $date_shortcode) 
	        { 
	        	$match=array();
	        	$date_val=time();
	        	$date_shortcode_format=$date_shortcode;
	            if(preg_match('/data-val=\'(.*?)\'/s', $date_shortcode, $match))
	            { 
	            	if(trim($match[1])=='order_date')
	            	{
	            		$date_shortcode_format=trim(str_replace($match[0], '', $date_shortcode));           		
	            		if(!is_null($order))
	            		{ 
	            			$wc_version=WC()->version;
							$order_id=$wc_version<'2.7.0' ? $order->id : $order->get_id();
							$date_val=strtotime(get_the_date('Y-m-d H:i:s', $order_id));
	            		}
	            	}
	            }
	            $date=date($date_shortcode_format, $date_val);
	            $shortcode_text=str_replace("[$date_shortcode]", $date, $shortcode_text); 
	        }
	    }
	    return $shortcode_text;
	}

    /** 
	* 	@since 4.0.4
	*	Add Prefix/Postfix to sequential number
	* 	@return string
	*/
	public static function add_postfix_prefix($padded_invoice_number,$module_id, $order=null) 
	{          
        $invoice_format =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_number_format',$module_id);
        $prefix_data =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_number_prefix',$module_id);
        $postfix_data =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_number_postfix',$module_id);
        if($invoice_format=="")
        {
            if($prefix_data!='' && $postfix_data!='')
            {
            	$invoice_format='[prefix][number][suffix]';
            }
            elseif($prefix_data!='')
            {
            	$invoice_format = '[prefix][number]'; 
            }
            elseif($postfix_data!= '')
            {
                $invoice_format = '[number][suffix]'; 
            }
        }
        if($prefix_data != '')
        {
            $prefix_data=self::get_shortcode_replaced_date($prefix_data, $order);
        }
        if($postfix_data != '')
        {
            $postfix_data=self::get_shortcode_replaced_date($postfix_data, $order);
        }
        return str_replace(array('[prefix]','[number]','[suffix]'),array($prefix_data,$padded_invoice_number,$postfix_data),$invoice_format); 
    }

}