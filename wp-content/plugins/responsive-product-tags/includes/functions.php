<?php

if ( !function_exists( 'value' ) ) {

    /**
     * WPShowCase's function for getting data from arrays
     */
    function value( $array, $key, $default = '', $ignore_empty_string = false ) {
        if ( isset( $array[ $key ] ) ) {
            if ( !$ignore_empty_string || $array[ $key ] != '' ) {
                return $array[ $key ];
            }
        }
        return $default;
    }

}

if ( !function_exists( 'wpshowcase_checkboxes' ) ) {

    /**
     * WPShowCase's function for displaying checkboxes
     */
    function wpshowcase_checkboxes( $options, $values, $label, $name_base ) {
        if ( !empty( $options ) ) {
            print '<p>' . $label . '</p>';
            foreach ( $options as $name => $option ) {
                print '<label><input type="checkbox" value="yes" ';
                if ( !empty( $values[ $name ] ) ) {
                    print 'checked="checked" ';
                }
                print 'name="' . $name_base . '[' . $name . ']" />' . $option . '</label><br>';
            }
        }
    }

}


if ( !function_exists( 'wpshowcase_radioboxes' ) ) {

    /**
     * WPShowCase's function for displaying radio boxes
     */
    function wpshowcase_radioboxes( $options, $value, $label, $name ) {
        if ( !empty( $options ) ) {
            print '<p>' . $label . '</p>';
            foreach ( $options as $id => $option ) {
                print '<label><input type="radio" value="' . $id . '" ';
                if ( $id == $value ) {
                    print 'checked="checked" ';
                }
                print 'name="' . $name . '" />' . $option . '</label><br>';
            }
        }
    }

}
?>