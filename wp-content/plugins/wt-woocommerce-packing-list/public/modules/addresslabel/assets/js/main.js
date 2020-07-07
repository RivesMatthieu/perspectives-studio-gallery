(function( $ ) {
	'use strict';
	$(function() {

		$('.wf_cst_change_addrlabel').change(function(){
			var ind=$(this).val();
			if(ind==""){ return false; }
			var trgt_elm=$('.wf_default_template_list_item:eq('+ind+')').find('.wfte_hidden_template_html');
			if(trgt_elm.length>0)
			{
				var template_html=trgt_elm.html();
				$('#wfte_code').val(template_html);
				pklist_customize.updateFromCodeView();
			}
		});

	});
})( jQuery );