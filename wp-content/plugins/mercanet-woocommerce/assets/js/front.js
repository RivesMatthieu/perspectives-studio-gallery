
function check_option(id, card, type) {    
    jQuery("#payment_option_" + type).val(id);
    jQuery("#payment_mean_brand_" + type).val(card);
}

function check_option_sdd(id, type) {    
    jQuery("#payment_mean_brand_" + type).val(id);
}

jQuery(document).ready(function () {        
    jQuery('#stop_recurring_button').click(function (e) {
        e.preventDefault();
        jQuery('.stop_recurring_confirmation, .mercanet-overlay').show();
    });

    jQuery('#confirm_stop_recurring').click(function (e) {
        jQuery('#mercanet_stop_recurring_form').submit();
    });
    jQuery('#noconfirm_stop_recurring').click(function (e) {
        jQuery('.stop_recurring_confirmation, .mercanet-overlay').hide();
    });

    jQuery(document).mouseup(function (e) {
        var container = jQuery(".stop_recurring_confirmation");
        if (!container.is(e.target) && container.has(e.target).length === 0){
            jQuery('#noconfirm_stop_recurring').click();
        }
    });
});

