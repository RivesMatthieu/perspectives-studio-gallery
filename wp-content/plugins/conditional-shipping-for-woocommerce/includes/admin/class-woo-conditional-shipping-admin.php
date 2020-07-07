<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Shipping_Admin {
  /**
   * Constructor
   */
  public function __construct() {
    add_filter( 'woocommerce_get_sections_shipping', array( $this, 'register_section' ), 10, 1 );

		add_action( 'woocommerce_settings_shipping', array( $this, 'output' ) );
		
		add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_ruleset' ), 10, 0 );

    // Add admin JS
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    
    // Add link to conditions to the plugins page
    add_filter( 'plugin_action_links_' . WOO_CONDITIONAL_SHIPPING_BASENAME, array( $this, 'add_conditions_link' ) );

    // Legacy: show message about moved settings
    if ( get_option( 'wcs_conditions', false ) ) {
			add_filter( 'woocommerce_shipping_zone_shipping_methods', array( $this, 'add_fields_modal'), 10, 4 );
			add_filter( 'woocommerce_settings_shipping', array( $this, 'add_fields'), 20, 0 );
		}

		// Hide default settings from conditions settings
		// WooCommerce 3.6.2 at least has a bug which causes default shipping options to be output
		// without standard section
		add_filter( 'woocommerce_get_settings_shipping', array( $this, 'hide_default_settings' ), 100, 2 );
	}
	
  /**
   * Add conditions link to the plugins page.
   */
  public function add_conditions_link( $links ) {
    $url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping' );
    $link = '<a href="' . $url . '">' . __( 'Conditions', 'woo-conditional-shipping' ) . '</a>';

    return array_merge( array( $link ), $links );
  }

  /**
	 * Add admin JS
	 */
	public function admin_enqueue_scripts() {
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    
    wp_enqueue_script( 'woo_conditional_shipping_admin_js', plugin_dir_url( __FILE__ ) . '../../admin/js/woo-conditional-shipping.js', array( 'jquery', 'wp-util' ), WOO_CONDITIONAL_SHIPPING_ASSETS_VERSION );
    
		wp_enqueue_style( 'woo_conditional_shipping_admin_css', plugin_dir_url( __FILE__ ) . '../../admin/css/woo-conditional-shipping.css', array(), WOO_CONDITIONAL_SHIPPING_ASSETS_VERSION );
  }
  
  /**
   * Register section under "Shipping" settings in WooCommerce
   */
  public function register_section( $sections ) {
    $sections['woo_conditional_shipping'] = __( 'Conditions', 'woo-conditional-shipping' );

    return $sections;
	}
	
  /**
   * Output conditions page
   */
  public function output() {
    global $current_section;
    global $hide_save_button;

    if ( 'woo_conditional_shipping' === $current_section ) {
			if ( isset( $_REQUEST['ruleset_id'] ) ) {
        $hide_save_button = true;

        if ( $_REQUEST['ruleset_id'] === 'new' ) {
          $ruleset_id = false;
        } else {
          $ruleset_id = wc_clean( wp_unslash( $_REQUEST['ruleset_id'] ) );
        }

        if ( $ruleset_id && isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) {
          wp_delete_post( $ruleset_id, false );

          $url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping' );
          wp_safe_redirect( $url );
          exit;
        }

        $ruleset = new Woo_Conditional_Shipping_Ruleset( $ruleset_id );

        include 'views/ruleset.html.php';
      } else {
        $hide_save_button = true;

        $rulesets = woo_conditional_shipping_get_rulesets();
        
        include 'views/settings.html.php';
      }
    }
  }

	/**
	 * Save ruleset
	 */
	public function save_ruleset() {
		global $current_section;
    
    if ( 'woo_conditional_shipping' === $current_section && isset( $_POST['ruleset_id'] ) ) {
      $post = false;
      if ( $_POST['ruleset_id'] ) {
        $post = get_post( $_POST['ruleset_id'] );

        if ( ! $post && 'wcs_ruleset' !== get_post_type( $post ) ) {
          $post = false;
        }
      }

      if ( ! $post ) {
        $post_id = wp_insert_post( array(
          'post_type' => 'wcs_ruleset',
          'post_title' => wp_strip_all_tags( $_POST['ruleset_name'] ),
          'post_status' => 'publish',
        ) );

        $post = get_post( $post_id );
      } else {
        $post->post_title = wp_strip_all_tags( $_POST['ruleset_name'] );

        wp_update_post( $post, false );
      }

      $conditions = isset( $_POST['wcs_conditions'] ) ? $_POST['wcs_conditions'] : array();
      update_post_meta( $post->ID, '_wcs_conditions', array_values( (array) $conditions ) );

      $actions = isset( $_POST['wcs_actions'] ) ? $_POST['wcs_actions'] : array();
			update_post_meta( $post->ID, '_wcs_actions', array_values( (array) $actions ) );
			
			$enabled = ( isset( $_POST['ruleset_enabled'] ) && $_POST['ruleset_enabled'] ) ? 'yes' : 'no';
			update_post_meta( $post->ID, '_wcs_enabled', $enabled );

      // Increments the transient version to invalidate cache.
		  WC_Cache_Helper::get_transient_version( 'shipping', true );

      $url = add_query_arg( array(
        'ruleset_id' => $post->ID,
      ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping' ) );
      wp_safe_redirect( $url );
      exit;
    }
	}

	/**
	 * Hide default settings from condition settings
	 */
	public function hide_default_settings( $settings, $section ) {
		if ( $section === 'woo_conditional_shipping' ) {
			return array();
		}

		return $settings;
	}

	/**
	 * Add fields to a shipping method settings in a modal
	 * 
	 * @legacy
	 */
	public function add_fields_modal( $methods, $raw_methods, $allowed_classes, $wc_shipping_zone ) {
		foreach ( $methods as $instance_id => $method ) {
			if ( $method->has_settings ) {
				// Do not add settings to the modal if there are no other settings. Plugins
				// like USPS only show settings in a separate window.
				if ( ! empty ( $method->settings_html ) ) {
					$methods[$instance_id]->settings_html .= $this->generate_settings_html( $method );
				}
			}
		}

		return $methods;
	}

	/**
	 * Add fields to a shipping method settings in a separate page
	 * 
	 * @legacy
	 */
	public function add_fields() {
		if ( isset( $_REQUEST['instance_id'] ) && ! empty( $_REQUEST['instance_id'] ) ) {
			$instance_id = absint( $_REQUEST['instance_id'] );
			$zone = WC_Shipping_Zones::get_zone_by( 'instance_id', $instance_id );
			$shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );

			if ( ! $shipping_method || ! $zone || ! $shipping_method->has_settings() ) {
				return;
			}

			echo $this->generate_settings_html( $shipping_method );
		}
	}

	/**
	 * Generate settings HTML for conditions
	 * 
	 * @legacy
	 */
	public function generate_settings_html( $method ) {
		$output = '';

		$output .= '<h3 class="wc-settings-sub-title">' . wp_kses_post( __( 'Conditions', 'woo-conditional-shipping' ) ) . '</h3>';

		$url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping' );
		$output .= '<p>' . sprintf( __( 'As of version 2.0.0 conditions are moved to <a href="%s">separate settings page</a>.', 'woo-conditional-shipping' ), $url ) . '</p>';

		return $output;
	}
}
