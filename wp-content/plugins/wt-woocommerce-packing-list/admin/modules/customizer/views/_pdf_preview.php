<?php
if (!defined('ABSPATH')) {
    exit;
}
$query = new WC_Order_Query( array(
    'limit' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'parent'=>0,
    //'return' => 'ids',
) );

$orders = $query->get_orders();
if(count($orders)>0)
{
	$order=$orders[0];
	$order_number=$order->get_order_number();
	$tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('preview_option', $module_id);
?>
	<div style="float:left; width:100%; margin-bottom:10px; padding:10px; padding-top:0px; box-sizing:border-box; height:40px; overflow:hidden; border:solid 1px #fff;" class="wf_sample_pdf_options">
		<a class="wf_download_sample_pdf wf_codeview_link_btn <?php echo $tooltip_conf['class'];?>" style="margin-top:9px;" <?php echo $tooltip_conf['text'];?>>
			<span class="dashicons dashicons-external"></span>
			<?php _e('Preview sample PDF','wf-woocommerce-packing-list');?>
			(<?php _e('Order','wf-woocommerce-packing-list');?>: <span class="wf_sample_pdf_order_no_preview"><?php echo $order_number;?></span>)
		</a>		
		<a class="wf_codeview_link_btn wf_sample_pdf_options_btn" style="float:right; margin-right:-5px; font-weight:900; font-size:22px; line-height:15px; color:#333;">...</a>
		<span class="spinner" style="margin-top:11px; display:none;"></span>
		<label style="font-weight:bold; margin-top:9px; margin-bottom:5px; float:left; width:100%;"><?php _e('Order number','wf-woocommerce-packing-list');?></label>
		<input type="number" style="width:99%;" name="wf_sample_pdf_order_no" value="<?php echo $order_number;?>" class="wf_pklist_text_field" placeholder="<?php _e('Order number','wf-woocommerce-packing-list');?>">
	</div>
<?php
}
?>