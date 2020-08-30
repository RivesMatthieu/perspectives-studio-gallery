<?PHP
/*
Plugin Name: No Right Click Images Plugin
Plugin URI: https://www.facebook.com/BlogsEye/
Description: Uses Javascript to prevent right clicking of images to help keep leaches from copying images
Version: 3.5
Author: Keith P. Graham
Author URI: https://www.facebook.com/BlogsEye/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
if (!defined('ABSPATH')) exit; // just in case


// add scripts to page
add_action( 'wp_enqueue_scripts', 'nrci_scripts' ); // enqueue the scripts
add_action('admin_menu', 'kpg_no_rc_img_init');	

function nrci_scripts() {
	$options=kpg_nrci_get_options();
	extract($options);
	if (function_exists('is_user_logged_in')) { // for non wp versions
		// check to see if we should load js
		if(is_user_logged_in()) {
			if( current_user_can('editor') || current_user_can('administrator') ) {
				if ($admin=='Y') {
					return; // admins can right click
				}
			}
			if ($allowforlogged=='Y') return;
		}
	}	
	wp_enqueue_script( 'nrci_methods', plugin_dir_url( __FILE__ ) . 'no-right-click-images.js', array(), null, false );
	$opts=array(
	'gesture'=>$gesture,
	'drag'=>$drag,
	'touch'=>$touch,
	'admin'=>$admin
	);
	wp_localize_script( 'nrci_methods', 'nrci_opts', $opts );
	if ($ios=='Y') {
		wp_register_style('nrci_methods', plugin_dir_url( __FILE__ ) . 'no-right-click-images.css');
		wp_enqueue_style( 'nrci_methods' );
	}

}
function kpg_no_rc_img_control()  {
	nrci_load_control();
	kpg_no_rc_img_control_2();
}
function nrci_load_control() {
	require_once('includes/nrci_options.php');
}

function kpg_no_rc_img_init() {
	if ( function_exists('register_uninstall_hook') ) {
		register_uninstall_hook(__FILE__, 'kpg_no_rc_img_uninstall');
	}
   add_options_page('No Right Click Images', 'No Right Click Images', 'manage_options',__FILE__,'kpg_no_rc_img_control');
}
// uninstall
function kpg_no_rc_img_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_no_right_click_image'); 
	return;
}
function kpg_nrci_get_options() {
	// before we begin we need to check if we need to redirect the options to blog 1
	$opts=get_option('kpg_no_right_click_image');
	$opts1=$opts;
	if (empty($opts)||!is_array($opts)) {
		$opts=array();
		$opts['gesture']='Y';
		$opts['drag']='Y';
		$opts['touch']='Y';
		$opts['allowforlogged']='N';
		$opts['ios']='N';
		$opts['admins']='Y';
	}
	if (!array_key_exists('gesture',$opts)||empty($opts['gesture'])) $opts['gesture']='Y';
	if (!array_key_exists('drag',$opts)||empty($opts['drag'])) $opts['drag']='Y';
	if (!array_key_exists('touch',$opts)||empty($opts['touch'])) $opts['touch']='Y';
	if (!array_key_exists('ios',$opts)||empty($opts['ios'])) $opts['ios']='N';
	if (!array_key_exists('allowforlogged',$opts)||empty($opts['allowforlogged'])) $opts['allowforlogged']='N';
	if (!array_key_exists('admin',$opts)||empty($opts['admin'])) $opts['admin']='Y';
	
	if ($opts['gesture']!='Y') $opts['gesture']='N';
	if ($opts['drag']!='Y') $opts['drag']='N';
	if ($opts['allowforlogged']!='Y') $opts['allowforlogged']='N';
	if ($opts['touch']!='Y') $opts['touch']='N';
	if ($opts['ios']!='Y') $opts['ios']='N';
	if ($opts['admin']!='Y') $opts['admin']='N';
	if ($opts1!==$opts) {
		update_option('kpg_no_right_click_image',$opts);
	}


	return $opts;
}// done

// bottom	
?>