<?php
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
?>

<script type="text/html" id="tmpl-woo-variation-gallery-image">
    <li class="image">
        <input type="hidden" name="woo_variation_gallery[{{data.product_variation_id}}][]" value="{{data.id}}">
        <img src="{{data.url}}">
        <a href="#" class="delete remove-woo-variation-gallery-image"><span class="dashicons dashicons-dismiss"></span></a>
    </li>
</script>