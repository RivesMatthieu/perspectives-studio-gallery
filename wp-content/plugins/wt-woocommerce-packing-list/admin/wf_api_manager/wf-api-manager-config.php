<?php
$product_name=WF_PKLIST_ACTIVATION_ID; // name should match with 'Software Title' configured in server, and it should not contains white space
$product_version=WF_PKLIST_VERSION;
$product_slug=WF_PKLIST_PLUGIN_BASENAME; //product base_path/file_name
$serve_url='https://www.webtoffee.com/';
$plugin_settings_url=admin_url('admin.php?page='.WF_PKLIST_POST_TYPE);

//include api manager
include_once ( 'wf_api_manager.php' );
new WF_API_Manager($product_name, $product_version, $product_slug, $serve_url, $plugin_settings_url);
?>
