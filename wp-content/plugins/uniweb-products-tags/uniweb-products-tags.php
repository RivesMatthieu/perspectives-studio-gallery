<?php 
/**
 * Plugin Name: Products tags
 * Author: Uniweb
 * Version: 1.0
 * Requires PHP: 7.2
 * Description: Affiche des images sur les produits par rapport aux étiquettes définis
 */

function shortcode_tags(){
    global $product;
    $productId = $product->id;
    $product_tags = get_the_term_list($productId, 'product_tag', '', ',' );
    return  $product_tags;
};
add_shortcode('tags-custom', 'shortcode_tags');
