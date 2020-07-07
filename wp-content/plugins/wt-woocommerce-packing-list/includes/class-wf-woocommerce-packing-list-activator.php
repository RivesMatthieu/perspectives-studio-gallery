<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webtoffee.com/
 * @since      4.0.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      4.0.0
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Wf_Woocommerce_Packing_List_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    4.0.0
	 */
	public static function activate() 
	{ 
	    global $wpdb;
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach($blog_ids as $blog_id ) 
            {
                switch_to_blog( $blog_id );
                self::install_tables();
                restore_current_blog();
            }
        }
        else 
        {
            self::install_tables();
        }

        self::secure_upload_dir();
	}

	/**
	*	@since 4.0.6
	*	Secure directory with htaccess	
	*/
	public static function secure_upload_dir()
	{
		$upload_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');

        if(!is_dir($upload_dir))
        {
            @mkdir($upload_dir, 0700);
        }

        $files_to_create=array('.htaccess' => 'deny from all', 'index.php'=>'<?php // Silence is golden');
        foreach($files_to_create as $file=>$file_content)
        {
        	if(!file_exists($upload_dir.'/'.$file))
	        {
	            $fh=@fopen($upload_dir.'/'.$file, "w");
	            if(is_resource($fh))
	            {
	                fwrite($fh,$file_content);
	                fclose($fh);
	            }
	        }
        }    
	}

	public static function install_tables()
	{
		global $wpdb;
		//install necessary tables
		//creating table for saving template data================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $tb=Wf_Woocommerce_Packing_List::$template_data_tb;
        $like = '%' . $wpdb->prefix.$tb.'%';
        $table_name = $wpdb->prefix.$tb;
        if(!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) 
        {
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
			  `id_wfpklist_template_data` int(11) NOT NULL AUTO_INCREMENT,
			  `template_name` varchar(200) NOT NULL,
			  `template_html` text NOT NULL,
			  `template_from` varchar(200) NOT NULL,
			  `is_active` int(11) NOT NULL DEFAULT '0',
			  `template_type` varchar(200) NOT NULL,
			  `created_at` int(11) NOT NULL DEFAULT '0',
			  `updated_at` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY(`id_wfpklist_template_data`)
			) DEFAULT CHARSET=utf8;";
            dbDelta($sql_settings);
        }
        //creating table for saving template data================
	}

}
