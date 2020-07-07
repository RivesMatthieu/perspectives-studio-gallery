<?php
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	
	
	//print_r( $gallery_images);
	
	foreach ( $gallery_images as $image_id ):
		
		$image = wp_get_attachment_image_src( $image_id );
		
		?>
        <li class="image">
            <input type="hidden" name="woo_variation_gallery[<?php echo esc_attr( $variation_id ) ?>][]" value="<?php echo $image_id ?>">
            <img src="<?php echo esc_url( $image[ 0 ] ) ?>">
            <a href="#" class="delete remove-woo-variation-gallery-image"><span class="dashicons dashicons-dismiss"></span></a>
        </li>
	
	<?php endforeach; ?>
