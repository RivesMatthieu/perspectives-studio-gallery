<?php
class WoofiltersWpf extends ModuleWpf {
	public $mainWCQuery = '';
	public $renderModes = array();
	public $preselects = array();
	public $displayMode = null;

	public function init() {
		DispatcherWpf::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_shortcode(WPF_SHORTCODE, array($this, 'render'));
		add_shortcode(WPF_SHORTCODE_PRODUCTS, array($this, 'renderProductsList'));
		add_shortcode(WPF_SHORTCODE_SELECTED_FILTERS, array($this, 'renderSelectedFilters'));
		if (is_admin()) {
			add_action('admin_notices', array($this, 'showAdminErrors'));
		}
		FrameWpf::_()->addScript('jquery-ui-autocomplete', '', array('jquery'), false, true);

		add_action('woocommerce_product_query', array($this, 'loadProductsFilter'));
		add_action('woocommerce_shortcode_products_query', array($this, 'loadShortcodeProductsFilter'), 999);
		add_filter('woocommerce_product_query_tax_query', array($this, 'customProductQueryTaxQuery'), 10, 1);
		trait_exists('\Essential_Addons_Elementor\Template\Content\Product_Grid') && add_action('pre_get_posts', array($this, 'loadProductsFilterForProductGrid'), 999);

		$options = FrameWpf::_()->getModule('options')->getModel('options')->getAll();
		add_filter('loop_shop_per_page', array($this, 'newLoopShopPerPage'), 20 );

		class_exists( 'WC_pif' ) && add_filter( 'post_class', array( $this, 'WC_pif_product_has_gallery' ) );
		add_filter('yith_woocompare_actions_to_check_frontend', array($this, 'addAjaxFilterForYithWoocompare'), 20 );
	}

	public function newLoopShopPerPage( $count ) {
		$options = FrameWpf::_()->getModule('options')->getModel('options')->getAll();
		if ( isset($options['count_product_shop']) && isset($options['count_product_shop']['value']) && !empty($options['count_product_shop']['value']) ) {
			$count  = $options['count_product_shop']['value'];
		}
		return $count ;
	}

	public function addWooOptions( $args ) {
		if (get_option('woocommerce_hide_out_of_stock_items') == 'yes') {
			$args['meta_query'][] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!='
				)
			);
		}
		return $args;
	}

	public function getPreselectedValue( $val = '' ) {
		if (empty($val)) {
			return $this->preselects;
		}
		return isset($this->preselects[$val]) ? $this->preselects[$val] : null;
	}
	public function addPreselectedParams() {
		if (!is_admin()) {
			global $wp_registered_widgets;
			$filterWidget = 'wpfwoofilterswidget';

			$widgetOpions = get_option('widget_' . $filterWidget);
			$sidebarsWidgets = wp_get_sidebars_widgets();
			$preselects = array();
			$filters = array();
			if ( is_array($sidebarsWidgets) && !empty($widgetOpions) ) {
				foreach ($sidebarsWidgets as $sidebar => $widgets) {
					if ( ( 'wp_inactive_widgets' === $sidebar || 'orphaned_widgets' === substr($sidebar, 0, 16) ) ) {
						continue;
					}
					if (is_array($widgets)) {
						foreach ($widgets as $widget) {
							$ids = explode('-', $widget);
							if ( count($ids) == 2 && $ids[0] == $filterWidget ) {
								if ( isset($widgetOpions[$ids[1]]) && isset($widgetOpions[$ids[1]]['id']) ) {
									$filterId = $widgetOpions[$ids[1]]['id'];

									if (!isset($filters[$filterId])) {
										$filter = $this->getModel('woofilters')->getById($filterId);
										$settings = unserialize($filter['setting_data']);
										$preselect = !empty($settings['settings']['filters']['preselect']) ? $settings['settings']['filters']['preselect'] : '';
										if (!empty($preselect)) {
											$mode = $this->getRenderMode($filterId, $settings);
											if ($mode > 0) {
												$preselects = array_merge($preselects, explode(';', $preselect));
											}
										}
										$filters[$filterId] = 1;
									}
								}
							}

						}
					}
				}
			}
			$this->preselects = array();
			foreach ($preselects as $value) {
				if (!empty($value)) {
					$paar = explode('=', $value);
					if (count($paar) == 2) {
						$name = $paar[0];
						$var = $paar[1];
						if ( 'min_price' == $name || 'max_price' == $name ) {
							$var = $this->getCurrencyPrice($var);
						}

						$this->preselects[$name] = $var;
					}
				}
			}
		}
	}

	public function loadProductsFilter( $q ) {
		$metaQuery = $this->preparePriceFilter(
			ReqWpf::getVar(
				'min_price',
				'all',
				$this->getPreselectedValue('min_price')),
				ReqWpf::getVar('max_price', 'all', $this->getPreselectedValue('max_price')
			)
		);
		if (false != $metaQuery) {
			$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
			remove_filter( 'posts_clauses', array(WC()->query, 'price_filter_post_clauses' ), 10, 2);
		}

		if (ReqWpf::getVar('pr_stock')) {
			$slugs = explode('|', ReqWpf::getVar('pr_stock'));
			if ($slugs) {
				$metaQuery = array(
					array(
						'key' => '_stock_status',
						'value' => $slugs,
						'compare' => 'IN'
					)
				);
			}
			$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
		}
		if (ReqWpf::getVar('pr_onsale')) {
			$q->set('post__in', array_merge(array(0), wc_get_product_ids_on_sale()));
		}

		if (ReqWpf::getVar('pr_author')) {
			$author_obj = get_user_by('slug', ReqWpf::getVar('pr_author'));
			if (isset($author_obj->ID)) {
				$q->set( 'author', $author_obj->ID );
			}
		}
		if (ReqWpf::getVar('vendors')) {
			$author_obj = get_user_by('slug', ReqWpf::getVar('vendors'));
			if (isset($author_obj->ID)) {
				$q->set( 'author', $author_obj->ID );
			}
		}
		if (ReqWpf::getVar('pr_rating')) {
			$ratingRange = ReqWpf::getVar('pr_rating');
			$range = strpos($ratingRange, '-') !== false ? explode('-', $ratingRange) : array(intval($ratingRange));
			if (isset($range[1]) && intval($range[1]) !== 5) {
				$range[1] = $range[1] - 0.001;
			}
			if ($range[0] && isset($range[1]) && $range[1]) {
				$metaQuery = array(
					array( // Simple products type
						'key' => '_wc_average_rating',
						'value' => array($range[0], $range[1]),
						'type' => 'DECIMAL',
						'compare' => 'BETWEEN'
					)
				);
				$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
			} elseif ($range[0]) {
				$metaQuery = array(
					array( // Simple products type
						'key' => '_wc_average_rating',
						'value' => $range[0],
						'type' => 'DECIMAL',
						'compare' => '='
					)
				);
				$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
			}
		}

		if (ReqWpf::getVar('wpf_count')) {
			$q->set('posts_per_page', ReqWpf::getVar('wpf_count'));
		}
		if (ReqWpf::getVar('wpf_order')) {
			add_filter( 'posts_clauses', array($this, 'addClausesTitleOrder'));
		}

		$q = DispatcherWpf::applyFilters('loadProductsFilterPro', $q);

		$this->mainWCQuery = $q;
	}
	
	public function loadProductsFilterForProductGrid( $q ) {
		if ('product' == $q->get('post_type')) {
			global $paged;
			$this->loadProductsFilter($q);
			if ($paged) {
				$q->set('paginate', true);
				$q->set('paged', $paged);
			}
			$q->set('tax_query', $this->customProductQueryTaxQuery($this->mainWCQuery->get('tax_query')));
			$this->mainWCQuery = $q;
		}
	}

	public function loadShortcodeProductsFilter( $args ) {
		$args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();
		$metaQuery = $this->preparePriceFilter(ReqWpf::getVar('min_price', 'all', $this->getPreselectedValue('min_price')), ReqWpf::getVar('max_price', 'all', $this->getPreselectedValue('max_price')));
		if (false != $metaQuery) {
			$args['meta_query'] = array_merge($args['meta_query'], $metaQuery);
			remove_filter( 'posts_clauses', array(WC()->query, 'price_filter_post_clauses' ), 10, 2);
		}
		if (ReqWpf::getVar('pr_stock')) {
			$slugs = explode('|', ReqWpf::getVar('pr_stock'));
			if ($slugs) {
				$metaQuery = array(
					array(
						'key' => '_stock_status',
						'value' => $slugs,
						'compare' => 'IN'
					)
				);
			}
			$args['meta_query'] = array_merge($args['meta_query'], $metaQuery);
		}
		if (ReqWpf::getVar('pr_onsale')) {
			$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
		}
		
		if (ReqWpf::getVar('pr_author')) {
			$author_obj = get_user_by('slug', ReqWpf::getVar('pr_author'));
			if (isset($author_obj->ID)) {
				$args['author'] = $author_obj->ID;
			}
		}
		if (ReqWpf::getVar('vendors')) {
			$author_obj = get_user_by('slug', ReqWpf::getVar('vendors'));
			if (isset($author_obj->ID)) {
				$args['author'] = $author_obj->ID;
			}
		}
		if (ReqWpf::getVar('pr_rating')) {
			$ratingRange = ReqWpf::getVar('pr_rating');
			$range = explode('-', $ratingRange);
			if (intval($range[1] ) !== 5) {
				$range[1] = $range[1] - 0.001;
			}
			$metaQuery = array(
				array( // Simple products type
					'key' => '_wc_average_rating',
					'value' => array($range[0], $range[1]),
					'type' => 'DECIMAL',
					'compare' => 'BETWEEN'
				)
			);
			$args['meta_query'] = array_merge($args['meta_query'], $metaQuery);
		}
		if (ReqWpf::getVar('wpf_count')) {
			$args['posts_per_page'] = ReqWpf::getVar('wpf_count');
		}
		if (ReqWpf::getVar('wpf_order')) {
			$args['order'] = $this->getWpfOrderParam(ReqWpf::getVar('wpf_order'));
			$args['orderby'] = 'title';
		}

		$args['tax_query'] = $this->customProductQueryTaxQuery($args['tax_query']);
		
		if (ReqWpf::getVar('wpf_fbv') && FrameWpf::_()->isPro()) {
			$args = FrameWpf::_()->getModule('woofilterpro')->getModel()->checkBeforeFiltersFrontendArgs($args, array('filtering_by_variations' => 1));
		}

		return $args;
	}
	public function getRenderMode( $id, $settings, $isWidget = true ) {
		if (!in_array($id, $this->renderModes)) {
			$displayShop = !$isWidget;
			$displayCategory = false;
			$displayTag = false;
			$displayAttribute = false;
			$displayMobile = true;

			if (is_admin()) {
				$displayShop = true;
			} else {
				$displayOnPage = empty($settings['settings']['display_on_page']) ? 'shop' : $settings['settings']['display_on_page'];

				if ('specific' === $displayOnPage) {
					$pageList = empty($settings['settings']['display_page_list']) ? '' : $settings['settings']['display_page_list'];
					if (is_array($pageList)) {
						$pageList = isset($pageList[0]) ? $pageList[0] : '';
					}
					$pages = explode(',', $pageList);
					$pageId = $this->getView()->wpfGetPageId();
					if (in_array($pageId, $pages)) {
						$displayShop = true;
						$displayCategory = true;
						$displayTag = true;
					}
				} elseif ( is_shop() || is_product_category() || is_product_tag() || is_customize_preview() ) {
					if ( 'shop' === $displayOnPage || 'both' === $displayOnPage ) {
						$displayShop = true;
					}
					if ( 'category' === $displayOnPage || 'both' === $displayOnPage ) {
						$displayCategory = true;
					}
					if ( 'tag' === $displayOnPage || 'both' === $displayOnPage ) {
						$displayTag = true;
					}
				} elseif ( is_tax() && ( 'both' === $displayOnPage || 'shop' === $displayOnPage ) ) {
					$displayAttribute = true;
				}

				$displayFor = empty($settings['settings']['display_for']) ? '' : $settings['settings']['display_for'];

				$mobileBreakpointWidth = $this->getView()->getMobileBreakpointValue($settings['settings']);
				if ($mobileBreakpointWidth) {
					$displayFor = 'both';
				}
				if ('mobile' === $displayFor) {
					$displayMobile = UtilsWpf::isMobile();
				} else if ('both' === $displayFor) {
					$displayMobile = true;
				} else if ('desktop' === $displayFor) {
					$displayMobile = !UtilsWpf::isMobile();
				}
			}
			$hideWithoutProducts = !empty($settings['settings']['hide_without_products']) && $settings['settings']['hide_without_products'];
			$displayMode = $this->getDisplayMode();
			$mode = 0;

			if ( !$hideWithoutProducts || 'subcategories' != $displayMode ) {
				if ( is_product_category() && $displayCategory && $displayMobile ) {
					$mode = 1;
				} else if ( $this->isWcVendorsPluginActivated() && WCV_Vendors::is_vendor_page() && $displayShop && $displayMobile ) {
					$mode = 7;
				} else if ( is_shop() && $displayShop && $displayMobile ) {
					$mode = 2;
				} else if ( is_product_tag() && $displayTag && $displayMobile ) {
					$mode = 3;
				} else if ( is_tax('product_brand') && $displayShop && $displayMobile ) {
					$mode = 4;
				} else if ( is_tax('pwb-brand') && $displayShop && $displayMobile ) {
					$mode = 5;
				} else if ( $displayAttribute && $displayMobile ) {
					$mode = 6;
				} else if ( $displayShop && $displayMobile && !is_product_category() && !is_product_tag() ) {
					$mode = 10;
				}
			}
			$this->renderModes[$id] = $mode;
		}
		return $this->renderModes[$id];
	}
	private function wpf_get_loop_prop( $prop ) {
		return isset( $GLOBALS['woocommerce_loop'], $GLOBALS['woocommerce_loop'][ $prop ] ) ? $GLOBALS['woocommerce_loop'][ $prop ] : '';
	}

	public function getDisplayMode() {
		if (is_null($this->displayMode)) {
			$mode = '';
			if ( $this->wpf_get_loop_prop('is_search') || $this->wpf_get_loop_prop('is_filtered') ) {
				$display_type = 'products';
			} else {
				$parent_id    = 0;
				$display_type = '';
				if ( is_shop() ) {
					$display_type = get_option('woocommerce_shop_page_display', '');
				} elseif ( is_product_category() ) {
					$parent_id    = get_queried_object_id();
					$display_type = get_term_meta( $parent_id, 'display_type', true );
					$display_type = '' === $display_type ? get_option('woocommerce_category_archive_display', '') : $display_type;
				}

				if ( ( !is_shop() || 'subcategories' !== $display_type ) && 1 < $this->wpf_get_loop_prop('current_page') ) {
					$display_type = 'products';
				}
			}

			if ( '' === $display_type || ! in_array($display_type, array('products', 'subcategories', 'both'), true) ) {
				$display_type = 'products';
			}

			if ( in_array( $display_type, array('subcategories', 'both'), true) ) {
				$subcategories = woocommerce_get_product_subcategories( $parent_id );

				if (empty($subcategories)) {
					$display_type = 'products';
				}
			}
			$this->displayMode = $display_type;
		}
		return $this->displayMode;
	}

	public function addClausesTitleOrder( $args ) {
		global $wpdb;
		$posId = strpos($args['orderby'], '.product_id');
		if (false !== $posId) {
			$idBegin = strrpos( $args['orderby'], ',', ( strlen($args['orderby']) - $posId ) * ( -1 ) );
			if ($idBegin) {
				$args['orderby'] = substr($args['orderby'], 0, $idBegin);
			}
		} else {
			$posId = strpos($args['orderby'], $wpdb->posts . '.ID');
			if (false !== $posId) {
				$idBegin = strrpos($args['orderby'], ',', ( strlen($args['orderby']) - $posId ) * ( -1 ) );
				if ($idBegin) {
					$args['orderby'] = substr($args['orderby'], 0, $idBegin);
				}
			}
		}

		$order = $this->getWpfOrderParam(ReqWpf::getVar('wpf_order'));
		$orderByTitle = "$wpdb->posts.post_title $order";
		$args['orderby'] = ( empty($args['orderby']) ? $orderByTitle : $orderByTitle . ', ' . $args['orderby'] );
		remove_filter('posts_clauses', array($this, 'addClausesTitleOrder'));
		return $args;
	}

	public function addCustomOrder( $args, $customOrder = 'title' ) {
		if (empty($args['orderby'])) {
			$args['orderby'] = $customOrder;
			$args['order'] = 'ASC';
		} else if ($args['orderby'] != $customOrder) {
			if (is_array($args['orderby'])) {
				reset($args['orderby']);
				$key = key($args['orderby']);
				$args['orderby'] = array($key => $args['orderby'][$key]);
			} else {
				$args['orderby'] = array($args['orderby'] => empty($args['order']) ? 'ASC' : $args['order']);
			}
			$args['orderby'][$customOrder] = 'ASC';
			$args['order'] = '';
		}
		return $args;
	}

	private function getWpfOrderParam( $wpfOrder ) {
		$order = 'ASC';
		if ('titled' == $wpfOrder) {
			$order = 'DESC';
		}
		
		return $order;
	}

	public function customProductQueryTaxQuery( $tax_query ) {
		$this->addPreselectedParams();
		$attrFound = array();

		// custom taonomy attr block
		if (!empty($tax_query)) {
			foreach ($tax_query as $i => $tax) {
				if (is_array($tax) && isset($tax['field']) && 'slug' == $tax['field']) {
					$name = str_replace('pa_', 'filter_', $tax['taxonomy']);
					$param = ReqWpf::getVar($name);
					if (!is_null($param)) {
						$attrFound[] = $name;
						$slugs = explode('|', $param);
						if (count($slugs) > 1) {
							$tax_query[$i]['terms'] = $slugs;
							$tax_query[$i]['operator'] = 'IN';
						}
					}
				}
			}
		} else {
			$tax_query = array();
		}

		$getGet = array_merge($this->getPreselectedValue(), ReqWpf::get('get'));

		foreach ($getGet as $key => $value) {
			if (strpos($key, 'filter_cat_list') !== false) {
				$param = ReqWpf::getVar($key, 'all', $this->getPreselectedValue($key));
				if (!is_null($param)) {
					$idsAnd = explode(',', $param);
					$idsOr = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);
					$tax_query[] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => false,
					);
				}
			} elseif (strpos($key, 'filter_cat') !== false) {
				$param = ReqWpf::getVar($key, 'all', $this->getPreselectedValue($key));
				if (!is_null($param)) {
					$idsAnd = explode(',', $param);
					$idsOr = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);
					$tax_query[] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => true,
					);
				}
			} else if (strpos($key, 'product_tag') === 0) {
				$param = ReqWpf::getVar($key);
				$field = 'slug';
				if (is_null($param)) {
					$param = $this->getPreselectedValue($key);
					$field = 'id';
				}
				if (!is_null($param)) {
					$idsAnd = explode(',', $param);
					$idsOr = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);

					$tax_query[] = array(
						'taxonomy' => 'product_tag',
						'field'    => $field,
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => true,
					);
				}
			} else if (strpos($key, 'product_brand') === 0) {
				$param = ReqWpf::getVar($key);
				$field = 'slug';
				if (is_null($param)) {
					$param = $this->getPreselectedValue($key);
					$field = 'id';
				}
				if (!is_null($param)) {
					$idsOr = explode(',', $param);
					$idsAnd = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);

					$tax_query[] = array(
						'taxonomy' => 'product_brand',
						'field'    => $field,
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => true,
					);
				}
			} else if (strpos($key, 'filter_pwb_list') !== false) {
				$param = ReqWpf::getVar($key, 'all', $this->getPreselectedValue($key));
				if (!is_null($param)) {
					$idsAnd = explode(',', $param);
					$idsOr = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);
					$tax_query[] = array(
						'taxonomy' => 'pwb-brand',
						'field'    => 'term_id',
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => false,
					);
				}
			} elseif (strpos($key, 'filter_pwb') !== false) {
				$param = ReqWpf::getVar($key, 'all', $this->getPreselectedValue($key));
				if (!is_null($param)) {
					$idsAnd = explode(',', $param);
					$idsOr = explode('|', $param);
					$isAnd = count($idsAnd) > count($idsOr);
					$tax_query[] = array(
						'taxonomy' => 'pwb-brand',
						'field'    => 'term_id',
						'terms'    => $isAnd ? $idsAnd : $idsOr,
						'operator' => $isAnd ? 'AND' : 'IN',
						'include_children' => true,
					);
				}
			} elseif (strpos($key, 'pr_filter') !== false) {
				$param = ReqWpf::getVar($key, 'all', $this->getPreselectedValue($key));
				if (!is_null($param)) {
					$exeptionalLogic = 'not_in';
					$logic = $this->getAttrFilterLogic();
					if ( ! empty( $logic['delimetr'][$exeptionalLogic] ) ) {
						$ids = explode($logic['delimetr'][$exeptionalLogic], $param);
						$taxonomy = str_replace('pr_filter_', 'pa_', $key);
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $ids,
							'operator' => $logic['loop'][$exeptionalLogic],
						);
					}
				}
			} else if ( strpos($key, 'filter_') === 0 ) {
				$param = ReqWpf::getVar($key);
				if (is_null($param)) {
					$param = $this->getPreselectedValue($key);
					if (!is_null($param)) {
						$idsAnd = explode(',', $param);
						$idsOr = explode('|', $param);
						$isAnd = count($idsAnd) > count($idsOr);
						$attrIds = $isAnd ? $idsAnd : $idsOr;
						$taxonomy = '';
						foreach ($attrIds as $attr) {
							$term = get_term( $attr );
							if ($term) {
								$taxonomy = $term->taxonomy;
								break;
							}
						}
						if (!empty($taxonomy)) {
							$tax_query[] = array(
								'taxonomy' => $taxonomy,
								'field'    => 'id',
								'terms'    => $attrIds,
								'operator' => $isAnd ? 'AND' : 'IN'
							);
						}
					}
				} else if (!in_array($key, $attrFound)) {
					$taxonomy = str_replace('filter_', '', $key);
					$taxonomy = preg_replace('/_\d{1,}/', '', $taxonomy);
					$taxExists = taxonomy_exists($taxonomy);
					if (!$taxExists) {
						$taxonomy = 'pa_' . $taxonomy;
						$taxExists = taxonomy_exists($taxonomy);
					}
					if ($taxExists) {
						$idsAnd = explode(',', $param);
						$idsOr = explode('|', $param);
						$isAnd = count($idsAnd) > count($idsOr);
						$tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $isAnd ? $idsAnd : $idsOr,
							'operator' => $isAnd ? 'AND' : 'IN'
						);
					}
				}
			}
		}

		if (ReqWpf::getVar('pr_featured')) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured'
			);
		}

		return $tax_query;
	}
	public function addAdminTab( $tabs ) {
		$tabs[ $this->getCode() . '#wpfadd' ] = array(
			'label' => esc_html__('Add New Filter', 'woo-product-filter'), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-plus-circle', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() . '_edit' ] = array(
			'label' => esc_html__('Edit', 'woo-product-filter'), 'callback' => array($this, 'getEditTabContent'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() ] = array(
			'label' => esc_html__('Show All Filters', 'woo-product-filter'), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-list', 'sort_order' => 20, //'is_main' => true,
		);
		return $tabs;
	}
	public function getCurrencyPrice( $price ) {
		return apply_filters('raw_woocommerce_price', $price);
	}
	public function preparePriceFilter( $minPrice = null, $maxPrice = null, $rate = null ) {
		if ( !is_null($minPrice) ) {
			$minPrice = str_replace(',', '.', $minPrice);
			if ( !is_numeric($minPrice) ) {
				$minPrice = null;
			}
		}
		if ( !is_null($maxPrice) ) {
			$maxPrice = str_replace(',', '.', $maxPrice);
			if ( !is_numeric($maxPrice) ) {
				$maxPrice = null;
			}
		}

		if ( is_null($minPrice) && is_null($maxPrice) ) {
			return false;
		}
		if (is_null($rate)) {
			$rate = $this->getCurrentRate();
		}
		$metaQuery = array('key' => '_price', 'price_filter' => true, 'type' => 'DECIMAL(20,3)');
		if (is_null($minPrice)) {
			$metaQuery['compare'] = '<=';
			$metaQuery['value'] = $maxPrice / $rate;
		} elseif (is_null($maxPrice)) {
			$metaQuery['compare'] = '>=';
			$metaQuery['value'] = $minPrice / $rate;
		} else {
			$metaQuery['compare'] = 'BETWEEN';
			$metaQuery['value'] = array($minPrice / $rate, $maxPrice / $rate);
		}
		add_filter('posts_where', array($this, 'controlDecimalType'), 9999, 2);

		return array('price_filter' => $metaQuery);
	}
	public function controlDecimalType( $where ) {
		return preg_replace('/DECIMAL\([\d]*,[\d]*\)\(20,3\)/', 'DECIMAL(20,3)', $where);
	}

	public function getCurrentRate() {
		$price = 1000;
		$newPrice = $this->getCurrencyPrice($price);
		return $newPrice / $price;
	}
	public function addHiddenFilterQuery( $query ) {
		$hidden_term = get_term_by('name', 'exclude-from-catalog', 'product_visibility');
		if ($hidden_term) {
			$query[] = array(
				'taxonomy' => 'product_visibility',
				'field' => 'term_taxonomy_id',
				'terms' => array($hidden_term->term_taxonomy_id),
				'operator' => 'NOT IN'
			);
		}
		return $query;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getEditTabContent() {
		$id = ReqWpf::getVar('id', 'get');
		return $this->getView()->getEditTabContent( $id );
	}
	public function getEditLink( $id, $tableTab = '' ) {
		$link = FrameWpf::_()->getModule('options')->getTabUrl( $this->getCode() . '_edit' );
		$link .= '&id=' . $id;
		if (!empty($tableTab)) {
			$link .= '#' . $tableTab;
		}
		return $link;
	}
	public function render( $params ) {
		return $this->getView()->renderHtml($params);
	}
	public function renderProductsList( $params ) {
		return $this->getView()->renderProductsListHtml($params);
	}
	public function renderSelectedFilters( $params ) {
		return FrameWpf::_()->isPro() ? $this->getView()->renderSelectedFiltersHtml($params) : '';
	}
	public function showAdminErrors() {
		// check WooCommerce is installed and activated
		if (!$this->isWooCommercePluginActivated()) {
			// WooCommerce install url
			$wooCommerceInstallUrl = add_query_arg(
				array(
					's' => 'WooCommerce',
					'tab' => 'search',
					'type' => 'term',
				),
				admin_url( 'plugin-install.php' )
			);
			$tableView = $this->getView();
			$tableView->assign('errorMsg',
				$this->translate('For work with "')	. WPF_WP_PLUGIN_NAME . $this->translate('" plugin, You need to install and activate WooCommerce plugin.')
			);
			// check current module
			if (ReqWpf::getVar('page') == WPF_SHORTCODE) {
				// show message
				HtmlWpf::echoEscapedHtml($tableView->getContent('showAdminNotice'));
			}
		}
	}
	public function isWooCommercePluginActivated() {
		return class_exists('WooCommerce');
	}

	public function WC_pif_product_has_gallery( $classes ) {
		global $product;

		$post_type = get_post_type( get_the_ID() );

		if ( wp_doing_ajax() ) {

			if ( 'product' == $post_type ) {

				if ( is_callable( 'WC_Product::get_gallery_image_ids' ) ) {
					$attachment_ids = $product->get_gallery_image_ids();
				} else {
					$attachment_ids = $product->get_gallery_attachment_ids();
				}

				if ( $attachment_ids ) {
					$classes[] = 'pif-has-gallery';
				}
			}
		}

		return $classes;
	}

	public function YITH_hide_add_to_cart_loop( $link, $product ) {

		if ( wp_doing_ajax() ) {

			if ( get_option( 'ywraq_hide_add_to_cart' ) == 'yes' ) {
				return call_user_func_array(array('YITH_YWRAQ_Frontend', 'hide_add_to_cart_loop'), array($link, $product));
			}
		}
		
		return $link;
	}

	/**
	 * Add plugin compatibility wp_query filtering results args
	 *
	 * @link https://iconicwp.com/products/woocommerce-show-single-variations
	 *
	 * @param array $args query args
	 *
	 * @return array
	 */
	public function Iconic_Wssv_Query_Args( $args ) {
		$args = Iconic_WSSV_Query::add_variations_to_shortcode_query($args, array());

		return $args;
	}

	public function getAttributeTerms( $slug ) {
		$terms = array();
		if (empty($slug)) {
			return $terms;
		}
		$args = array('hide_empty' => false);

		if (is_numeric($slug)) {
			$values = get_terms(wc_attribute_taxonomy_name_by_id((int) $slug), $args);
		} else {
			$values = DispatcherWpf::applyFilters('getCustomTerms', array(), $slug, $args);
		}

		if ($values) {
			foreach ($values as $value ) {
				if (!empty($value->term_id)) {
					$terms[$value->term_id] = $value->name;
				}
			}
		}
	
		return $terms;
	}

	public function getFilterTaxonomies( $settings, $calcCategories = false ) {
		$taxonomies = array();
		$forCount = array();
		$forCountWithChildren = array();
		if ($calcCategories) {
			$taxonomies[] = 'product_cat';
		}
		foreach ($settings as $filter) {
			if (empty($filter['settings']['f_enable'])) {
				continue;
			}

			$taxonomy = '';
			switch ($filter['id']) {
				case 'wpfCategory':
					$taxonomy = 'product_cat';
					break;
				case 'wpfTags':
					$taxonomy = 'product_tag';
					break;
				case 'wpfAttribute':
					if (!empty($filter['settings']['f_list'])) {
						$slug = $filter['settings']['f_list'];
						$taxonomy = is_numeric($slug) ? wc_attribute_taxonomy_name_by_id( (int) $slug ) : DispatcherWpf::applyFilters('getCustomAttributeName', $slug);
					}
					break;
				case 'wpfBrand':
					$taxonomy = 'product_brand';
					break;
				case 'wpfPerfectBrand':
					$taxonomy = 'pwb-brand';
					break;
				default:
					break;
			}
			if (!empty($taxonomy)) {
				$taxonomies[] = $taxonomy;
				if (!empty($filter['settings']['f_show_count'])) {
					$forCount[] = $taxonomy;
				}
				if (!empty($filter['settings']['f_show_count_parent_with_children'])) {
					$forCountWithChildren[] = $taxonomy;
				}
			}
		}
		return array('names' => array_unique($taxonomies), 'count' => array_unique($forCount), 'count_with_children' => array_unique($forCountWithChildren));
	}

	public function getFilterExistsTerms( $args, $taxonomies, $calcCategory = null, $prodCatId = false, $querySettings = array() ) {
		if (empty($taxonomies['names'])) {
			return false;
		}

		if (is_null($args)) {
			if (!empty($this->mainWCQuery) && !empty($this->mainWCQuery->query_vars) && 'product' == $this->mainWCQuery->query_vars['post_type']) {
				$args = $this->mainWCQuery->query_vars;
			} else {
				$q = new WP_Query(array('post_type' => 'product', 'meta_query' => array(), 'tax_query' => array()));
				$this->loadProductsFilter($q);
				$args = $this->mainWCQuery->query_vars;
				$args['tax_query'] = $this->customProductQueryTaxQuery($args['tax_query']);
			}
		}

		if (isset($args['taxonomy']) && 'product_brand' == $args['taxonomy'] ) {
			unset($args['taxonomy'], $args['term']);
		}

		if ( is_null( $args ) || empty( $args ) || 'product' != $args['post_type'] && ( is_array($args['post_type'] ) && !in_array( 'product', $args['post_type'] ) ) ) {
			$args = array(
				'post_status' => 'publish',
				'post_type' => 'product',
				'ignore_sticky_posts' => true,
				'tax_query' => array()
			);
		}

		$args['tax_query'] = array_merge($args['tax_query'],
			array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-catalog',
					'operator' => 'NOT IN',
				)
			));

		if ($prodCatId) {
			$args['tax_query'] = !isset($args['tax_query']) ? array() : $args['tax_query'];
			$args['tax_query'] = array_merge($args['tax_query'],
				array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'term_id',
						'terms' => $prodCatId
					)
				));
		}
		
		if (isset($querySettings['product_tag']) && $querySettings['product_tag']) {
			$args['tax_query'] = !isset($args['tax_query']) ? array() : $args['tax_query'];
			$args['tax_query'] = array_merge($args['tax_query'],
				array(
					array(
						'taxonomy' => 'product_tag',
						'field' => 'term_id',
						'terms' => $querySettings['product_tag']
					)
				));
		}
		
		if (isset($querySettings['product_brand']) && $querySettings['product_brand']) {
			$args['tax_query'] = !isset($args['tax_query']) ? array() : $args['tax_query'];
			$args['tax_query'] = array_merge($args['tax_query'],
				array(
					array(
						'taxonomy' => 'product_brand',
						'field' => 'term_id',
						'terms' => $querySettings['product_brand']
					)
				));
		}
		
		if (isset($querySettings['vendors']) && $querySettings['vendors']) {
			$args['author'] = $querySettings['vendors'];
		}

		if (!empty($querySettings['tax_page'])) {
			$args['tax_query'] = array_merge($args['tax_query'],
				array(
					array(
						'taxonomy' => $querySettings['tax_page']['taxonomy'],
						'field' => 'term_id',
						'terms' => $querySettings['tax_page']['term']
					)
				));
		}
		
		$args['nopaging'] = true;
		$args['posts_per_page'] = -1;
		$args['hide_empty'] = 1;
		$args['fields'] = 'ids';

		if (class_exists('Iconic_WSSV_Query')) {
			$args = $this->Iconic_Wssv_Query_Args($args);
		}
		
		$filterLoop = new WP_Query($args);
		$existTerms = array();
		$countProducts = array();
		$termsObjs = array();

		$forCount = $taxonomies['count'];
		$forCountWithChildren = $taxonomies['count_with_children'];
		$isCalcCategory = !is_null($calcCategory);
		$isInStockOnly = get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes';
		$withCount = !empty($forCount) || $isCalcCategory;
		$calcCategories = array();
		$childs = array();
		$names = array();

		if ($filterLoop->have_posts()) {
			$productList = implode(',', $filterLoop->posts);
			$taxonomyList = "'" . implode("','", $taxonomies['names']) . "'";
			global $wpdb;
			$sql = 'SELECT ' . ( $withCount ? '' : 'DISTINCT ' ) . 'tr.term_taxonomy_id, tt.term_id, tt.taxonomy, tt.parent' . ( $withCount ? ', COUNT(*) as cnt' : '' ) . 
				" FROM $wpdb->term_relationships tr 
				INNER JOIN $wpdb->term_taxonomy tt ON (tt.term_taxonomy_id=tr.term_taxonomy_id) " .
				( $withCount && $isInStockOnly ? "INNER JOIN $wpdb->postmeta pm ON (pm.post_id=tr.object_id) " : '' ) .
				'WHERE tr.object_id in (' . $productList . ') AND tt.taxonomy IN (' . $taxonomyList . ')' .
				( $withCount && $isInStockOnly ? ' AND pm.meta_key=\'_stock_status\' AND pm.meta_value=\'instock\' ' : '' );
			if ($withCount) {
				$sql .= ' GROUP BY tr.term_taxonomy_id';
			}

			$sql = DispatcherWpf::applyFilters('addCustomAttributesSql', $sql, array('taxonomies' => $taxonomies['names'], 'withCount' => $withCount, 'productList' => $productList));
			$wpdb->wpf_prepared_query = $sql;
			$termProducts = $wpdb->get_results($wpdb->wpf_prepared_query);
			foreach ($termProducts as $term) {
				$taxonomy = $term->taxonomy;
				$isCat = 'product_cat' == $taxonomy;

				$name = urldecode($taxonomy);
				$names[$name] = $taxonomy;
				if (!isset($existTerms[$name])) {
					$existTerms[$name] = array();
				}

				$termId = $term->term_id;
				$cnt = $withCount ? intval($term->cnt) : 0;
				$existTerms[$name][$termId] = $cnt;

				$parent = $term->parent;
				if ( $isCat && $isCalcCategory && $calcCategory == $parent ) {
					$calcCategories[$termId] = $cnt;
				}

				if (0 != $parent) {
					$children = array($termId);
					do {
						if (!isset($existTerms[$name][$parent])) {
							$existTerms[$name][$parent] = 0;
						}
						if (isset($childs[$parent])) {
							array_merge($childs[$parent], $children);
						} else {
							$childs[$parent] = $children;
						}
						$parentTerm = get_term($parent, $taxonomy);
						$children[] = $parent;
						if ( $parentTerm && isset($parentTerm->parent) ) {
							$parent = $parentTerm->parent;
							if ( $isCat && $isCalcCategory && $calcCategory == $parent ) {
								$calcCategories[$parentTerm->term_id] = 0;
							}
						} else {
							$parent = 0;
						}
					} while (0 != $parent);
				}
			}

			if ($withCount) {
				foreach ($existTerms as $taxonomy => $terms) {
					$allCalc = in_array($taxonomy, $forCount);
					$calcWithChildren = in_array($taxonomy, $forCountWithChildren);
					if ( !( $allCalc || ( $isCalcCategory && 'product_cat' == $taxonomy ) || $calcWithChildren ) ) {
						continue;
					}
					foreach ($terms as $termId => $cnt) {
						if ($calcWithChildren) {
							$query = new WP_Query(array(
								'tax_query' => array(
									array(
										'taxonomy' => 'product_cat',
										'field' => 'id',
										'terms' => $termId,
										'include_children' => true,
									),
								),
								'nopaging' => true,
								'fields' => 'ids',
							));
							$cnt = intval($query->post_count);
							$existTerms[$taxonomy][$termId] = $cnt;
							if (isset($calcCategories[$termId])) {
								$calcCategories[$termId] = $cnt;
							}
						} elseif (empty($cnt)) {
							if ( isset($childs[$termId]) && ( $allCalc || isset($calcCategories[$termId]) ) ) {
								$sql = "SELECT count(DISTINCT tr.object_id)
									FROM $wpdb->term_relationships tr
									INNER JOIN $wpdb->term_taxonomy tt ON (tt.term_taxonomy_id=tr.term_taxonomy_id)
									WHERE tr.object_id in (" . $productList . ")
									AND tt.taxonomy='" . $names[$taxonomy] . "'
									AND tt.term_id in (" . $termId . ',' . implode(',', $childs[$termId]) . ')';
								$wpdb->wpf_prepared_query = $sql;
								$cnt = intval($wpdb->get_var($wpdb->wpf_prepared_query));
								$existTerms[$taxonomy][$termId] = $cnt;
								if (isset($calcCategories[$termId])) {
									$calcCategories[$termId] = $cnt;
								}
							}
						}
					}
				}
			}
		}
		return array('exists' => $existTerms, 'categories' => $calcCategories);
	}
	public function addAjaxFilterForYithWoocompare( $actions ) {
		return array_merge($actions, array('filtersFrontend'));
	}
	public function getAllPages() {
		global $wpdb;
		$allPages = dbWpf::get("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page' AND post_status IN ('publish','draft') ORDER BY post_title");
		$pages = array();
		if (!empty($allPages)) {
			foreach ($allPages as $p) {
				$pages[ $p['ID'] ] = $p['post_title'];
			}
		}
		return $pages;
	}
	
	public function isWcVendorsPluginActivated() {
		return class_exists('WC_Vendors');
	}

	/**
	 * Get logic for filtering.
	 *
	 * @return array
	 */
	public function getAttrFilterLogic( $mode = '' ) {
		$logic = array (
			'display' => array(
				'and' => 'And',
				'or'  => 'Or',
			),
			'loop' => array(
				'and' => 'AND',
				'or'  => 'IN',
			),
			'delimetr' => array(
				'and' => ',',
				'or'  => '|',
			)
		);

		$logic = DispatcherWpf::applyFilters( 'getAttrFilterLogic', $logic );
		return empty($mode) ? $logic : ( isset($logic[$mode]) ? $logic[$mode] : array() );
	}
	
	public function getFilterTagsList() {
		return array( 0 => 'Default', 1 => 'h1', 2 => 'h2', 3 => 'h3', 4 => 'h4', 5 => 'h5' );
	}
	
	public function getCategoriesDisplay() {
		$catArgs = array(
			'orderby' => 'name',
			'order' => 'asc',
			'hide_empty' => false,
		);
		
		$productCategories = get_terms( 'product_cat', $catArgs );
		$categoryDisplay = array();
		$parentCategories = array();
		foreach ($productCategories as $c) {
			if (0 == $c->parent) {
				array_push($parentCategories, $c->term_id);
			}
			$categoryDisplay[$c->term_id] = '[' . $c->term_id . '] ' . $c->name;
		}
		
		return array( $categoryDisplay, $parentCategories );
	}
	
	public function getTagsDisplay() {
		$tagArgs = array(
			'orderby' => 'name',
			'order' => 'asc',
			'hide_empty' => false,
			'parent' => 0
		);
		
		$productTags = get_terms('product_tag', $tagArgs);
		$tagsDisplay = array();
		foreach ($productTags as $t) {
			$tagsDisplay[$t->term_id] = $t->name;
		}
		
		return array( $tagsDisplay );
	}
	
	public function getAttributesDisplay() {
		$productAttr = DispatcherWpf::applyFilters('addCustomAttributes', wc_get_attribute_taxonomies());
		
		$attrDisplay = array(0 => esc_html__('Select...', 'woo-product-filter'));
		$attrTypes = array();
		$attrNames = array();
		foreach ($productAttr as $attr) {
			$attrId = (int) $attr->attribute_id;
			$slug = empty($attrId) ? $attr->attribute_slug : $attrId;
			$attrDisplay[$slug] = $attr->attribute_label;
			$attrTypes[$slug] = isset($attr->custom_type) ? $attr->custom_type : '';
			$attrNames[$slug] = isset($attr->filter_name) ? $attr->filter_name : 'filter_' . $attr->attribute_name;
		}
		
		return array( $attrDisplay, $attrTypes, $attrNames );
	}
	
	public function getRolesDisplay() {
		if (!function_exists('get_editable_roles')) {
			require_once(ABSPATH . '/wp-admin/includes/user.php');
		}
		$rolesMain = get_editable_roles();
		$roles = array();
		
		foreach ($rolesMain as $key => $r) {
			$roles[$key] = $r['name'];
		}
		
		return array( $roles );
	}

	/**
	 * Exlude parent terms from term list
	 *
	 * @param array $termList
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function exludeParentTems( $termList, $taxonomy ) {
		foreach ($termList as $key => $termId) {
			$parents = get_ancestors( $termId, $taxonomy, 'taxonomy' );

			if (is_array($parents)) {
				// remove all parent termsId from main parent list
				foreach ($parents as $parentId) {
					if (array_search($parentId, $termList) !== false) {
						$keyParent = array_search($parentId, $termList);
						unset($termList[$keyParent]);
					}
				}
			}
		}

		return $termList;
	}

	/**
	 * Exlude parent terms from term list
	 *
	 * @param array $termList
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function exludeChildTems( $termList, $taxonomy ) {
		foreach ($termList as $key => $termId) {
			$children = get_term_children( $termId, $taxonomy );
			if (is_array($children)) {
				// remove all parent termsId from main parent list
				foreach ($children as $childId) {
					if (array_search($childId, $termList) !== false) {
						$keyParent = array_search($childId, $termList);
						unset($termList[$keyParent]);
					}
				}
			}
		}

		return $termList;
	}
}
