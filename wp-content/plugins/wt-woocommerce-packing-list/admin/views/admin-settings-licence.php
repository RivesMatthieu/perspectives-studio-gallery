<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<?php
	$plugin_name =WF_PKLIST_ACTIVATION_ID;   
	require_once(plugin_dir_path( dirname( __FILE__ ) ).'wf_api_manager/html/html-wf-activation-window.php' );
	?>
</div>