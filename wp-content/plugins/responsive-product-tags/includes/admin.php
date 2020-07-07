<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Responsive_Product_Tags_Admin {

    /**
     * Constructor - adds actions and filters
     */
    function __construct() {
        //Add the image upload to the product tag edit/create page
        add_action( 'created_term', array( $this, 'product_tag_save' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'product_tag_save' ), 10, 3 );
        add_action( 'product_tag_edit_form_fields', array( $this, 'product_tag_edit_form_fields' ), 10, 2 );
        add_action( 'product_tag_add_form_fields', array( $this, 'product_tag_add_form_fields' ), 1 );
        // Add columns
        add_filter( 'manage_edit-product_tag_columns', array( $this, 'product_tag_columns' ) );
        add_filter( 'manage_product_tag_custom_column', array( $this, 'product_tag_column' ), 10, 3 );
    }

    /**
     * Thumbnail column
     */
    public function product_tag_columns( $columns ) {
        $new_columns = array();
        $new_columns[ 'cb' ] = value( $columns, 'cb' );
        $new_columns[ 'thumb' ] = __( 'Image', 'responsive-product-tags' );
        unset( $columns[ 'cb' ] );
        return array_merge( $new_columns, $columns );
    }

    /**
     * Thumbnail column value
     */
    public function product_tag_column( $columns, $column, $id ) {
        if ( $column == 'thumb' ) {
            $term = get_term( $id, 'product_tag' );
            $name = $term->name;
            $product_tag_data = get_option( 'product_tag_data', array() );
            $data = value( $product_tag_data, $name, array() );
            $id = value( $data, 'id' );
            $image = wc_placeholder_img_src();
            if ( !empty( $id ) ) {
                $image = wp_get_attachment_thumb_url( $id );
            }
            $image = str_replace( ' ', '%20', $image );
            $columns .= '<img src="' . esc_url( $image ) . '" alt="' . __( 'Thumbnail', 'woocommerce' ) . '" class="wp-post-image" height="48" width="48" />';
        }
        return $columns;
    }

    /**
     * Add another field to the edit term form
     */
    function product_tag_edit_form_fields( $tag, $taxonomy ) {
        $this->add_upload_image_field( $tag->name );
    }

    /**
     * Add another field to the add term form
     */
    function product_tag_add_form_fields( $taxonomy ) {
        $this->add_upload_image_field( 'new' );
    }

    /**
     * The upload image field
     */
    function add_upload_image_field( $term ) {
        $product_tag_data = get_option( 'product_tag_data', array() );
        $data = value( $product_tag_data, $term, array() );
        $id = value( $data, 'id' );
        $src = value( $data, 'src' );
        print '<tr class="upload-product-tag-image"><td>' . __( 'Thumbnail', 'responsive-product-tags' ) . '</td><td>';
        $upload_image = __( 'Choose image', 'responsive-product-tags' );
        print '<div class="product-tag-upload-image">';
        print '<a href="#" class="product-tag-upload-image-button">
		<span class="product-tag-upload-image-container">';
        $src_of_img = $src;
        if ( empty( $src_of_img ) ) {
            $src_of_img = WP_PLUGIN_URL . '/responsive-product-tags/images/blank-upload.png';
        }
        print '<img class="product-tag-image-upload" src="' . $src_of_img . '" /><br>';
        if ( $src != '' ) {
            $upload_image = __( 'Choose a different image', 'responsive-product-tags' );
        }
        print '</span>';
        print $upload_image . '</a>
		<input class="product-tag-upload-image-src" type="hidden" name="product_tag_image_src" value="' . $src . '">
	   <input class="product-tag-upload-image-id" type="hidden" name="product_tag_image_id" value="' . $id . '">
	    </div>';
        print '</td>';
        print '<td><a href="#" class="remove-responsive-product-tag-image button">' . __( 'Remove image', 'responsive-product-tags' ) . '</a></td></tr>';
        print '<br><br>';
        $this->enqueue_scripts();
    }

    /**
     * Saves the image when a term is saved
     */
    function product_tag_save( $term_id, $tt_id, $taxonomy ) {
        $srcs = get_option( 'product_tag_data', array() );
        $tag = get_term( $term_id, $taxonomy );
        $name = $tag->name;
        $srcs[ $name ] = array();
        if ( !empty( $_POST[ 'product_tag_image_src' ] ) ) {
            $srcs[ $name ][ 'src' ] = $_POST[ 'product_tag_image_src' ];
            $srcs[ $name ][ 'id' ] = $_POST[ 'product_tag_image_id' ];
        }
        update_option( 'product_tag_data', $srcs );
    }

    /*
     * js and css
     */

    function enqueue_scripts() {
        wp_enqueue_script( 'jquery' );
        if ( function_exists( 'wp_enqueue_media' ) && !did_action( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }
        wp_enqueue_script( 'jquery-ui-core' );
        wp_register_style( 'responsive-product-tags-product-tag-admin', WP_PLUGIN_URL . '/responsive-product-tags/css/product-tag-admin.css' );
        wp_enqueue_style( 'responsive-product-tags-product-tag-admin' );
        wp_enqueue_script( 'responsive-product-tags-product-tag-admin', WP_PLUGIN_URL . '/responsive-product-tags/js/product-tag-admin.js', array( 'jquery' ), false, true );
        wp_localize_script( 'responsive-product-tags-product-tag-admin', 'responsive_product_tags_product_tag_settings', array( 'blank_upload_src' => WP_PLUGIN_URL . '/responsive-product-tags/images/blank-upload.png' ) );
    }

}

$responsive_product_tags_admin = new Responsive_Product_Tags_Admin();
