<?php
class WoofiltersControllerWpf extends ControllerWpf {

	protected $_code = 'woofilters';

	protected function _prepareTextLikeSearch( $val ) {
		$query = '(title LIKE "%' . $val . '%"';
		if (is_numeric($val)) {
			$query .= ' OR id LIKE "%' . (int) $val . '%"';
		}
		$query .= ')';
		return $query;
	}
	public function _prepareListForTbl( $data ) {
		foreach ($data as $key => $row) {
			$id = $row['id'];
			$shortcode = '[' . WPF_SHORTCODE . ' id=' . $id . ']';
			$titleUrl = '<a href="' . esc_url($this->getModule()->getEditLink( $id )) . '">' . esc_html($row['title']) . ' <i class="fa fa-fw fa-pencil"></i></a> <a data-filter-id="' . $id . '" class="wpfDuplicateFilter" href="" title="' . esc_attr__('Duplicate filter', 'woo-product-filter') . '"><i class="fa fa-fw fa-clone"></i></a>';

			$data[$key]['shortcode'] = $shortcode;
			$data[$key]['title'] = $titleUrl;
		}
		return $data;
	}

	public function drawFilterAjax() {
		$res = new ResponseWpf();
		$data = ReqWpf::get('post');
		if (isset($data) && $data) {
			$isPro = FrameWpf::_()->isPro();
			if (!empty($data['settings']['js_editor'])) {
				$data['settings']['js_editor'] = '';
			}

			$html = FrameWpf::_()->getModule('woofilters')->render($data);
			$html .= '<script type="text/javascript">window.wpfFrontendPage.init();' . ( $isPro ? 'window.wpfFrontendPage.eventsFrontendPro();' : '' ) . '</script>';
			$res->setHtml($html);
		} else {
			$res->pushError($this->getModule('woofilters')->getErrors());
		}

		$res->ajaxExec();
	}

	public function save() {
		$res = new ResponseWpf();
		$id = $this->getModel('woofilters')->save(ReqWpf::get('post'));
		if ( false != $id ) {
			$res->addMessage(esc_html__('Done', 'woo-product-filter'));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
			$filter = $this->getModel('woofilters')->getById($id);
			$settings = unserialize($filter['setting_data']);
			$res->addData('filter', $filter);
			$res->addData('filterSettings', $settings);
		} else {
			$res->pushError ($this->getModel('woofilters')->getErrors());
		}
		return $res->ajaxExec();
	}

	public function deleteByID() {
		$res = new ResponseWpf();

		if ($this->getModel('woofilters')->delete(ReqWpf::get('post')) != false) {
			$res->addMessage(esc_html__('Done', 'woo-product-filter'));
		} else {
			$res->pushError($this->getModel('woofilters')->getErrors());
		}
		return $res->ajaxExec();
	}

	public function createTable() {
		$res = new ResponseWpf();
		$id = $this->getModel('woofilters')->save(ReqWpf::get('post'));
		if ( false != $id ) {
			$res->addMessage(esc_html__('Done', 'woo-product-filter'));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
			$filter = $this->getModel('woofilters')->getById($id);
			$settings = unserialize($filter['setting_data']);
			$res->addData('filter', $filter);
			$res->addData('filterSettings', $settings);
		} else {
			$res->pushError ($this->getModel('woofilters')->getErrors());
		}
		return $res->ajaxExec();
	}

	public function filtersFrontend() {
		$res = new ResponseWpf();

		$params = ReqWpf::get('post');
		$filterSettings = UtilsWpf::jsonDecode(stripslashes($params['settings']));
		$settings = UtilsWpf::jsonDecode(stripslashes($params['options']));
		$generalSettings = UtilsWpf::jsonDecode(stripslashes($params['general']));
		$queryvars = UtilsWpf::jsonDecode(stripslashes($params['queryvars']));
		$curUrl = $params['currenturl'];
		$queryvars['posts_per_page'] = isset($filterSettings['count_product_shop']) && !empty($filterSettings['count_product_shop']) ? $filterSettings['count_product_shop'] : $queryvars['posts_per_page'];

		foreach ($settings as $key => $filteringSettings) {
			if ('wpfCategory' == $filteringSettings['id']) {
				$filterOrder = array_search($filteringSettings['uniqId'], array_column($generalSettings, 'uniqId'));
				$logicHierarchical = $generalSettings[$filterOrder]['settings']['f_multi_logic_hierarchical'];
				$isHierarchical = $generalSettings[$filterOrder]['settings']['f_show_hierarchical'];
				$type = $generalSettings[$filterOrder]['settings']['f_frontend_type'];
				$isLogicHierarchical = ( $isHierarchical && in_array( $type, array( 'text', 'multi' ) ) );

				if ($isLogicHierarchical && 'child' == $logicHierarchical) {
					$settings[$key]['settings'] = $this->getModule()->exludeParentTems($settings[$key]['settings'], 'product_cat');
				} elseif ($isLogicHierarchical && 'parent' == $logicHierarchical) {
					$settings[$key]['settings'] = $this->getModule()->exludeChildTems($settings[$key]['settings'], 'product_cat');
				}
			}
		}

		$args = $this->createArgsForFilteringBySettings($settings, $queryvars, $filterSettings, $generalSettings);
		$cacheArgs = $args;

		DispatcherWpf::doAction('beforeFiltersFrontend', $settings);

		$paged = empty($queryvars['paged']) ? 1 : $queryvars['paged'];
		if (empty($params['runbyload']) && empty($queryvars['pagination'])) {
			$paged = 1;
		}
		if (!empty($queryvars['posts_per_row'])) {
			$customNums = $queryvars['posts_per_row'];
			add_filter('loop_shop_columns', function( $num ) use ( $customNums ) {
				return $customNums;
			}, 999);
		}
		$args['paged'] = $paged;

		// other plugin compatibility
		class_exists('WC_pif') && add_filter('post_class', array($this->getModule(), 'WC_pif_product_has_gallery'));
		class_exists('YITH_Request_Quote') && add_filter('woocommerce_loop_add_to_cart_link', array($this->getModule(), 'YITH_hide_add_to_cart_loop'), 10, 2);

		if (class_exists('Iconic_WSSV_Query')) {
			$args = $this->getModule()->Iconic_Wssv_Query_Args($args);
		}

		if (function_exists( 'kute_boutique_woocommerce_setup_loop' )) {
			kute_boutique_woocommerce_setup_loop();
		}

		$categoryHtml = '';
		$productsHtml = '';

		//Prepare params for WooCommerce Shop and Category template variants.
		$shopPageId = wc_get_page_id('shop');
		$currentPageId = isset($queryvars['page_id']) ? $queryvars['page_id'] : 0;
		$categoryPageId = isset($queryvars['product_category_id']) ? $queryvars['product_category_id'] : 0;

		$calcParentCategory = null;
		$showProducts = true;
		if ($shopPageId == $currentPageId) {
			$pageDisplay = get_option('woocommerce_shop_page_display', '');
			if ( 'subcategories' == $pageDisplay || 'both' == $pageDisplay ) {
				$calcParentCategory = 0;
				if ('subcategories' == $pageDisplay) {
					$showProducts = false;
				}
			}
		} else if ($categoryPageId) {
			$archiveDisplay = get_option('woocommerce_category_archive_display', '');
			$termProductCategory = get_term_by('id', $categoryPageId, 'product_cat');

			if ( $termProductCategory && ( 'subcategories' == $archiveDisplay || 'both' == $archiveDisplay ) ) {
				$calcParentCategory = $termProductCategory->term_id;
				if ('subcategories' == $archiveDisplay) {
					$showProducts = false;
				}
			}
		}
		$recount = isset($filterSettings['filter_recount']) && $filterSettings['filter_recount'];
		$removeActions = isset($filterSettings['remove_actions']) && $filterSettings['remove_actions'];
		$displayProductVariations = isset($filterSettings['display_product_variations']) ? $filterSettings['display_product_variations'] : false;

		$module = $this->getModule();
		$taxonomies = $module->getFilterTaxonomies($generalSettings, !is_null($calcParentCategory));
		if (!$recount) {
			$taxonomies['count'] = array();
		}
		$args = DispatcherWpf::applyFilters('beforeFilterExistsTerms', $args, $filterSettings);
		$terms = $module->getFilterExistsTerms($args, $taxonomies, $calcParentCategory);

		$categoryIn = isset($terms['categories']) ? $terms['categories'] : array();
		if (count($categoryIn) > 0) {
			ob_start();
			$catIds = array_keys($categoryIn);
			$cats = get_terms( 'product_cat', array(
				'include' => $catIds
			) );
			foreach ($cats as $category) {
				$cnt = $categoryIn[$category->term_id];
				$category->count = $cnt;
				wc_get_template('content-product_cat.php', array('category' => $category));
			}
			$categoryHtml .= ob_get_clean();
		}

		$loopFoundPost = 0;
		if ( $showProducts || empty($categoryHtml) ) {
			if ($removeActions) {
				remove_all_filters('posts_orderby');
				remove_all_filters('pre_get_posts');
			}
			
			//get products
			$loop = new WP_Query(DispatcherWpf::applyFilters('beforeFiltersFrontendArgs', $args, $filterSettings));
			$loopFoundPost = $loop->found_posts;
			if ($loop->have_posts()) {
				ob_start();
				while ($loop->have_posts()) :
					$loop->the_post();
				
					if ($displayProductVariations) {
						DispatcherWpf::doAction('beforeDisplayProduct');
					}
					
					wc_get_template_part('content', 'product');
				endwhile;
				$productsHtml = ob_get_clean();
			} else {
				$productsHtml = $filterSettings['text_no_products'];
			}
			if (false !== $terms) {
				if ($recount) {
					$productsHtml .= '<script type="text/javascript">wpfChangeFiltersCount(' . json_encode($terms['exists']) . ');</script>';
				}
				$productsHtml .= '<script type="text/javascript">wpfShowHideFiltersAtts(' . json_encode($terms['exists']) . ');</script>';
			}
		}

		ob_start();
		wc_get_template( 'loop/loop-start.php' );

		$loopStart = ob_get_clean();

		//get result count
		ob_start();
		$args = array(
			'total'    => $loopFoundPost,
			'per_page' => $queryvars['posts_per_page'],
			'current'  => 1,//$queryvars['paged'],
		);
		wc_get_template( 'loop/result-count.php', $args );
		$resultCountHtml = ob_get_clean();

		//get pagination
		ob_start();
		$base    =  $queryvars['base'];

		//get query params
		$curUrl = explode( '?', $curUrl );
		$curUrl = isset($curUrl[1]) ? remove_query_arg('product-page', $curUrl[1]) : '';

		$format  = isset($queryvars['format']) ? $queryvars['format'] : '';

		//add quary params to base url
		$fullBaseUrl =  $base . ( strpos($base, '?') === false ? '?' : '&' ) . $curUrl;

		$total = ceil($loopFoundPost / $queryvars['posts_per_page']);

		//after filtering we always start from 1 page
		$args = array(
			'base'         => $fullBaseUrl,
			'format'       => $format,
			'add_args'     => false,
			'current'      => $paged,//1,//$queryvars['paged'],
			'total'        => $total,
			'prev_text'    => '&larr;',
			'next_text'    => '&rarr;',
			'type'         => 'list',
			'end_size'     => 3,
			'mid_size'     => 3,
		);
		$theme = wp_get_theme();
		if ($theme && $theme->get('Name') == 'Impreza') {
			$args[ 'before_page_number'] = '<span>';
			$args['after_page_number'] = '</span>';
			unset($args['type']);
			$links = paginate_links($args);
			HtmlWpf::echoEscapedHtml(_navigation_markup( $links, 'pagination', '' ));
		} else {
			wc_get_template( 'loop/pagination.php', $args );
		}

		$paginationHtml = ob_get_clean();
		wp_reset_postdata();
		$paginationLeer = '';

		add_filter('primer_wc_pagination_args', function( $arg ) use ( $fullBaseUrl ) {
			$arg['base'] = $fullBaseUrl;
			return $arg;
		});
		if (empty($paginationHtml)) {
			ob_start();
			$args['current'] = 1;
			$args['total'] = 2;
			wc_get_template( 'loop/pagination.php', $args);
			$paginationLeer = ob_get_clean();
			wp_reset_postdata();
			if (empty($paginationLeer)) {
				$args['current'] = $paged;
				$args['total'] = $total;
				ob_start();
				global $wp_query;
				$wp_query->max_num_pages = $total;
				wc_get_template( 'loop/pagination.php', $args );
				$paginationHtml = ob_get_clean();
				wp_reset_postdata();
				if (empty($paginationHtml)) {
					$args['current'] = 1;
					$args['total'] = 2;
					ob_start();
					$wp_query->max_num_pages = 2;
					wc_get_template( 'loop/pagination.php', $args );
					$paginationLeer = ob_get_clean();
				}
			}
		}

		$recountPrice = isset($filterSettings['filter_recount_price']) && $filterSettings['filter_recount_price'];
		$prices = array(
			'min_price' => 1000000000,
			'max_price' => 0
		);
		if ( $recountPrice ) {
			$recountArgs = $cacheArgs;
			unset($recountArgs['wpfPrice']);
			$filteredPrices = $this->getView('woofilters')->wpfGetFilteredPrice(true, $recountArgs);
			$prices['max_price'] = $filteredPrices->wpfMaxPrice;
			$prices['min_price'] = $filteredPrices->wpfMinPrice;

			if ( !empty($prices['max_price']) ) {
				$productsHtml .= '<script type="text/javascript">wpfChangePriceFiltersCount(' . json_encode($prices) . ');</script>';
			}
		}

		$res->addData('categoryHtml', $categoryHtml);
		$res->addData('productHtml', $productsHtml);
		$res->addData('paginationHtml', $paginationHtml);
		$res->addData('resultCountHtml', $resultCountHtml);
		$res->addData('loopStartHtml', $loopStart);
		$res->addData('paginationLeerHtml', $paginationLeer);
		$res->addData('prices', $prices);

		return $res->ajaxExec();
	}

	/**
	 * Clone orderby new wp_query parametr.
	 *
	 * @deprecated 1.3.4
	 * @deprecated No longer used by internal code and not recommended.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function order_by_popularity_post_clauses_clone( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
		return $args;
	}

	public function getTaxonomyTerms() {
		$res = new ResponseWpf();
		$slug = ReqWpf::getVar('slug');

		$terms = array();
		$keys = array();
		if (!is_null($slug)) {
			$terms = $this->getModule()->getAttributeTerms($slug);
			$keys = array_keys($terms);
		}
		$res->addData('terms', htmlentities(UtilsWpf::jsonEncode($terms)));
		$res->addData('keys', htmlentities(UtilsWpf::jsonEncode($keys)));
		return $res->ajaxExec();
	}

	public function createArgsForFilteringBySettings( $settings, $queryvars, $filterSettings = array(), $generalSettings = array() ) {
		$queryvars['product_tag'] = isset($queryvars['product_tag']) ? $queryvars['product_tag'] : false;
		$queryvars['product_brand'] = isset($queryvars['product_brand']) ? $queryvars['product_brand'] : false;
		$queryvars['pwb-brand'] = isset($queryvars['pwb-brand']) ? $queryvars['pwb-brand'] : false;
		$queryvars['tax_page'] = isset($queryvars['tax_page']) ? $queryvars['tax_page'] : false;
		$queryvars['vendors'] = isset($queryvars['vendors']) ? $queryvars['vendors'] : false;
		$asDefaultCats = array();
		$settingIds = array_column($settings, 'id');
		$settingCats = array_keys($settingIds, 'wpfCategory');
		$settingsMultiLogic = isset( $filterSettings['f_multi_logic'] ) ? $filterSettings['f_multi_logic'] : 'and';
		if (!count($settingCats)) {
			foreach ($generalSettings as $generalSingle) {
				if ( ( 'wpfCategory' == $generalSingle['id'] ) && $generalSingle['settings']['f_filtered_by_selected'] && !empty($generalSingle['settings']['f_mlist[]']) ) {
					$asDefaultCats = array_merge($asDefaultCats, explode(',', $generalSingle['settings']['f_mlist[]']));
					break;
				}
			}
		}
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'product',
			'paged' => 1,
			'posts_per_page' => $queryvars['posts_per_page'],
			'ignore_sticky_posts' => true,
			'tax_query' => array()
		);
		$args['tax_query'] = $this->getModule()->addHiddenFilterQuery($args['tax_query']);
		if ( ( isset($queryvars['product_category_id']) || $asDefaultCats ) && !$queryvars['product_tag'] && !$queryvars['product_brand']  && !$queryvars['pwb-brand'] ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'id',
				'terms'    => isset($queryvars['product_category_id']) ? $queryvars['product_category_id'] : $asDefaultCats,
				'include_children' => true
			);
		} elseif ($queryvars['product_tag']) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'id',
				'terms'    => $queryvars['product_tag'],
				'include_children' => true
			);
		} elseif ($queryvars['product_brand']) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_brand',
				'field'    => 'id',
				'terms'    => $queryvars['product_brand'],
				'include_children' => true
			);
		} elseif ($queryvars['pwb-brand']) {
			$args['tax_query'][] = array(
				'taxonomy' => 'pwb-brand',
				'field'    => 'id',
				'terms'    => $queryvars['pwb-brand'],
				'include_children' => true
			);
		} elseif ($queryvars['vendors']) {
			$args['author'] = $queryvars['vendors'];
		} elseif (is_array($queryvars['tax_page'])) {
			$args['tax_query'][] = array(
				'taxonomy' => $queryvars['tax_page']['taxonomy'],
				'field'    => 'id',
				'terms'    => $queryvars['tax_page']['term'],
				'include_children' => true
			);
		}

		$temp = array();
		foreach ($settings as $setting) {
			if (!empty($setting['settings'])) {
				$proArgs = DispatcherWpf::applyFilters('getFilterArgsPro', array(), $setting);
				if (!empty($proArgs)) {
					$args = array_merge($args, $proArgs);
					continue;
				}

				switch ($setting['id']) {
					case 'wpfPrice':
						$priceStr = $setting['settings'][0];
						$priceVal = explode(',', $priceStr);
						if ( ( false !== $priceVal[0] ) && $priceVal[1] ) {
							$temp['wpfPrice']['min_price'] = $priceVal[0];
							$temp['wpfPrice']['max_price'] = $priceVal[1];
						}
						break;
					case 'wpfPriceRange':
						$priceStr = $setting['settings'][0];
						$priceVal = explode(',', $priceStr);
						if ( count($priceVal) == 2 && ( false !== $priceVal[0] ) ) {
							$temp['wpfPrice']['min_price'] = ( '' == $priceVal[0] ? null : $priceVal[0] );
							$temp['wpfPrice']['max_price'] = ( '' == $priceVal[1] ? null : $priceVal[1] );
						}
						break;
					case 'wpfSortBy':
						switch ($setting['settings']) {
							case 'title':
								$args['orderby'] = 'title';
								$args['order'] = 'ASC';
								break;
							case 'rand':
								$args['orderby'] = 'rand';
								break;
							case 'date':
								$args['orderby']  = array(
									'date' => 'DESC',
									'ID' => 'ASC',
								);
								$args['order'] = 'DESC';
								break;
							case 'price':
								add_filter( 'posts_clauses', array( wc()->query, 'order_by_price_asc_post_clauses' ) );
								break;
							case 'price-desc':
								add_filter( 'posts_clauses', array( wc()->query, 'order_by_price_desc_post_clauses' ) );
								break;
							case 'popularity':
								$args['orderby'] = 'meta_value_num';
								$args['order'] = 'DESC';
								$args['meta_key'] = 'total_sales';
								break;
							case 'rating':
								$args['meta_key'] = '_wc_average_rating'; // @codingStandardsIgnoreLine
								$args['orderby']  = array(
									'meta_value_num' => 'DESC',
									'ID'             => 'ASC',
								);
								break;
						}
						break;
					case 'wpfCategory':
						$categoryIds = $setting['settings'];
						$temp['wpfCategory'][] = array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $categoryIds,
							'operator' => ( ( isset($setting['logic']) && ( 'or' == $setting['logic'] ) ) || count($categoryIds) <= 1 ? 'IN' : 'AND' ),
							'include_children' => ( isset($setting['children']) && ( '1' == $setting['children'] ) ),
						);
						break;
					case 'wpfPerfectBrand':
						$brandIds = $setting['settings'];
						$args['tax_query'][] = array(
							'taxonomy' => 'pwb-brand',
							'field'    => 'term_id',
							'terms'    => $brandIds,
							'operator' => ( ( isset($setting['logic']) && ( 'or' == $setting['logic'] ) ) || count($brandIds) <= 1 ? 'IN' : 'AND' ),
							'include_children' => ( isset($setting['children']) && ( '1' == $setting['children'] ) ),
						);
						break;
					case 'wpfTags':
						$tagsIdStr = $setting['settings'];
						if ($tagsIdStr) {
							$args['tax_query'][] = array(
								'taxonomy' => 'product_tag',
								'field'    => 'id',
								'terms'    => $tagsIdStr,
								'operator' => ( ( isset($setting['logic'] ) && ( 'or' == $setting['logic'] ) ) || count($tagsIdStr) <= 1 ? 'IN' : 'AND' ),
							);
						}
						break;
					case 'wpfAttribute':
						$attrIds = $setting['settings'];
						if ($attrIds) {
							$taxonomy = '';
							foreach ($attrIds as $attr) {
								$term = get_term( $attr );
								if ($term) {
									$taxonomy = $term->taxonomy;
									break;
								}
							}
							$logic = $this->getModule()->getAttrFilterLogic('loop');
							$operator = isset($setting['logic']) && array_key_exists($setting['logic'], $logic) ? $logic[$setting['logic']] : 'IN';
							$temp['wpfAttribute'][] = array(
								'taxonomy' => $taxonomy,
								'field'    => 'id',
								'terms'    => $attrIds,
								'operator' => $operator,
							);
						}
						break;
					case 'wpfAuthor':
						$authorId = $setting['settings'][0];
						if ($authorId) {
							$args['author'] = $authorId;
						}
						break;
					case 'wpfFeatured':
						$enable = $setting['settings'][0];
						if ('1' === $enable) {
							$args['tax_query'][] = array(
								'taxonomy' => 'product_visibility',
								'field'    => 'name',
								'terms'    => 'featured',
								'operator' => 'IN',
							);
						}
						break;
					case 'wpfOnSale':
						$enable = $setting['settings'][0];
						if ('1' === $enable) {
							$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
						}
						break;
					case 'wpfInStock':
						$stockstatus = $setting['settings'];
						if ($stockstatus) {
							$metaQuery = array(
								'key' => '_stock_status',
								'value' => $stockstatus,
								'compare' => 'IN'
							);
							$args['meta_query'][] = $metaQuery;
						}
						break;
					case 'wpfRating':
						$ratingRange = $setting['settings'];
						if (is_array($ratingRange)) {
							foreach ($ratingRange as $range) {
								$range = strpos($range, '-') !== false ? explode('-', $range) : array(intval($range));
								break;
							}
							if (isset($range[1]) && intval($range[1]) !== 5) {
								$range[1] = $range[1] - 0.001;
							}
							if ($range[0] && isset($range[1]) && $range[1]) {
								$metaQuery = array(
									'key' => '_wc_average_rating',
									'value' => array($range[0], $range[1]),
									'type' => 'DECIMAL',
									'compare' => 'BETWEEN'
								);
								$args['meta_query'][] = $metaQuery;
							} elseif ($range[0]) {
								$metaQuery = array(
									'key' => '_wc_average_rating',
									'value' => $range[0],
									'type' => 'DECIMAL',
									'compare' => '='
								);
								$args['meta_query'][] = $metaQuery;
							}
						}
						break;
					case 'wpfBrand':
						$brandsIdStr = $setting['settings'];
						if ($brandsIdStr) {
							$args['tax_query'][] = array(
								'taxonomy' => 'product_brand',
								'field'    => 'id',
								'terms'    => $brandsIdStr,
								'operator' => ( isset($setting['logic']) && ( 'or' == $setting['logic'] ) ? 'IN' : 'AND' )
							);
						}
						break;
					case 'wpfVendors':
						$vendorIds = $setting['settings'];
						if (!empty($vendorIds)) {
							$args['author__in'] = $vendorIds;
						}
						break;
				}
			}
		}
		DispatcherWpf::doAction('addArgsForFilteringBySettings', $settings);

		if (isset($temp['wpfCategory'])) {
			$temp['wpfCategory']['relation'] = strtoupper($settingsMultiLogic);
			$args['tax_query'][] = $temp['wpfCategory'];
		}
		if (isset($temp['wpfAttribute'])) {
			$temp['wpfAttribute']['relation'] = strtoupper($settingsMultiLogic);
			$args['tax_query'][] = $temp['wpfAttribute'];
		}
		if ( isset($args['tax_query']) && !empty($args['tax_query']) ) {
			$args['tax_query']['relation'] = 'AND';
		}
		if (isset($temp['wpfPrice'])) {
			$args['meta_query'][] = $this->getModule()->preparePriceFilter($temp['wpfPrice']['min_price'], $temp['wpfPrice']['max_price']);
		}
		$sortByTitle = !empty( $filterSettings['sort_by_title'] ) ? $filterSettings['sort_by_title'] : false;
		if ( $sortByTitle && count($settings) > 0 ) {
			$args = $this->getModule()->addCustomOrder($args, 'title');
		}
		if (empty($args['orderby'])) {
			$WC_Query = new WC_Query();
			$vars = $WC_Query->get_catalog_ordering_args();
			if ( is_array($vars) && !empty($vars['orderby']) ) {
				$args['orderby'] = $vars['orderby'];
				$args['order'] = empty($vars['order']) ? 'ASC' : $vars['order'];
			} else {
				$args['orderby'] = 'menu_order title';
				$args['order'] = 'ASC';
			}
		}
		$args = $this->getModule()->addWooOptions($args);

		return $args;
	}
}
