<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	/**
	 * Settings for API.
	 */
	if ( class_exists( 'Woo_Variation_Gallery_Settings' ) ) {
		return new Woo_Variation_Gallery_Settings();
	}
	
	class Woo_Variation_Gallery_Settings extends WC_Settings_Page {
		
		public function __construct() {
			$this->id    = 'woo-variation-gallery';
			$this->label = esc_html__( 'WooCommerce Additional Variation Images', 'woo-variation-gallery' );
			parent::__construct();
		}
		
		public function get_sections() {
			
			$sections = array(
				''          => esc_html__( 'General', 'woo-variation-gallery' ),
				'advanced'  => esc_html__( 'Advanced', 'woo-variation-gallery' ),
				'tutorials' => esc_html__( 'Tutorials', 'woo-variation-gallery' )
			);
			
			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
		}
		
		public function output() {
			
			global $current_section, $hide_save_button;
			
			if ( $current_section === 'tutorials' ) {
				$hide_save_button = true;
				$this->tutorial_section();
			} else {
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::output_fields( $settings );
			}
		}
		
		public function tutorial_section() {
			ob_start();
			include_once 'tutorials.php';
			echo ob_get_clean();
		}
		
		public function get_settings( $current_section = '' ) {
			
			$settings = array();
			
			switch ( $current_section ):
				
				case 'advanced':
					$settings = apply_filters( 'woo_variation_gallery_advanced_settings', array(
						
						array(
							'name' => __( 'Advanced Options', 'woo-variation-gallery' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'woo_variation_gallery_advanced_options',
						),
						
						// Reset Variation Gallery
						array(
							'title'   => esc_html__( 'Reset Variation Gallery', 'woo-variation-gallery' ),
							'type'    => 'checkbox',
							'default' => 'no',
							'desc'    => esc_html__( 'Always Reset Gallery After Variation Select', 'woo-variation-gallery' ),
							'id'      => 'woo_variation_gallery_reset_on_variation_change'
						),
						
						// Gallery Image Preload
						array(
							'title'   => esc_html__( 'Gallery Image Preload', 'woo-variation-gallery' ),
							'type'    => 'checkbox',
							'default' => 'yes',
							'desc'    => esc_html__( 'Variation Gallery Image Preload', 'woo-variation-gallery' ),
							'id'      => 'woo_variation_gallery_image_preload'
						),
						// Defer JS Load
						array(
							'title'   => esc_html__( 'Load Defer Gallery JS', 'woo-variation-gallery' ),
							'type'    => 'checkbox',
							'default' => 'yes',
							'desc'    => esc_html__( 'Variation Gallery JS Loading', 'woo-variation-gallery' ),
							'id'      => 'woo_variation_gallery_defer_js'
						),
						
						array(
							'type' => 'sectionend',
							'id'   => 'woo_variation_gallery_advanced_options'
						),
					
					) );
					break;
				
				default:
					$settings = apply_filters( 'woo_variation_gallery_default_settings', array(
						
						// Thumbnails Section Start
						array(
							'name' => esc_html__( 'Thumbnail Options', 'woo-variation-gallery' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'woo_variation_gallery_thumbnail_options',
						),
						
						// Thumbnails Item
						array(
							'title'             => esc_html__( 'Thumbnails Item', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_default_thumbnails_columns', 4 ) ),
							'css'               => 'width:50px;',
							'desc_tip'          => esc_html__( 'Product Thumbnails Item Image', 'woo-variation-gallery' ),
							'desc'              => '<br>' . sprintf( esc_html__( 'Product Thumbnails Item Image. Default value is: %d. Limit: 2-8.', 'woo-variation-gallery' ), absint( apply_filters( 'woo_variation_gallery_default_thumbnails_columns', 4 ) ) ),
							'id'                => 'woo_variation_gallery_thumbnails_columns',
							'custom_attributes' => array(
								'min'  => 2,
								'max'  => 8,
								'step' => 1,
							),
						),
						
						// Thumbnails Gap
						array(
							'title'             => esc_html__( 'Thumbnails Gap', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_default_thumbnails_gap', 0 ) ),
							'css'               => 'width:50px;',
							'desc_tip'          => esc_html__( 'Product Thumbnails Gap In Pixel', 'woo-variation-gallery' ),
							'desc'              => 'px <br>' . sprintf( esc_html__( 'Product Thumbnails Gap In Pixel. Default value is: %d. Limit: 0-20.', 'woo-variation-gallery' ), apply_filters( 'woo_variation_gallery_default_thumbnails_gap', 0 ) ),
							'id'                => 'woo_variation_gallery_thumbnails_gap',
							'custom_attributes' => array(
								'min'  => 0,
								'max'  => 20,
								'step' => 1,
							),
						),
						
						// Section End
						array(
							'type' => 'sectionend',
							'id'   => 'woo_variation_gallery_thumbnail_options'
						),
						
						// Gallery Section Start
						array(
							'name' => __( 'Gallery Options', 'woo-variation-gallery' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'woo_variation_gallery_main_options',
						),
						
						// Default Gallery Width
						array(
							'title'             => esc_html__( 'Gallery Width', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_default_width', 30 ) ),
							'css'               => 'width:60px;',
							'desc_tip'          => esc_html__( 'Slider gallery width in % for large devices.', 'woo-variation-gallery' ),
							'desc'              => '%. For large devices.<br>' . sprintf( __( 'Slider Gallery Width in %%. Default value is: %d. Limit: 10-100. Please check this <a target="_blank" href="%s">how to video to configure it.</a>', 'woo-variation-gallery' ), absint( apply_filters( 'woo_variation_gallery_default_width', 30 ) ), 'http://bit.ly/video-tuts-for-deactivate-dialogue' ),
							'id'                => 'woo_variation_gallery_width',
							'custom_attributes' => array(
								'min'  => 10,
								'max'  => 100,
								'step' => 1,
							),
						),
						
						// Medium Devices, Desktop
						array(
							'title'             => esc_html__( 'Gallery Width', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_medium_device_width', 0 ) ),
							'css'               => 'width:60px;',
							'desc_tip'          => esc_html__( 'Slider gallery width in px for medium devices, small desktop', 'woo-variation-gallery' ),
							'desc'              => 'px. For medium devices.<br>' . esc_html__( 'Slider gallery width in pixel for medium devices, small desktop. Default value is: 0. Limit: 0-1000. Media query (max-width : 992px)', 'woo-variation-gallery' ),
							'id'                => 'woo_variation_gallery_medium_device_width',
							'custom_attributes' => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 1,
							),
						),
						
						// Small Devices, Tablets
						array(
							'title'             => esc_html__( 'Gallery Width', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_small_device_width', 720 ) ),
							'css'               => 'width:60px;',
							'desc_tip'          => esc_html__( 'Slider gallery width in px for small devices, tablets', 'woo-variation-gallery' ),
							'desc'              => 'px. For small devices, tablets.<br>' . esc_html__( 'Slider gallery width in pixel for medium devices, small desktop. Default value is: 720. Limit: 0-1000. Media query (max-width : 768px)', 'woo-variation-gallery' ),
							'id'                => 'woo_variation_gallery_small_device_width',
							'custom_attributes' => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 1,
							),
						),
						
						// Extra Small Devices, Phones
						array(
							'title'             => esc_html__( 'Gallery Width', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_extra_small_device_width', 320 ) ),
							'css'               => 'width:60px;',
							'desc_tip'          => esc_html__( 'Slider gallery width in px for extra small devices, phones', 'woo-variation-gallery' ),
							'desc'              => 'px. For extra small devices, mobile.<br>' . esc_html__( 'Slider gallery width in pixel for extra small devices, phones. Default value is: 320. Limit: 0-1000. Media query (max-width : 480px)', 'woo-variation-gallery' ),
							'id'                => 'woo_variation_gallery_extra_small_device_width',
							'custom_attributes' => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 1,
							),
						),
						
						// Gallery Bottom GAP
						array(
							'title'             => esc_html__( 'Gallery Bottom Gap', 'woo-variation-gallery' ),
							'type'              => 'number',
							'default'           => absint( apply_filters( 'woo_variation_gallery_default_margin', 30 ) ),
							'css'               => 'width:60px;',
							'desc_tip'          => esc_html__( 'Slider gallery gottom margin in pixel', 'woo-variation-gallery' ),
							'desc'              => 'px <br>' . sprintf( esc_html__( 'Slider gallery bottom margin in pixel. Default value is: %d. Limit: 10-100.', 'woo-variation-gallery' ), apply_filters( 'woo_variation_gallery_default_margin', 30 ) ),
							'id'                => 'woo_variation_gallery_margin',
							'custom_attributes' => array(
								'min'  => 10,
								'max'  => 100,
								'step' => 1,
							),
						),
						
						// Preload Style
						array(
							'title'   => esc_html__( 'Preload Style', 'woo-variation-gallery' ),
							'type'    => 'select',
							'class'   => 'wc-enhanced-select',
							'default' => 'blur',
							'id'      => 'woo_variation_gallery_preload_style',
							'options' => array(
								'fade' => esc_html__( 'Fade', 'woo-variation-gallery' ),
								'blur' => esc_html__( 'Blur', 'woo-variation-gallery' ),
								'gray' => esc_html__( 'Gray', 'woo-variation-gallery' ),
							)
						),
						
						
						// End
						array(
							'type' => 'sectionend',
							'id'   => 'woo_variation_gallery_main_options'
						),
					
					) );
					break;
			
			endswitch;
			
			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
		}
		
		public function save() {
			
			global $current_section;
			
			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::save_fields( $settings );
			
			if ( $current_section ) {
				do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
			}
		}
	}
	
	return new Woo_Variation_Gallery_Settings();
	
