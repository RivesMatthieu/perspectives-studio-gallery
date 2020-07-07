(function( $ ) {
	//'use strict';

	function wf_toggle_invoice_number_fields()
	{
		var vl=$('#woocommerce_wf_invoice_number_format').val();
		var number_tr=$('[name="woocommerce_wf_invoice_as_ordernumber"]').parents('tr');
		var prefix_tr=$('[name="woocommerce_wf_invoice_number_prefix"]').parents('tr');
		var postfix_tr=$('[name="woocommerce_wf_invoice_number_postfix"]').parents('tr');
		var start_tr=$('#woocommerce_wf_invoice_start_number_tr');
		number_tr.hide().find('th label').css({'padding-left':'0px'});
		prefix_tr.hide().find('th label').css({'padding-left':'0px'});
		postfix_tr.hide().find('th label').css({'padding-left':'0px'});
		start_tr.hide().find('th label').css({'padding-left':'0px'});

		$('.form-table th label').css({'float':'left','width':'100%'});

		var num_reg=/\[number\]/gm;
		var pre_reg=/\[prefix\]/gm;
		var pos_reg=/\[suffix\]/gm;

		if(vl.search(num_reg)>=0)
		{
			number_tr.show().find('th label').animate({'padding-left':'15px'});
			if($('[name="woocommerce_wf_invoice_as_ordernumber"]:checked').val()=='No')
			{
				start_tr.show().find('th label').animate({'padding-left':'30px'});
			}
		}
		if(vl.search(pre_reg)>=0)
		{  
			prefix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
		if(vl.search(pos_reg)>=0)
		{
			postfix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
	}
	$('#woocommerce_wf_invoice_number_format').change(function(){
		wf_toggle_invoice_number_fields();
	});
	wf_toggle_invoice_number_fields();

	$('.wf_inv_num_frmt_hlp_btn').click(function(){
		var trgt_field=$(this).attr('data-wf-trget');
		$('.wf_inv_num_frmt_hlp').attr('data-wf-trget',trgt_field);
		wf_popup.showPopup($('.wf_inv_num_frmt_hlp'));
	});

	$('.wf_inv_num_frmt_append_btn').click(function(){
		var trgt_elm_name=$(this).parents('.wf_inv_num_frmt_hlp').attr('data-wf-trget');
		var trgt_elm=$('[name="'+trgt_elm_name+'"]');
		var exst_vl=trgt_elm.val();
		var cr_vl=$(this).text();
		if($('[name="wf_inv_num_frmt_data_val"]:checked').length>0)
		{
			var data_val=$('[name="wf_inv_num_frmt_data_val"]:checked').val();
			const regex = /\[(.*?)\]/gm;
			cr_vl=cr_vl.replace(regex, "[$1 data-val='"+data_val+"']");
		}
		trgt_elm.val(exst_vl+cr_vl);
		wf_popup.hidePopup();
	});
})( jQuery );