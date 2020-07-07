jQuery(document).ready(function ($) {

    $('.responsive-product-tag, .responsive-product-tags').each(function() {
        if($(this).html()=='') {
            $(this).hide(0);
            if($(this).next().is('br')) {
                $(this).next().hide(0);
            }
        }
    });

    $('.responsive-product-tags-shop').each(function () {
        var marginTop = $(this).closest('.product').find('img.wp-post-image').css('margin-top');
        $(this).css('margin-top', marginTop);
        var marginLeft = $(this).closest('.product').find('img.wp-post-image').css('margin-left');
        $(this).css('margin-left', marginLeft);        
    });

    $('.responsive-product-tag-over').each(function () {
        $(this).append('<span class="responsive-product-tag-over-inner"></span>');
    });

    $('.responsive-product-tags-no-link').click(function (event) {
        event.preventDefault();
    });

    var color = $('.product_title').css('color');
    $('a.responsive-product-tag').css('color', color);

    if ($('.responsive-product-tags-right').length > 0) {
        var width = $('.responsive-product-tags-right').width();
        var summaryWidth = $('.summary').width();
        summaryWidth = summaryWidth - width;
        $('.summary').width(summaryWidth);
    }

});