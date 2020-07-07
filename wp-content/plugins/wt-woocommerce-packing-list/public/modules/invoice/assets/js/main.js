(function( $ ) {
	//'use strict';
	$(function() {
		wf_invoice_update_order_status_to_email_select_box();
		$('#woocommerce_wf_generate_for_orderstatus_st').on('change',function(){
			wf_invoice_update_order_status_to_email_select_box();
		})

	});
	function wf_invoice_update_order_status_to_email_select_box()
	{
		var attch_inv_elm=$('#woocommerce_wf_attach_invoice_st');
		var attch_inv_vl=attch_inv_elm.val();
		attch_inv_vl=attch_inv_vl!==null ? attch_inv_vl : new Array();
		var html='';
		$('#woocommerce_wf_generate_for_orderstatus_st').find('option:selected').each(function(){
			var slcted=$.inArray($(this).val(),attch_inv_vl)==-1 ? '' : 'selected';
			html+='<option value="'+$(this).val()+'" '+slcted+'>'+$(this).html()+'</option>';
		});
		attch_inv_elm.html(html).trigger('change');
	}
	
})( jQuery );