<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	// Admin Part
	add_action( 'woocommerce_save_product_variation', 'wvg_save_variation_gallery', 10, 2 );
	
	add_action( 'woocommerce_admin_process_variation_object', 'wvg_clear_default_variation_transient_by_variation' );
	add_action( 'woocommerce_delete_product_transients', 'wvg_clear_default_variation_transient_by_product' );
	
	add_action( 'woocommerce_product_after_variable_attributes', 'wvg_gallery_admin_html', 10, 3 );
	
	// Frontend Part
	add_filter( 'woocommerce_available_variation', 'wvg_available_variation_gallery', 90, 3 );
	
	add_filter( 'post_class', 'wvg_product_loop_post_class', 25, 3 );
	
	// Get Default Gallery Images
	add_action( 'wp_ajax_nopriv_wvg_get_default_gallery', 'wvg_get_default_gallery' );
	
	add_action( 'wp_ajax_wvg_get_default_gallery', 'wvg_get_default_gallery' );
	
	
	// Get Default Gallery Images
	add_action( 'wp_ajax_nopriv_wvg_get_available_variation_images', 'wvg_get_available_variation_images' );
	
	add_action( 'wp_ajax_wvg_get_available_variation_images', 'wvg_get_available_variation_images' );
	
	
	// Enfold Theme Support
	add_action( 'after_setup_theme', 'wvg_enable_theme_support', 200 );
	
	// add_action( 'init', 'wvg_remove_default_template', 200 );
	
	add_filter( 'woo_variation_product_gallery_inline_style', function ( $styles ) {
		
		$gallery_width = absint( get_option( 'woo_variation_gallery_width', apply_filters( 'woo_variation_gallery_default_width', 30 ) ) );
		
		if ( $gallery_width > 99 ) {
			$styles[ 'float' ]   = 'none';
			$styles[ 'display' ] = 'block';
		}
		
		return $styles;
	}, 9 );
	
	// Delete Default Width after switch theme
	add_action( 'after_switch_theme', function () {
		delete_option( 'woo_variation_gallery_width' );
	}, 20 );
	
	add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
		
		$defer_load_js = wc_string_to_bool( get_option( 'woo_variation_gallery_defer_js', 'yes' ) );
		
		if ( $defer_load_js ) {
			$handles = array( 'woo-variation-gallery', 'woo-variation-gallery-slider', 'woo-variation-gallery-pro' );
			
			if ( in_array( $handle, $handles ) ) {
				return str_ireplace( ' src=', ' defer src=', $tag );
			}
		}
		
		return $tag;
		
	}, 10, 3 );
	