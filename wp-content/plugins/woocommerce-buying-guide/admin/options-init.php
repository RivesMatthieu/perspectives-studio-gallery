<?php

    /**
     * For full documentation, please visit: http://docs.reduxframework.com/
     * For a more extensive sample-config file, you may look at:
     * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
     */

    if ( ! class_exists( 'Redux' ) ) {
        return;
    }

    // This is your option name where all the Redux data is stored.
    $opt_name = "woocommerce_buying_guide_options";

    /**
     * ---> SET ARGUMENTS
     * All the possible arguments for Redux.
     * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
     * */

    $args = array(
        'opt_name' => 'woocommerce_buying_guide_options',
        'use_cdn' => TRUE,
        'dev_mode' => FALSE,
        'display_name' => 'WooCommerce Buying Guide',
        'display_version' => '1.2.0',
        'page_title' => 'WooCommerce Buying Guide',
        'update_notice' => TRUE,
        'intro_text' => '',
        'footer_text' => '&copy; '.date('Y').' weLaunch',
        'admin_bar' => TRUE,
        'menu_type' => 'submenu',
        'menu_title' => 'Settings',
        'allow_sub_menu' => TRUE,
        'page_parent' => 'edit.php?post_type=buying-guide',
        'page_parent_post_type' => 'your_post_type',
        'customizer' => FALSE,
        'default_mark' => '*',
        'hints' => array(
            'icon_position' => 'right',
            'icon_color' => 'lightgray',
            'icon_size' => 'normal',
            'tip_style' => array(
                'color' => 'light',
            ),
            'tip_position' => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect' => array(
                'show' => array(
                    'duration' => '500',
                    'event' => 'mouseover',
                ),
                'hide' => array(
                    'duration' => '500',
                    'event' => 'mouseleave unfocus',
                ),
            ),
        ),
        'output' => TRUE,
        'output_tag' => TRUE,
        'settings_api' => TRUE,
        'cdn_check_time' => '1440',
        'compiler' => TRUE,
        'page_permissions' => 'manage_options',
        'save_defaults' => TRUE,
        'show_import_export' => TRUE,
        'database' => 'options',
        'transient_time' => '3600',
        'network_sites' => TRUE,
    );

    Redux::setArgs( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     * ---> START HELP TABS
     */

    $tabs = array(
        array(
            'id'      => 'help-tab',
            'title'   => __( 'Information', 'woocommerce-buying-guide' ),
            'content' => __( '<p>Need support? Please use the comment function on codecanyon.</p>', 'woocommerce-buying-guide' )
        ),
    );
    Redux::setHelpTab( $opt_name, $tabs );

    // Set the help sidebar
    // $content = __( '<p>This is the sidebar content, HTML is allowed.</p>', 'woocommerce-buying-guide' );
    // Redux::setHelpSidebar( $opt_name, $content );


    /*
     * <--- END HELP TABS
     */


    /*
     *
     * ---> START SECTIONS
     *
     */

    Redux::setSection( $opt_name, array(
        'title'  => __( 'Buying Guide', 'woocommerce-buying-guide' ),
        'id'     => 'general',
        'desc'   => __( 'Need support? Please use the comment function on codecanyon.', 'woocommerce-buying-guide' ),
        'icon'   => 'el el-home',
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'General', 'woocommerce-buying-guide' ),
        // 'desc'       => __( '', 'woocommerce-buying-guide' ),
        'id'         => 'general-settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'enable',
                'type'     => 'checkbox',
                'title'    => __( 'Enable', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Enable the Buying Guide.', 'woocommerce-buying-guide' ),
                'default'  => 1,
            ),
            array(
                'id'       => 'liveFilter',
                'type'     => 'checkbox',
                'title'    => __( 'Live Filter', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Try live filtering. This may causes some trouble with themes. If so you have to deactivate it.', 'woocommerce-buying-guide' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'filterAfterChoice',
                'type'     => 'checkbox',
                'title'    => __( 'Filter after Choice', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Filter the products directly after a choice was made.', 'woocommerce-buying-guide' ),
                'default'  => 1,
                'required' => array('liveFilter','equals','1'),
            ),
            array(
                'id'       => 'filterAdjustChoices',
                'type'     => 'checkbox',
                'title'    => __( 'Adjust Choices', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'This will hide next choices if there are no matching products.', 'woocommerce-buying-guide' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'removePagination',
                'type'     => 'checkbox',
                'title'    => __( 'Remove pagination', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Only without pagination the live filtering would work.', 'woocommerce-buying-guide' ),
                'default'  => 1,
                'required' => array('liveFilter','equals','1'),
            ),
            array(
                'id'       => 'hideFirst',
                'type'     => 'checkbox',
                'title'    => __( 'Hide Products First', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Hide all products in the beginning.', 'woocommerce-buying-guide' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'maxQuestions',
                'type'     => 'spinner',
                'title'    => __( 'Maximum Questions', 'wordpress-store-locator' ),
                'subtitle'     => __( 'Maximum Questions per Buying Guide. The lesser, the more performance!'),
                'min'      => '1',
                'step'     => '1',
                'max'      => '12',
                'default'  => '4',
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'maxChoices',
                'type'     => 'spinner',
                'title'    => __( 'Maximum Choices', 'wordpress-store-locator' ),
                'subtitle'     => __( 'Maximum Choices per Buying Guide. The lesser, the more performance!'),
                'min'      => '1',
                'step'     => '1',
                'max'      => '12',
                'default'  => '4',
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'showOnParent',
                'type'     => 'checkbox',
                'title'    => __( 'Show in parent Cats', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Show Buying Guides automatically in the parent categories.', 'woocommerce-buying-guide' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'               => 'successText',
                'type'             => 'editor',
                'title'            => __('Success Text', 'redux-framework-demo'), 
                'subtitle'         => __('Success Message when products were found.', 'redux-framework-demo'),
                'default'          => '<h3>Our Buying Guide was successful.</h3> Below products matches your choices!',
                'args'   => array(
                    'teeny'            => true,
                    'textarea_rows'    => 3
                ),
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'               => 'errorText',
                'type'             => 'editor',
                'title'            => __('Error Text', 'redux-framework-demo'), 
                'subtitle'         => __('Error Message when no products were found.', 'redux-framework-demo'),
                'default'          => 'No products were found matching your choices!',
                'args'   => array(
                    'teeny'            => true,
                    'textarea_rows'    => 3
                ),
                'required' => array('enable','equals','1'),
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Styles', 'woocommerce-buying-guide' ),
        // 'desc'       => __( '', 'woocommerce-buying-guide' ),
        'id'         => 'style-settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'     =>'accentBackgroundColor',
                'type'  => 'color',
                'title' => __('Accent Background color', 'woocommerce-buying-guide'), 
                'validate' => 'color',
                'default' => '#d35400',
            ),
            array(
                'id'     =>'accentTextColor',
                'type'  => 'color',
                'title' => __('Accent Text color', 'woocommerce-buying-guide'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Advanced settings', 'woocommerce-buying-guide' ),
        'desc'       => __( 'Custom stylesheet / javascript.', 'woocommerce-buying-guide' ),
        'id'         => 'advanced',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'customCSS',
                'type'     => 'ace_editor',
                'mode'     => 'css',
                'title'    => __( 'Custom CSS', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'Add some stylesheet if you want.', 'woocommerce-buying-guide' ),
            ),
            array(
                'id'       => 'doNotLoadBootstrap',
                'type'     => 'checkbox',
                'title'    => __( 'Don\'t load Bootstrap', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'This will deactivate the load of bootstrap.js. Used for some themes that are using it to avoid conflicts.', 'woocommerce-buying-guide' ),
                'default'  => 0,
            ),
            array(
                'id'       => 'doNotLoadFontAwesome',
                'type'     => 'checkbox',
                'title'    => __( 'Don\'t load Font Awesome', 'woocommerce-buying-guide' ),
                'subtitle' => __( 'This will deactivate the load of font awesome icons. Used for some themes that are using it to avoid conflicts.', 'woocommerce-buying-guide' ),
                'default'  => 0,
            ),
        )
    ));


    /*
     * <--- END SECTIONS
     */
