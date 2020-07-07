<?php
/**
 * Popups for Divi
 * Main plugin instance/controller. The main popup logic is done in javascript,
 * so we mainly need to make sure that our JS/CSS is loaded on the correctly.
 *
 * @package Popups_For_Divi
 */

defined( 'ABSPATH' ) || die();

/**
 * The application entry class.
 */
class Popups_For_Divi extends Popups_For_Divi_Component {

	/**
	 * Hook up the module.
	 *
	 * @since 1.0.0
	 * @since 2.0.4 function renamed from __construct() to setup().
	 */
	public function setup() {
		load_plugin_textdomain(
			'popups-for-divi',
			false,
			dirname( DIVI_POPUP_PLUGIN ) . '/lang/'
		);

		add_filter(
			'plugin_action_links_' . DIVI_POPUP_PLUGIN,
			array( $this, 'plugin_add_settings_link' )
		);

		add_filter(
			'plugin_row_meta',
			array( $this, 'plugin_row_meta' ),
			10,
			4
		);

		// Do not load the JS library, when the Pro version is active.
		if ( defined( 'DIVI_AREAS_PLUGIN' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_js_library' ] );
		add_filter( 'divi_areas_debug_infos', [ $this, 'generate_debug_infos' ] );

		// Load compatibility module.
		$this->add_module( 'compat', 'Popups_For_Divi_Compatibility' )
			->setup_on( 'divi_popups_loaded' );

		// Add dependencies.
		$this->add_module( 'onboarding', 'Popups_For_Divi_Onboarding' )
			->setup_on( 'divi_popups_loaded' );
		$this->add_module( 'editor', 'Popups_For_Divi_Editor' )
			->setup_on( 'divi_popups_loaded' );

		/**
		 * Initialize dependencies.
		 *
		 * @since 2.0.4
		 */
		do_action( 'divi_popups_loaded' );
	}

	/**
	 * Display a custom link in the plugins list
	 *
	 * @since  1.0.2
	 * @param  array $links List of plugin links.
	 * @return array New list of plugin links.
	 */
	public function plugin_add_settings_link( $links ) {
		$links[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://divimode.com/divi-popup/?utm_source=wpadmin&utm_medium=link&utm_campaign=popups-for-divi',
			__( 'How it works', 'divi-popup' )
		);
		return $links;
	}

	/**
	 * Display additional details in the right column of the "Plugins" page.
	 *
	 * @since 1.6.0
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data.
	 * @param string   $status      Status of the plugin. Defaults are 'All', 'Active',
	 *                              'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
	 *                              'Drop-ins', 'Search'.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( DIVI_POPUP_PLUGIN !== $plugin_file ) {
			return $plugin_meta;
		}

		$plugin_meta[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://divimode.com/divi-areas-pro/?utm_source=wpadmin&utm_medium=link&utm_campaign=popups-for-divi',
			__( 'Divi Areas <strong>Pro</strong>', 'divi-popup' )
		);

		return $plugin_meta;
	}

	/**
	 * Add the CSS/JS support to the front-end to make the popups work.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_js_library() {
		global $wp_query;

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		/*
		 * Logic found in Divi/includes/builder/functions.php
		 * @see function et_pb_register_preview_page()
		 */
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'true' === $wp_query->get( 'et_pb_preview' ) && isset( $_GET['et_pb_preview_nonce'] ) ) { // input var okay.
			return;
		}

		$cache_version = DIVI_POPUP_VERSION;

		if ( function_exists( 'et_fb_is_enabled' ) ) {
			$is_divi_v3 = true;
			add_filter( 'divi_popup_build_mode', 'et_fb_is_enabled' );
		} else {
			$is_divi_v3 = false;
		}

		$js_data = [];

		/**
		 * The base z-index. This z-index is used for the overlay, every
		 * popup has a z-index increased by 1.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['zIndex'] = 1000000;

		/**
		 * Speed of the fade-in/out animations. Set this to 0 to disable fade-in/out.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['animateSpeed'] = 400;

		/**
		 * A class-name prefix that can be used in *any* element to trigger
		 * the given popup. Default prefix is 'show-popup-', so we could
		 * add the class 'show-popup-demo' to an image. When this image is
		 * clicked, the popup "#demo" is opened.
		 * The prefix must have 3 characters or more.
		 *
		 * Example:
		 * <span class="show-popup-demo">Click here to show #demo</span>
		 *
		 * @since JS 1.0.0
		 */
		$js_data['triggerClassPrefix'] = 'show-popup-';

		/**
		 * Alternate popup trigger via data-popup attribute.
		 *
		 * Example:
		 * <span data-popup="demo">Click here to show #demo</span>
		 *
		 * @since JS 1.0.0
		 */
		$js_data['idAttrib'] = 'data-popup';

		/**
		 * Class that indicates a modal popup. A modal popup can only
		 * be closed via a close button, not by clicking on the overlay.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['modalIndicatorClass'] = 'is-modal';

		/**
		 * This changes the default close-button state when a popup does
		 * not specify noCloseClass or withCloseClass
		 *
		 * @since  1.1.0
		 */
		$js_data['defaultShowCloseButton'] = true;

		/**
		 * Add this class to the popup section to show the close button
		 * in the top right corner.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['withCloseClass'] = 'with-close';

		/**
		 * Add this class to the popup section to hide the close button
		 * in the top right corner.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['noCloseClass'] = 'no-close';

		/**
		 * Name of the class that closes the currently open popup. By default
		 * this is "close".
		 *
		 * @since JS 1.0.0
		 */
		$js_data['triggerCloseClass'] = 'close';

		/**
		 * Name of the class that marks a popup as "singleton". A "singleton" popup
		 * will close all other popups when it is opened/focused. By default this
		 * is "single".
		 *
		 * @since JS 1.0.0
		 */
		$js_data['singletonClass'] = 'single';

		/**
		 * Name of the class that activates the dark mode (dark close button) of the
		 * popup.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['darkModeClass'] = 'dark';

		/**
		 * Name of the class that removes the box-shadow from the popup.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['noShadowClass'] = 'no-shadow';

		/**
		 * Name of the class that changes the popups close button layout.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['altCloseClass'] = 'close-alt';

		/**
		 * CSS selector used to identify popups.
		 * Each popup must also have a unique ID attribute that
		 * identifies the individual popups.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['popupSelector'] = '.et_pb_section.popup';

		/**
		 * Whether to wait for an JS event-trigger before initializing
		 * the popup module in front end. This is automatically set
		 * for the Divi theme.
		 *
		 * If set to false, the popups will be initialized instantly when the JS
		 * library is loaded.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['initializeOnEvent'] = (
			$is_divi_v3
				? 'et_pb_after_init_modules' // Divi 3.0+ detected.
				: false // Older Divi or other themes.
		);

		/**
		 * All popups are wrapped in a new div element. This is the
		 * class name of this wrapper div.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['popupWrapperClass'] = 'area-outer-wrap';

		/**
		 * CSS class that is added to the popup when it enters
		 * full-width mode (i.e. on small screens)
		 *
		 * @since JS 1.0.0
		 */
		$js_data['fullWidthClass'] = 'full-width';

		/**
		 * CSS class that is added to the popup when it enters
		 * full-height mode (i.e. on small screens)
		 *
		 * @since JS 1.0.0
		 */
		$js_data['fullHeightClass'] = 'full-height';

		/**
		 * CSS class that is added to the website body when the background overlay
		 * is visible.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['openPopupClass'] = 'da-overlay-visible';

		/**
		 * CSS class that is added to the modal overlay that is
		 * displayed while at least one popup is visible.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['overlayClass'] = 'da-overlay';

		/**
		 * Class that adds an exit-intent trigger to the popup.
		 * The exit intent popup is additionally triggered, when the
		 * mouse pointer leaves the screen towards the top.
		 * It's only triggered once.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['exitIndicatorClass'] = 'on-exit';

		/**
		 * Class that can be added to any trigger element (e.g., to a link) to
		 * instruct the JS API to trigger the Area on mouse contact. Default trigger
		 * is only click, not hover.
		 *
		 * @since JS 1.2.3
		 */
		$js_data['hoverTriggerClass'] = 'on-hover';

		/**
		 * Class that can be added to an trigger (e.g., to a link or button) to
		 * instruct the JS API to trigger the Area on click. That is the default
		 * behavior already, so this class only needs to be added if you want to
		 * enable on-hover AND on-click triggers for the same element.
		 *
		 * @since JS 1.2.3
		 */
		$js_data['clickTriggerClass'] = 'on-click';

		/**
		 * Defines the delay for reacting to exit-intents.
		 * Default is 2000, which means that an exit intent during the first two
		 * seconds after page load is ignored.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['onExitDelay'] = 2000;

		/**
		 * Class to hide a popup on mobile devices.
		 * Used for non-Divi themes or when creating popups via DiviPopup.register().
		 *
		 * @since JS 1.0.0
		 */
		$js_data['notMobileClass'] = 'not-mobile';

		/**
		 * Class to hide a popup on tablet devices.
		 * Used for non-Divi themes or when creating popups via DiviPopup.register().
		 *
		 * @since JS 1.0.0
		 */
		$js_data['notTabletClass'] = 'not-tablet';

		/**
		 * Class to hide a popup on desktop devices.
		 * Used for non-Divi themes or when creating popups via DiviPopup.register().
		 *
		 * @since JS 1.0.0
		 */
		$js_data['notDesktopClass'] = 'not-desktop';

		/**
		 * The parent container which holds all popups. For most Divi sites
		 * this could be "#page-container", but some child themes do not
		 * adhere to this convention.
		 * When a valid Divi theme is detected by the JS library, it will switch from
		 * 'body' to '#page-container'. To avoid this, simply use
		 *
		 * @since JS 1.0.0
		 */
		$js_data['baseContext'] = 'body';

		/**
		 * This class is added to the foremost popup; this is useful to
		 * hide/fade popups in the background.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['activePopupClass'] = 'is-open';

		/**
		 * This is the class-name of the close button that is
		 * automatically added to the popup. Only change this, if you
		 * want to use existing CSS or when the default class causes a
		 * conflict with your existing code.
		 *
		 * Note: The button is wrapped in a span which gets the class-
		 *       name `closeButtonClass + "-wrap"` e.g. "da-close-wrap"
		 *
		 * @since JS 1.0.0
		 */
		$js_data['closeButtonClass'] = 'da-close';

		/**
		 * Apply this class to a popup to add a loading animation in the background.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['withLoaderClass'] = 'with-loader';

		/**
		 * Display debug output in the JS console.
		 *
		 * @since JS 1.0.0
		 */
		$js_data['debug'] = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;

		/* -- End of default configuration -- */

		// Compatibility with older Popups for Divi version.
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$js_data = apply_filters( 'evr_divi_popup-js_data', $js_data );

		// Divi Areas Pro filter.
		$js_data = apply_filters( 'divi_areas_js_data', $js_data );

		/**
		 * Additional debugging details to generate JS error reports.
		 *
		 * @since 1.2.2
		 * @var array $infos Details about the current environment.
		 */
		$js_data['sys'] = apply_filters( 'divi_areas_debug_infos', [] );

		if ( is_admin() || apply_filters( 'divi_popup_build_mode', false ) ) {
			$base_name  = 'builder';
			$inline_css = '';
		} else {
			$base_name  = 'front';
			$inline_css = sprintf(
				'%s{display:none}',
				$js_data['popupSelector']
			);
		}

		if ( $js_data['debug'] || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
			$cache_version .= '-' . time();
		}

		// Inject the loader module and the configuration object into the header.
		$this->inject_loader( $js_data );

		wp_register_script(
			'js-divi-popup',
			plugins_url( 'js/' . $base_name . '.js', dirname( __FILE__ ) ),
			[ 'jquery' ],
			$cache_version,
			true
		);

		wp_register_style(
			'css-divi-popup',
			plugins_url( 'css/' . $base_name . '.css', dirname( __FILE__ ) ),
			array(),
			$cache_version,
			'all'
		);

		wp_enqueue_script( 'js-divi-popup' );
		wp_enqueue_style( 'css-divi-popup' );

		if ( $inline_css ) {
			wp_add_inline_style( 'css-divi-popup', $inline_css );
		}
	}

	/**
	 * Outputs a small inline script on the page to initialize the JS API.
	 *
	 * This script is output as inline script, because it needs to be usable at the
	 * beginning of the html body. Due to its size, a separate HTTP request is most
	 * likely costing more than an inline output.
	 *
	 * Also, many caching plugins will defer or combine the loader.js and effectively
	 * breaking the purpose of enqueueing it this early.
	 *
	 * @since 1.4.3
	 * @param array $js_config The DiviAreaConfig object details.
	 */
	public function inject_loader( array $js_config ) {
		/*
		Notice how this function ignores WP Coding Standards!

		The reason is, that we load the contents of a javascript file which we
		control (it's minified and part of plugin folder). Hence, the output is
		also sane and does not require escaping.

		The security effects on the page are identical to including the code as
		a regular wp_enqueue_script() handle.
		*/

		$loader = [];

		/**
		 * Output the JS configuration before the loader.js contents.
		 *
		 * This line is used by the compatibility module!
		 *
		 * @see Popups_For_Divi_Compatibility::sg_optimizer_exclude_inline_content()
		 */
		$loader[] = sprintf(
			'window.DiviPopupData=window.DiviAreaConfig=%s',
			wp_json_encode( $js_config )
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$loader[] = file_get_contents( DIVI_POPUP_PATH . 'js/loader.js' );

		printf(
			'<script id="diviarea-loader">%s</script>',
			implode( ';', $loader ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Collect anonymous details about the current system for output in error logs.
	 *
	 * @filter divi_areas_debug_infos
	 *
	 * @since 2.0.4
	 * @param array $infos Debug details.
	 * @return array Array containing debug details.
	 */
	public function generate_debug_infos( array $infos ) {
		global $wp_version;

		/*
		For security reasons, we only generate debug information for
		logged-in users.
		*/

		if ( is_user_logged_in() ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$curr_theme     = wp_get_theme();
			$builder_plugin = 'divi-builder/divi-builder.php';

			/*
			Get relative path of the current file:
			1. gets the current file path and turns \ into /
			2. removes the leading WP_PLUGIN_DIR part
			3. remove an eventual leading slash
			-> result e.g., "popups-for-divi/includes/class-popups-for-divi.php"
			*/
			$file_rel    = ltrim(
				str_replace(
					wp_normalize_path( WP_PLUGIN_DIR ),
					'',
					wp_normalize_path( __FILE__ )
				),
				'/'
			);
			$plugin_dirs = explode( '/', $file_rel );
			$plugin_name = $plugin_dirs[0];

			/*
			The plugin slug points to the main plugin file,
			e.g., "/popups-for-divi/plugin.php"
			-> we read the actual plugin version from that file.
			*/
			$plugin_slug = '/' . $plugin_name . '/plugin.php';

			// Fix path for windows.
			$plugin_path = wp_normalize_path( WP_PLUGIN_DIR . $plugin_slug );

			if ( $curr_theme->stylesheet !== $curr_theme->template ) {
				$curr_theme           = wp_get_theme( $curr_theme->template );
				$infos['child_theme'] = true;
			} else {
				$infos['child_theme'] = false;
			}
			$infos['theme']     = $curr_theme->stylesheet;
			$infos['theme_ver'] = $curr_theme->version;

			if (
				is_plugin_active( $builder_plugin )
				|| is_plugin_active_for_network( $builder_plugin )
				&& file_exists( WP_PLUGIN_DIR . '/' . $builder_plugin )
			) {
				$builder_plugin_path  = wp_normalize_path( WP_PLUGIN_DIR . '/' . $builder_plugin );
				$divi_plugin          = get_plugin_data( $builder_plugin_path );
				$infos['use_builder'] = true;
				$infos['builder_ver'] = $divi_plugin['Version'];
			} else {
				$infos['use_builder'] = false;
				$infos['builder_ver'] = '-';
			}

			if ( file_exists( $plugin_path ) ) {
				$plugin_data = get_plugin_data( $plugin_path );
			} else {
				$plugin_data = false;
			}

			$infos['plugin']     = $plugin_name;
			$infos['plugin_ver'] = ( $plugin_data ? $plugin_data['Version'] : '-' );
			$infos['wp_ver']     = $wp_version;
			$infos['php_ver']    = PHP_VERSION;
		}

		return $infos;
	}
}
