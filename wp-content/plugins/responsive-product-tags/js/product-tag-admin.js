jQuery(document).ready(function ($) {

    $('.remove-responsive-product-tag-image').click(function (event) {
        var optionForImage = $('.product-tag-upload-image');
        optionForImage.find('.product-tag-upload-image-src').val('');
        optionForImage.find('.product-tag-image-upload').attr('src', responsive_product_tags_product_tag_settings.blank_upload_src);
        event.preventDefault();
    });

    var file_frame;
    var element_clicked;
    $('.product-tag-upload-image-button').click(function (event) {
        event.preventDefault();
        element_clicked = $(this);
        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            var optionForImage = element_clicked.closest('.product-tag-upload-image');
            optionForImage.find('.product-tag-upload-image-id').val(attachment.id);
            optionForImage.find('.product-tag-upload-image-src').val(attachment.url);
            optionForImage.find('.product-tag-image-upload').attr('src', attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });


});