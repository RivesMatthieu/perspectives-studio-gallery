<?php
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
?>
<style type="text/css">
    :root {
        --wvg-thumbnail-item-gap : <?php echo $gallery_thumbnails_gap ?>px;
        --wvg-single-image-size  : <?php echo $single_image_width ?>px;
        --wvg-gallery-width      : <?php echo $gallery_width ?>%;
        --wvg-gallery-margin     : <?php echo $gallery_margin ?>px;
        }

    /* Default Width */
    .woo-variation-product-gallery {
        max-width : <?php echo $gallery_width ?>% !important;
        }

    /* Medium Devices, Desktops */
    <?php if( $gallery_medium_device_width > 0 ): ?>
    @media only screen and (max-width : 992px) {
        .woo-variation-product-gallery {
            width     : <?php echo $gallery_medium_device_width ?>px;
            max-width : 100% !important;
            }
        }

    <?php endif; ?>

    /* Small Devices, Tablets */
    <?php if( $gallery_small_device_width > 0 ): ?>
    @media only screen and (max-width : 768px) {
        .woo-variation-product-gallery {
            width     : <?php echo $gallery_small_device_width ?>px;
            max-width : 100% !important;
            }
        }

    <?php endif; ?>

    /* Extra Small Devices, Phones */
    <?php if( $gallery_extra_small_device_width > 0 ): ?>
    @media only screen and (max-width : 480px) {
        .woo-variation-product-gallery {
            width     : <?php echo $gallery_extra_small_device_width ?>px;
            max-width : 100% !important;
            }
        }
    <?php endif; ?>
</style>
