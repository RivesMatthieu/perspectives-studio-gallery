<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Responsive_Product_Tags_Settings {

    /**
     * Removes characters from array or string
     */
    function kses_array( $string ) {
        if ( is_string( $string ) ) {
            $string = wp_kses_data( $string );
        }
        if ( is_array( $string ) ) {
            if ( !empty( $string ) ) {
                foreach ( $string as $id => $value ) {
                    $string[ $id ] = $this->kses_array( $value );
                }
            }
        }
        return $string;
    }

    /**
     * Settings page
     */
    function settings_page() {
        $responsive_product_tags_settings = get_option( 'responsive_product_tags_settings', array() );
        if ( !empty( $_POST[ 'responsive_product_tags_settings' ] ) ) {
            $responsive_product_tags_settings = $this->kses_array( $_POST[ 'responsive_product_tags_settings' ] );
            update_option( 'responsive_product_tags_settings', $responsive_product_tags_settings );
        }
        print '<h1>';
        _e( 'Responsive Product Image Tag Settings', 'responsive-product-tags' );
        print '</h1>';
        print '<form action="' . admin_url( 'admin.php?page=responsive-product-tags-settings' ) . '" method="post">';
        $checked = '';
        $display_on_shop_page = value( $responsive_product_tags_settings, 'display_on_shop_page' );
        if ( !empty( $display_on_shop_page ) ) {
            $checked = ' checked="checked" ';
        }
        print '<label><input type="checkbox" name="responsive_product_tags_settings[display_on_shop_page]" value="yes" ' . $checked . '/> ' .
                __( 'Display tag thumbnails on the search/category/shop pages?', 'responsive-product-tags' ) . '</label><br>';

        $checked = '';
        $border = value( $responsive_product_tags_settings, 'border' );
        if ( !empty( $border ) ) {
            $checked = ' checked="checked" ';
        }
        print '<label><input type="checkbox" name="responsive_product_tags_settings[border]" value="yes" ' . $checked . '/> ' .
                __( 'Display a border around the tags?', 'responsive-product-tags' ) . '</label><br>';
        $checked = '';
        $link_tag = value( $responsive_product_tags_settings, 'link_tag' );
        if ( !empty( $link_tag ) ) {
            $checked = ' checked="checked" ';
        }
        print '<label><input type="checkbox" name="responsive_product_tags_settings[link_tag]" ' . $checked . ' /> ' .
                __( 'Link tag to all products with the same tag?', 'responsive-product-tags' ) . '</label><br>';

        $tooltip = '';
        $right = '';
        $over = '';
        $below = '';
        $no_display = '';
        $name_display = value( $responsive_product_tags_settings, 'name_display', 'right' );
        if ( $name_display == 'tooltip' ) {
            $tooltip = 'checked="checked"';
        } elseif ( $name_display == 'right' ) {
            $right = 'checked="checked"';
        } elseif ( $name_display == 'over' ) {
            $over = 'checked="checked"';
        } elseif ( $name_display == 'below' ) {
            $below = 'checked="checked"';
        } elseif ( $name_display == 'no_display' ) {
            $no_display = 'checked="checked"';
        }
        print '<p>' . __( 'How would you like to display the tag name?', 'responsive-product-tags' ) . '</p>'
                . '<label><input ' . $tooltip . ' type="radio" name="responsive_product_tags_settings[name_display]" value="tooltip" />' . __( 'As a tooltip', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $right . ' type="radio" name="responsive_product_tags_settings[name_display]" value="right" />' . __( 'To the right of the image', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $over . ' type="radio" name="responsive_product_tags_settings[name_display]" value="over" />' . __( 'Over the image', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $below . ' type="radio" name="responsive_product_tags_settings[name_display]" value="below" />' . __( 'Below the image', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $no_display . ' type="radio" name="responsive_product_tags_settings[name_display]" value="no_display" />' . __( 'Do not display', 'responsive-product-tags' ) . ' </label>';

        $description_display = value( $responsive_product_tags_settings, 'description_display', 'no_display' );
        if ( $description_display == 'tooltip' ) {
            $tooltip = 'checked="checked"';
        } elseif ( $description_display == 'right' ) {
            $right = 'checked="checked"';
        } elseif ( $description_display == 'over' ) {
            $over = 'checked="checked"';
        } elseif ( $description_display == 'below' ) {
            $below = 'checked="checked"';
        } else/* ( $description_display == 'no_display' ) */ {
            $no_display = 'checked="checked"';
        }
        print '<p>' . __( 'How would you like to display the tag description?', 'responsive-product-tags' ) . '</p>'
                . '<label><input ' . $tooltip . ' type="radio" name="responsive_product_tags_settings[description_display]" value="tooltip" />' . __( 'As a tooltip', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $below . ' type="radio" name="responsive_product_tags_settings[description_display]" value="below" />' . __( 'Below the image', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $no_display . ' type="radio" name="responsive_product_tags_settings[description_display]" value="no_display" />' . __( 'Do not display', 'responsive-product-tags' ) . ' </label>';


        $img_size = value( $responsive_product_tags_settings, 'img_size', 'thumbnail' );
        $thumbnail = '';
        $medium = '';
        $large = '';
        $full = '';
        if ( $img_size == 'thumbnail' ) {
            $thumbnail = 'checked="checked"';
        } elseif ( $img_size == 'medium' ) {
            $medium = 'checked="checked"';
        } elseif ( $img_size == 'large' ) {
            $large = 'checked="checked"';
        } elseif ( $img_size == 'full' ) {
            $full = 'checked="checked"';
        }
        print '<p>' . __( 'What size would you like the thumbnails to be?', 'responsive-product-tags' ) . '</p>'
                . '<label><input ' . $thumbnail . ' type="radio" name="responsive_product_tags_settings[img_size]" value="thumbnail" />' . __( 'Thumbnail', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $medium . ' type="radio" name="responsive_product_tags_settings[img_size]" value="medium" />' . __( 'Medium', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $large . ' type="radio" name="responsive_product_tags_settings[img_size]" value="large" />' . __( 'Large', 'responsive-product-tags' ) . ' </label>'
                . '<label><input ' . $full . ' type="radio" name="responsive_product_tags_settings[img_size]" value="full" />' . __( 'Full', 'responsive-product-tags' ) . ' </label>';

        $responsive_product_tags_position = value( $responsive_product_tags_settings, 'responsive_product_tags_position', 'normal' );
        wpshowcase_radioboxes( array( 'normal' => __( 'Below the product title', 'responsive-product-tags' ),
            'right' => __( 'Floating to the right of the price', 'responsive-product-tags' ) )
                , $responsive_product_tags_position, __( 'Where would you like the tags to appear?', 'responsive-product-tags' ), 'responsive_product_tags_settings[responsive_product_tags_position]' );



        print '<br><br><input type="submit" value="' . __( 'Save Settings', 'responsive-product-tags' ) . '" />';
        print '</form>';
    }

}

$responsive_product_tags_settings = new Responsive_Product_Tags_Settings();
