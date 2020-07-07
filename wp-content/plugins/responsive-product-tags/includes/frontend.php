<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Responsive_Product_Tags_Frontend {

    /**
     * Constructor - adds actions and filters
     */
    function __construct() {
        //Display at top of summary
        $responsive_product_tags_settings = get_option( 'responsive_product_tags_settings', array() );
        $responsive_product_tags_position = value( $responsive_product_tags_settings, 'responsive_product_tags_position', 'normal' );
        if ( $responsive_product_tags_position == 'right' ) {
            add_action( 'woocommerce_before_single_product_summary', array( $this, 'display_tags' ), 100 );
        } else {
            add_action( 'woocommerce_single_product_summary', array( $this, 'display_tags' ), 7 );
        }
        $display_on_shop_page = value( $responsive_product_tags_settings, 'display_on_shop_page' );
        if ( !empty( $display_on_shop_page ) ) {
            add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_tags_shop_page' ), 10 );
        }
    }

    /**
     * The tags html
     */
    function term_links( $responsive_product_tags_settings ) {
        global $post;
        $terms = get_the_terms( $post->ID, 'product_tag' );
        $link_tag = value( $responsive_product_tags_settings, 'link_tag' );
        $name_display = value( $responsive_product_tags_settings, 'name_display', 'right' );
        $description_display = value( $responsive_product_tags_settings, 'description_display', 'tooltip' );
        $img_size = value( $responsive_product_tags_settings, 'img_size', 'thumbnail' );
        if ( !empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $srcs = get_option( 'product_tag_data', array() );
                $data = value( $srcs, $term->name, '' );
                $id = value( $data, 'id' );
                $src = wp_get_attachment_image_src( $id, $img_size );
                $img = '';
                if ( !empty( $src ) ) {
                    $img = '<div class="responsive-product-tag-img-wrapper"><img class="responsive-product-tags-img-' . $img_size . '" src="' . $src[ 0 ] . '" alt="' . $term->name . '" />';
                    if ( $name_display == 'over' ) {
                        $img.= '<span class="responsive-product-tag-name responsive-product-tag-' . $name_display . '">' . $term->name . '</span>';
                    }
                    if ( $description_display == 'over' ) {
                        $img.= '<span class="responsive-product-tag-description responsive-product-tag-' . $description_display . '">' . $term->description . '</span>';
                    }
                    $img.='</div>';
                }
                $link_class = 'responsive-product-tag responsive-product-tags-link';
                if ( empty( $link_tag ) ) {
                    $link_class = 'responsive-product-tag responsive-product-tags-no-link';
                }
                $tooltip = '';
                if ( $name_display == 'tooltip' && $description_display == 'tooltip' ) {
                    $tooltip = ' title="' . $term->name . ' ' . __( ':', 'responsive-product-tags' ) . ' ' . $term->description . '" ';
                } elseif ( $name_display == 'tooltip' ) {
                    $tooltip = ' title="' . $term->name . '" ';
                } elseif ( $description_display == 'tooltip' ) {
                    $tooltip = ' title="' . $term->description . '" ';
                }
                $href = '#';
                if ( !empty( $link_tag ) ) {
                    $href = get_term_link( $term, 'product_tag' );
                }
                if( empty($link_tag ) ) {
                    print '<span href="' . $href . '" class="' . $link_class . '" ' . $tooltip . ' >';
                } else {
                    print '<a href="' . $href . '" class="' . $link_class . '" ' . $tooltip . ' >';                    
                }
                print $img;
                if ( $name_display == 'right' || $name_display == 'below' ) {
                    print '<span class="responsive-product-tag-name responsive-product-tag-' . $name_display . '">' . $term->name . '</span>';
                }
                if ( $description_display == 'right' || $description_display == 'below' ) {
                    print '<span class="responsive-product-tag-description responsive-product-tag-' . $description_display . '">' . $term->description . '</span>';
                }
                if( empty( $link_tag ) ) {
                    print '</span><br>';                    
                } else {
                    print '</a><br>';                    
                }
            }
        }
    }

    function display_tags_shop_page() {
        global $post;
        $tag_count = sizeof( get_the_terms( $post->ID, 'product_tag' ) );
        if ( empty( $tag_count ) ) {
            return;
        }
        $responsive_product_tags_settings = get_option( 'responsive_product_tags_settings', array() );
        $description_display = value( $responsive_product_tags_settings, 'description_display', 'tooltip' );
        $tooltip = '';
        if ( !empty( $description_display ) ) {
            $tooltip = 'responsive-product-tags-tooltip';
        }
        $border = value( $responsive_product_tags_settings, 'border' );
        $responsive_product_tags_border = '';
        if ( !empty( $border ) ) {
            $responsive_product_tags_border = 'responsive-product-tags-border';
        }
        $responsive_product_tags_position = value( $responsive_product_tags_settings, 'responsive_product_tags_position', 'normal' );
        print '<div class="responsive-product-tags-shop responsive-product-tags responsive-product-tags-' . $responsive_product_tags_position . ' ' . $tooltip . ' ' . $responsive_product_tags_border . '">';
        $responsive_product_tags_settings[ 'img_size' ] = 'thumbnail';
        $responsive_product_tags_settings[ 'name_display' ] = 'tooltip';
        $responsive_product_tags_settings[ 'description_display' ] = 'no_display';
        $this->term_links( $responsive_product_tags_settings );
        print '</div>';
        $this->enqueue_scripts();
    }

    /**
     * The tags and the html around them
     */
    function display_tags() {
        global $post;
        $tag_count = sizeof( get_the_terms( $post->ID, 'product_tag' ) );
        if ( empty( $tag_count ) ) {
            return;
        }
        $responsive_product_tags_settings = get_option( 'responsive_product_tags_settings', array() );
        $description_display = value( $responsive_product_tags_settings, 'description_display', 'tooltip' );
        $tooltip = '';
        if ( !empty( $description_display ) ) {
            $tooltip = 'responsive-product-tags-tooltip';
        }
        $border = value( $responsive_product_tags_settings, 'border' );
        $responsive_product_tags_border = '';
        if ( !empty( $border ) ) {
            $responsive_product_tags_border = 'responsive-product-tags-border';
        }
        $responsive_product_tags_position = value( $responsive_product_tags_settings, 'responsive_product_tags_position', 'normal' );
        print '<div class="responsive-product-tags responsive-product-tags-' . $responsive_product_tags_position . ' ' . $tooltip . ' ' . $responsive_product_tags_border . '">';
        $this->term_links( $responsive_product_tags_settings );
        print '</div>';
        $this->enqueue_scripts();
    }

    /**
     * CSS and JS
     */
    function enqueue_scripts() {
        //CSS
        wp_enqueue_style( 'responsive-product-tags-product-frontend-css', WP_PLUGIN_URL . '/responsive-product-tags/css/product-frontend.css' );
        //JS
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'jquery-ui-button' );
        wp_enqueue_script( 'jquery-ui-spinner' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'responsive-product-tags-product-frontend', WP_PLUGIN_URL . '/responsive-product-tags/js/product-frontend.js', array( 'jquery', 'jquery-ui-tooltip' ), false, true );
    }

}

$responsive_product_tags_frontend = new Responsive_Product_Tags_Frontend();
