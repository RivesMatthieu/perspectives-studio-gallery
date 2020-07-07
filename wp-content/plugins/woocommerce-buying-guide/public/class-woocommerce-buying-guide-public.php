<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://woocommerce.db-dzine.de
 * @since      1.0.0
 *
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_Buying_Guide
 * @subpackage WooCommerce_Buying_Guide/public
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WooCommerce_Buying_Guide_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options
	 */
	private $options;

	/**
	 * Current Buying Guide
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options
	 */
	private $buying_guide;
	
	/**
	 * All Buying Guides for Cat
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options
	 */
	private $buying_guides;

	/**
	 * Filtered Products
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options
	 */
	private $products_to_filter;

	/**
	 * Current product category
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object
	 */
	private $current_category;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @return  [type]                       [description]
	 */
	public function enqueue_styles() {

		global $woocommerce_buying_guide_options;

		$this->options = $woocommerce_buying_guide_options;

		if (!$this->get_option('enable')) {
			return false;
		}

		$doNotLoadFontAwesome = $this->get_option('doNotLoadFontAwesome');
		if(!$doNotLoadFontAwesome)
		{
			wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0', 'all' );
		}

		$doNotLoadBootstrap = $this->get_option('doNotLoadBootstrap');
		if(!$doNotLoadBootstrap)
		{
			wp_enqueue_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), '3.3.7', 'all' );
		}

		$css = "";
		$accentBackgroundColor = $this->get_option('accentBackgroundColor');
		if(!empty($accentBackgroundColor)) {

			$css .= ".woocommerce-buying-guide-progress-bar, 
					.woocommerce-buying-guide-modal 
					.woocommerce-buying-guide-modal-content,
					.woocommerce-buying-guide-breadcrumb > li.active > a,
					.woocommerce-buying-guide-breadcrumb > li.active > a:hover,
					.woocommerce-buying-guide-breadcrumb > li.active > a:focus {
			 			 background-color: " . $accentBackgroundColor . ";
			}";

			$css .= ".woocommerce-buying-guide-breadcrumb > li.active > a .badge,
					.woocommerce-buying-guide-breadcrumb > li > a .badge.badge-step {
					color: " . $accentBackgroundColor . ";
					border-color: " . $accentBackgroundColor . ";
			}";

			$css .= ".woocommerce-buying-guide-breadcrumb > li.active > a:after {
					border-left-color: " . $accentBackgroundColor . ";
			}";
		}

		$accentTextColor = $this->get_option('accentTextColor');
		if(!empty($accentTextColor)) {

			$css .= ".woocommerce-buying-guide-modal .woocommerce-buying-guide-modal-content, 
					.woocommerce-buying-guide-modal-title, 
					.woocommerce-buying-guide-modal .woocommerce-buying-guide-question {
			  color: " . $accentTextColor . ";
			}";
		}

		$customCSS = $this->get_option('customCSS');
		if(!empty($customCSS))
		{
			$css = $css . $customCSS;
		}

		file_put_contents( __DIR__  . '/css/woocommerce-buying-guide-custom.css', $css);

		wp_enqueue_style( $this->plugin_name, plugin_dir_url(__FILE__).'css/woocommerce-buying-guide-public.css', array(), $this->version, 'all');
		wp_enqueue_style( $this->plugin_name.'-custom', plugin_dir_url( __FILE__ ) . 'css/woocommerce-buying-guide-custom.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @return  [type]                       [description]
	 */
	public function enqueue_scripts() {

		global $woocommerce_buying_guide_options;

		$this->options = $woocommerce_buying_guide_options;

		if (!$this->get_option('enable')) {
			return false;
		}

		$doNotLoadBootstrap = $this->get_option('doNotLoadBootstrap');
		if(!$doNotLoadBootstrap)
		{
			wp_enqueue_script( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), '3.3.7', true );
		}

		wp_enqueue_script( $this->plugin_name . '-public', plugin_dir_url( __FILE__ ) . 'js/woocommerce-buying-guide-public.js', array( 'jquery'), $this->version, true );

        $forJS = array( 
        	'live_filter' => $woocommerce_buying_guide_options['liveFilter'],
        	'filter_after_choice' => $woocommerce_buying_guide_options['filterAfterChoice'],
        	'adjust_choices' => $woocommerce_buying_guide_options['filterAdjustChoices'],
        	'hide_first' => $woocommerce_buying_guide_options['hideFirst'],
    	);
        wp_localize_script($this->plugin_name . '-public', 'buying_guide_options', $forJS);

	}

	/**
	 * Get Option 
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $option [description]
	 * @return  [type]                               [description]
	 */
    private function get_option($option)
    {
    	if(!is_array($this->options)) {
    		return false;
    	}
    	if(!array_key_exists($option, $this->options))
    	{
    		return false;
    	}
    	return $this->options[$option];
    }
	
    /**
     * Init WooCommerce Buyin Guide Public
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function init()
    {

		global $woocommerce_buying_guide_options;

		$this->options = $woocommerce_buying_guide_options;

		if (!$this->get_option('enable'))
		{

			return false;
		}

		$this->show_buying_buying_guide_on_product_category();
		$this->show_buying_buying_guide_on_shop_page();

    }

    /**
     * Check Filtered Products from $_GET parameter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $q        [description]
     * @param   [type]                       $instance [description]
     * @return  [type]                                 [description]
     */
    public function check_filtered_products($q, $instance)
    {
		if(isset($_GET['woocommerce-buying-guide-products'])) {
			
			$paged = is_paged();
			if($paged) {
				$page = (get_query_var('paged')) ? get_query_var('paged') : 1;
				$q->set('paged', $page);
			}

			$products_to_filter = $_GET['woocommerce-buying-guide-products'];
			if(empty($products_to_filter)) {
				$products_to_filter = array(
					uniqid()
				);
			} else {
				$products_to_filter = array_filter( explode(',', $products_to_filter), 'is_numeric');
			}

			$q->set('post__in', $products_to_filter);
		}
    }

    /**
     * Show buying Guide on product categories
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function show_buying_buying_guide_on_product_category()
    {
    	if(!is_product_category()) {
    		return false;
    	}

    	$operator = 'AND';
    	$alsoParent = $this->get_option('showOnParent');
    	if($alsoParent === "1") {
			$operator = 'IN';
    	}

        global $wp_query;
        $cat = $wp_query->get_queried_object();
        $this->current_category = $cat;
	    $args = array( 
	    	'post_type' => 'buying-guide', 
	    	'posts_per_page' => -1, 
	    	'tax_query' => array(
	        	array(
		            'taxonomy'      => 'product_cat',
		            'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
		            'terms'         => $this->current_category->term_id,
		            'operator'      => $operator // Possible values are 'IN', 'NOT IN', 'AND'.
		        )
	    	)
    	);

	    $query = new WP_Query( $args );
	    if(isset($query->posts) && !empty($query->posts)) {
	    	$this->render($query->posts);
	    }

	    return true;
	}

	/**
	 * Show Buying Guide on Shop Page
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @return  [type]                       [description]
	 */
    public function show_buying_buying_guide_on_shop_page()
    {
		// Shop Page
		if(!is_shop()) {
			return false;
		}

	    $args = array( 
	    	'post_type' => 'buying-guide', 
	    	'posts_per_page' => -1, 
	    	'meta_query'    => array(
		        array(
		            'key'       => 'woocommerce_buying_guide_shop_page',
		            'value'     => 'yes',
		        )
    		)
    	);

	    $query = new WP_Query( $args );

	    if(isset($query->posts) && !empty($query->posts)) {
	    	$this->render($query->posts);
	    }

	    return true;
    }

    /**
     * Render Buying Guides
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $buying_guides [description]
     * @return  [type]                                      [description]
     */
	public function render($buying_guides, $isShortcode = false)
	{
		$this->buying_guides = $buying_guides;

		$pos = 0;
		foreach ($buying_guides as $buying_guide) {

			// Get Buyin Guide Metas and Strip the Prefix
			$metas = get_post_meta($buying_guide->ID);
			$temp = array();
			foreach ($metas as $key => $meta) {
				if(strpos($key, 'woocommerce_buying_guide') === false) continue;

				if(strpos($key, 'products') === false) {
					$meta = $meta[0];
				} 

				$temp[substr($key, 25)] = $meta;
			}
			$metas = $temp;
			$buying_guide->metas = $metas;

			// Set current Buying Guide Position
			$this->buying_guide = $buying_guide;
			if($buying_guide->metas['position'] == "woocommerce_archive_description") {
				$pos = $pos + 10;
			} else {
				$pos = $pos - 10;
			}
			$pos = apply_filters('woocommerce_buying_guide_priority', $pos, $buying_guide->ID);


			if($isShortcode) {
				switch ($buying_guide->metas['style']) {
					// Render Buying Guide as Image
					case 'image':
						$this->renderAsImage($buying_guide); 
						break;
					// Render Buying Guide as Modal
					case 'modal':
						$this->renderAsModal($buying_guide); 

						add_action('wp_footer', function() use ($buying_guide) { 
							$this->renderQuestionsAndChoices($buying_guide, true); 
						}, $pos);
						break;
					// Render Buying Guide as Text
					default:
						$this->renderAsText($buying_guide); 
						break;
				}
			} elseif(isset($buying_guide->metas['style']) && !empty($buying_guide->metas['style'])) {
				switch ($buying_guide->metas['style']) {
					// Render Buying Guide as Image
					case 'image':
						add_action($buying_guide->metas['position'], function() use ($buying_guide) { 
							$this->renderAsImage($buying_guide); 
						}, $pos);
						break;
					// Render Buying Guide as Modal
					case 'modal':
						add_action($buying_guide->metas['position'], function() use ($buying_guide) { 
							$this->renderAsModal($buying_guide); 
						}, $pos);
						add_action('wp_footer', function() use ($buying_guide) { 
							$this->renderQuestionsAndChoices($buying_guide, true); 
						}, $pos);
						break;
					// Render Buying Guide as Text
					default:
						add_action($buying_guide->metas['position'], function() use ($buying_guide) { 
							$this->renderAsText($buying_guide); 
						}, $pos);
						break;
				}
			}
		}
	}

	/**
	 * Render Buying Guide as Image
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @return  [type]                                     [description]
	 */
	public function renderAsImage($buying_guide)
	{
		$id = $buying_guide->ID;

		$html = "";
		$html .= '<div id="woocommerce-buying-guide-' . $id . '" class="woocommerce-buying-guide">';
			$html .= '<div id="woocommerce-buying-guide-start-container-' . $id . '" class="woocommerce-buying-guide-start-container">';
				$html .= '<a id="woocommerce-buying-guide-link-' . $id . '" data-id="' . $id . '" href="#" class="woocommerce-buying-guide-link">';
					$html .= '<div id="woocommerce-buying-guide-image-' . $id . '" class="woocommerce-buying-guide-image woocommerce-buying-guide-image">';

						if (has_post_thumbnail( $id ) ) {
							$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
							$html .= '<img src="' . $image[0] . '" alt="' . $buying_guide->post_title . '">';
						}

					$html .= '</div>';
				$html .= '</a>';
			$html .= '</div>';
			$html .= $this->renderQuestionsAndChoices( $buying_guide );
			$html .= $this->getSuccessMessage( $buying_guide );
			$html .= $this->getErrorMessage( $buying_guide );
		$html .= '</div>';
		$html .= '<hr class="woocommerce-buying-guide-hr">';

		$html = apply_filters('woocommerce_buying_image_render_html', $html, $id);
		echo $html;
	}

	/**
	 * Render Buying Guide as Modal
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @return  [type]                                     [description]
	 */
	public function renderAsModal($buying_guide)
	{
		$id = $buying_guide->ID;

		$html = "";
		$html .= '<div id="woocommerce-buying-guide-' . $id . '" class="woocommerce-buying-guide">';
			$html .= '<div id="woocommerce-buying-guide-start-container-' . $id . '" class="woocommerce-buying-guide-start-container">';
				$html .= '<h2>' . $buying_guide->post_title . '</h2>';
				$html .= '<p>' . $buying_guide->post_content . '</p>';
				$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-modal-start btn btn-default button center">' . esc_html__( 'Start', 'woocommerce-buying-guide' ) . '</a>';
			$html .= '</div>';
			$html .= $this->getSuccessMessage( $buying_guide, true );
			$html .= $this->getErrorMessage( $buying_guide, true );
		$html .= '</div>';
		$html .= '<hr class="woocommerce-buying-guide-hr">';

		$html = apply_filters('woocommerce_buying_modal_render_html', $html, $id);
		echo $html;
	}

	/**
	 * Render Buying Guide as Text
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @return  [type]                                     [description]
	 */
	public function renderAsText($buying_guide)
	{
		$id = $buying_guide->ID;

		$html = "";
		$html .= '<div id="woocommerce-buying-guide-' . $id . '" class="woocommerce-buying-guide">';
			$html .= '<div id="woocommerce-buying-guide-start-container-' . $id . '" class="woocommerce-buying-guide-start-container">';
				$html .= '<h2>' . $buying_guide->post_title . '</h2>';
				$html .= '<p>' . $buying_guide->post_content . '</p>';
				$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-start btn btn-default button center">' . esc_html__( 'Start', 'woocommerce-buying-guide' ) . '</a>';
			$html .= '</div>';
			$html .= $this->renderQuestionsAndChoices( $buying_guide );
			$html .= $this->getSuccessMessage( $buying_guide );
			$html .= $this->getErrorMessage( $buying_guide );
		$html .= '</div>';
		$html .= '<hr class="woocommerce-buying-guide-hr">';

		$html = apply_filters('woocommerce_buying_text_render_html', $html, $id);
		echo $html;
	}

	/**
	 * Render Questions and Choices
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @param   boolean                      $modal        [description]
	 * @return  [type]                                     [description]
	 */
	public function renderQuestionsAndChoices($buying_guide, $modal = false)
	{
		$id = $buying_guide->ID;

		$questions = 4;
		if( !empty($this->get_option('maxQuestions')) ) {
			$questions = $this->get_option('maxQuestions');
		} 

		$choices = 4;
		if( !empty($this->get_option('maxChoices')) ) {
			$choices = $this->get_option('maxChoices');
		}
		
		$html = "";

		if($modal) {
			$html .= '
			<div class="woocommerce-buying-guide">
				<div id="woocommerce-buying-guide-modal-' . $id . '" class="woocommerce-buying-guide-modal modal fade" tabindex="-1" role="dialog">
					<div class="woocommerce-buying-guide-modal-dialog modal-dialog" role="document">
						<div class="woocommerce-buying-guide-modal-content modal-content">
							<div class="woocommerce-buying-guide-modal-body modal-body">
								<button type="button" class="woocommerce-buying-guide-modal-close close" data-id="' . $id . '" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

		}

		$html .= '<div id="woocommerce-buying-guide-question-choice-' . $id . '" class="woocommerce-buying-guide-question-choice">';

		// Progess Bar
		$progress_style = $buying_guide->metas['progress_style'];
		if($progress_style == "breadcrumb") {

			$html .= '<ol id="woocommerce-buying-guide-breadcrumb-' . $id . '" class="nav woocommerce-buying-guide-breadcrumb nav-justified" style="display: none;">';
			for ($i=1; $i <= $questions; $i++) { 

				if(!isset($buying_guide->metas['question' . $i])) {
					continue;
				}

				$active_question = '';
				if($i == 1) {
					$active_question = ' class="active"';
				}


				$breadcrumb_text = isset($buying_guide->metas['breadcrumb' . $i]) ? $buying_guide->metas['breadcrumb' . $i] : '';
				if(empty($breadcrumb_text)) {
					$breadcrumb_text = $buying_guide->metas['question' . $i];
				}

				$html .= '<li' . $active_question . ' id="woocommerce-buying-guide-progress-breadcrumb-' . $i . '"><a href="#"><span class="badge badge-step">' . $i . '</span>  ' . $breadcrumb_text . '</a></li>';

			}
			$html .= '</ol>';

		} else {
			$html .= '<div id="woocommerce-buying-guide-progress-' . $id . '" class="woocommerce-buying-guide-progress progress" style="display: none;">
				<div id="woocommerce-buying-guide-progress-bar-' . $id . '" class="woocommerce-buying-guide-progress-bar progress-bar"" style="width: 0%;">
				</div>
			</div>';
		}

		// Questions
		for ($i=1; $i <= $questions; $i++) { 

			if(!isset($buying_guide->metas['question' . $i])) {
				continue;
			}

			$allProducts = array();

			$html .= '<div id="woocommerce-buying-guide-question-container-' . $i . '" class="woocommerce-buying-guide-question-container" style="display:none;">';

				$question = isset($buying_guide->metas['question' . $i]) ? $buying_guide->metas['question' . $i] : '';
				if(!empty($question)) {
					$toBackId = $i - 1;
					// $html .= '<a data-to-back-id="' . $toBackId . '" data-question="' . $i . '" data-id="' . $id . '" href="#" id="woocommerce-buying-guide-go-back-' . $i . '" class="woocommerce-buying-guide-go-back btn btn-default button">' . __( 'Go Back', 'woocommerce-buying-guide') . '</a>';
					$html .= '<h3 class="woocommerce-buying-guide-question">' . $question;

					$tooltip = isset($buying_guide->metas['tooltip' . $i]) ? $buying_guide->metas['tooltip' . $i] : '';
					if(!empty($tooltip)) {
						$html .= 	'<span class="woocommerce-buying-guide-question-tooltip tooltip-trigger" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '">
										<i class="fa fa-question-circle"></i>
									</span>';
					}

					$html .= '</h3>';

					$intro = isset($buying_guide->metas['intro' . $i]) ? $buying_guide->metas['intro' . $i] : '';
					if(!empty($intro)) {
						$html .= 	'<p class="woocommerce-buying-guide-intro">' . $intro . '</p>';
					}

				}

				$html .= '<div id="woocommerce-buying-guide-choices-container-' . $i . '" class="woocommerce-buying-guide-choices-container">';

				// Choices By Attribute
				$attribute = isset($buying_guide->metas['attributes' . $i]) ? $buying_guide->metas['attributes' . $i] : '';
				if(!empty($attribute)) {
					$terms = get_terms($attribute);

					$ii = 1;
					foreach ($terms as $term) {

						$args = array( 
							'post_type'				=> 'product',
							'post_status' 			=> 'publish',
							'ignore_sticky_posts'	=> 1,
							'posts_per_page' 		=> -1,
							// 'meta_query' 			=> array(
							// 	array(
							// 		'key' 			=> '_visibility',
							// 		'value' 		=> array('catalog', 'visible'),
							// 		'compare' 		=> 'IN'
							// 	)
							// ),
							'tax_query' 			=> array(
						    	array(
							    	'taxonomy' 		=> $attribute,
									'terms' 		=> $term->slug,
									'field' 		=> 'slug',
									'operator' 		=> 'IN'
								),
						    )
						);
						if($buying_guide->metas['shop_page'] !== "yes") {
							$args['tax_query'][] =
								array(
						            'taxonomy'      => 'product_cat',
						            'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
						            'terms'         => $this->current_category->term_id,
						            'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
						        );
						}

						$products = new WP_Query( $args );

						// Skip this attribute value, because no products were found in attribute + current category
						if(empty($products->posts)) {
							continue;
						}

						$temp = array();
						foreach ($products->posts as $product) {
							$temp[] = $product->ID;
						}

						$products = "";
						if(!empty($temp)) {
							$allProducts = array_merge($allProducts, $temp);
							$products = implode(',', $temp);
						}

						$html .= '<div class="woocommerce-buying-guide-choice-container">';

							$html .= '<div class="woocommerce-buying-guide-choice-radio" data-buying-guide="' . $id . '" data-question="' . $i . '" data-products="' . $products . '">';

							$html .= '<label class="woocommerce-buying-guide-choice radio-inline">';

							$html .= '<input type="radio"> ';
							$html .= '<span class="woocommerce-buying-guide-choice-radio-text">' . $term->name . '</span>';

							$html .= '</label>';

							$tooltip = $term->description;
							if(!empty($tooltip)) {
								$html .= 	'<span class="woocommerce-buying-guide-choice-tooltip tooltip-trigger" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '">
												<i class="fa fa-question-circle"></i>
											</span>';
							}

							$html .= '</div>';

						$html .= '</div>';
						$ii++;

					}
				// Custom Choices 
				// !! Just remove the else and you can render both (attributes & custom choices) !!
				} else {
				
					for ($ii = 1; $ii <= $choices; $ii++) { 

						$choice = isset($buying_guide->metas['choice' . $i . $ii]) ? $buying_guide->metas['choice' . $i . $ii] : '';

						if(empty($choice)) {
							continue;
						} 

						$products = isset($buying_guide->metas['products' . $i . $ii]) ? $buying_guide->metas['products' . $i . $ii] : '';
						if(!empty($products)) {
							$allProducts = array_merge($allProducts, $products);
							$products = implode(',', $buying_guide->metas['products' . $i . $ii]);	
						}

						$html .= '<div class="woocommerce-buying-guide-choice-container">';

							$html .= '<div class="woocommerce-buying-guide-choice-radio" data-buying-guide="' . $id . '" data-question="' . $i . '" data-products="' . $products . '">';

							$html .= '<label class="woocommerce-buying-guide-choice radio-inline">';

							$icon = isset($buying_guide->metas['icon' . $i . $ii]) ? $buying_guide->metas['icon' . $i . $ii] : '';
							if(!empty($icon)) {
								$image = wp_get_attachment_image_src( $icon, 'single-post-thumbnail' );
								$html .= '<img class="woocommerce-buying-guide-choice-radio-icon" src="' . $image[0] . '" alt="' . $buying_guide->post_title . '">';
							} else {
								$html .= '<input type="radio"> ';
							}
							$html .= '<span class="woocommerce-buying-guide-choice-radio-text">' . $choice . '</span>';

							$html .= '</label>';

							$tooltip = isset($buying_guide->metas['tooltip' . $i . $ii]) ? $buying_guide->metas['tooltip' . $i . $ii] : '';
							if(!empty($tooltip)) {
								$html .= 	'<span class="woocommerce-buying-guide-choice-tooltip tooltip-trigger" data-toggle="tooltip" data-placement="top" title="' . $tooltip . '">
												<i class="fa fa-question-circle"></i>
											</span>';
							}

							$html .= '</div>';

						$html .= '</div>';

					}
				}

				// Show Skip Button
				$skip = isset($buying_guide->metas['skip' . $i]) ? $buying_guide->metas['skip' . $i] : 'no';
				if($skip == "yes") {

					$products = implode(',' ,$allProducts);
					$skip_text = isset($buying_guide->metas['skip_text' . $i]) ? $buying_guide->metas['skip_text' . $i] : 'Skip';

					$html .= '<div class="woocommerce-buying-guide-choice-container">';

						$html .= '<div class="woocommerce-buying-guide-choice-radio" data-buying-guide="' . $id . '" data-question="' . $i . '" data-products="' . $products . '">';

							$html .= '<label class="woocommerce-buying-guide-choice radio-inline">';

								$html .= '<input type="radio"> ';
								$html .= '<span class="woocommerce-buying-guide-choice-radio-text">' . $skip_text . '</span>';

							$html .= '</label>';

						$html .= '</div>';

					$html .= '</div>';

				}

				$html .= '</div>';
			$html .= '</div>';
		}
		$html .= '</div>';

		if($modal) {
			$html .= '		</div>
						</div>
					</div>
				</div>
			</div>';
			echo $html;
			return true;
		}

		$html = apply_filters('woocommerce_buying_guide_questions_and_choices_html', $html, $id);
		return $html;
	}

	/**
	 * Get Success Message
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @param   boolean                      $modal        [description]
	 * @return  [type]                                     [description]
	 */
	public function getSuccessMessage($buying_guide, $modal = false)
	{
		$html = "";
		$id = $buying_guide->ID;

		$success = $this->get_option('successText');
		if(!empty($success)) {
			$html .= '<div id="woocommerce-buying-guide-success-' . $id . '" class="woocommerce-buying-guide-success" style="display:none;">';
				$html .= '<div class="woocommerce-buying-guide-success-message">' . $success . '</div>';

				$choices_made = $buying_guide->metas['choices_made'];
				if($choices_made == "yes") {
			 		$html .= '<div id="woocommerce-buying-guide-success-choices-made-' . $id . '" class="woocommerce-buying-guide-success-choices-made"></div>';
				}
				
				$liveFilter = $this->get_option('liveFilter');
				if(!$liveFilter) {
					if(is_shop()) {
						$link = get_permalink( wc_get_page_id( 'shop' ) );
					} else {
						$link = get_term_link( $this->current_category );
					}
					$html .= '<a href="' . $link . '" data-id="' . $id . '" class="btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
				} else {
					if($modal == true) {
						$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-modal-start btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
					} else {
						$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-start btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
					}
				}
				
			$html .= '</div>';
		}

		$html = apply_filters('woocommerce_buying_guide_success_message_html', $html, $id);
		return $html;
	}

	/**
	 * Get Error Message
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $buying_guide [description]
	 * @param   boolean                      $modal        [description]
	 * @return  [type]                                     [description]
	 */
	public function getErrorMessage($buying_guide, $modal = false)
	{
		$html = "";
		$id = $buying_guide->ID;

		$error = $this->get_option('errorText');
		if(!empty($error)) {
			$html .= '<div id="woocommerce-buying-guide-error-' . $id . '" class="woocommerce-buying-guide-error" style="display:none;">';
				$html .= $error . '<br><br>';

				$liveFilter = $this->get_option('liveFilter');
				if(!$liveFilter) {
					if(is_shop()) {
						$link = get_permalink( wc_get_page_id( 'shop' ) );
					} else {
						$link = get_term_link( $this->current_category );
					}
					$html .= '<a href="' . $link . '" data-id="' . $id . '" class="btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
				} else {
					if($modal == true) {
						$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-modal-start btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
					} else {
						$html .= '<a href="#" id="woocommerce-buying-guide-start-' . $id . '" data-id="' . $id . '" class="woocommerce-buying-guide-start btn btn-default button center">' . esc_html__( 'Start again', 'woocommerce-buying-guide' ) . '</a>';
					}
				}

			$html .= '</div>';
		}

		$html = apply_filters('woocommerce_buying_guide_error_message_html', $html, $id);
		return $html;
	}

	public function shortcode($atts)
	{
	    $attributes = shortcode_atts( array(
	        'id' => '',
	        'order' => 'ASC',
	        'orderby' => 'date',
	        // 'category' => '',
	    ), $atts );

	    ob_start();

	    if(!isset($attributes['id']) || empty($attributes['id'])) {
	    	echo __('Buying Guide ID missig', 'woocommerce-buying-guide');
	    	return false;
	    }

	    $buying_guide_id = $attributes['id'];
	    $categories = get_the_terms($buying_guide_id, 'product_cat');
	    if(empty($categories)) {
	    	echo __('Buying Guide has no product Categories.', 'woocommerce-buying-guide');
	    	return false;
	    }

	    $this->current_category = reset($categories);

    	$tax_query = array();
    	$term_ids = array();
    	foreach ($categories as $key => $category) {
    		$term_ids[] = $category->term_id;
    	}

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => 'id',
			'terms' => $term_ids
		);
		
		
		$args = array( 
			'posts_per_page' => -1, 
			'post_status' => 'publish', 
			'post_type' => 'product', 
			'order' => $attributes['order'],
			'orderby' => $attributes['orderby'],
			'tax_query' => $tax_query,
		);

	    $products = get_posts($args);

	    echo '<div class="woocommerce">';

	    $buying_guides = get_posts(array(
	    	'post__in' => array($buying_guide_id),
	    	'post_type' => 'buying-guide',
	    ));

	    $this->render($buying_guides, true);

		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();


		global $product, $post;
		foreach ($products as $product) {

			$GLOBALS['post'] = get_post( $product->ID ); // WPCS: override ok.
			setup_postdata( $GLOBALS['post'] );

			// the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 *
			 * @hooked WC_Structured_Data::generate_product_data() - 10
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();

		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );

		echo '</div>';

		wp_reset_postdata();

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}