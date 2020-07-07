(function( $ ) {
	'use strict';
	$(function() {

		if($('[name="woocommerce_wf_invoice_as_ordernumber"]:checked').val()=='Yes')
		{
			$('[name="woocommerce_wf_invoice_as_ordernumber"]').parents('tr').find('.wf_form_help').show();
		}else
		{
			$('[name="woocommerce_wf_invoice_as_ordernumber"]').parents('tr').find('.wf_form_help').hide();	
		}
		$('[name="woocommerce_wf_invoice_as_ordernumber"]').click(function(){
			if($(this).val()=='Yes')
			{
				$(this).parents('tr').find('.wf_form_help').show();
			}else
			{
				$(this).parents('tr').find('.wf_form_help').hide();	
			}
		});

	});
})( jQuery );