(function ($, app) {
"use strict";
	function WpfFrontendPage() {
		this.$obj = this;
		this.noWoo = this.$obj.checkNoWooPage();
		return this.$obj;
	}

	WpfFrontendPage.prototype.init = (function () {
		var _thisObj = this.$obj;
		_thisObj.isAdminPreview = jQuery('#wpfFiltersEditForm').length > 0;
		_thisObj.eventsPriceFilter();
		_thisObj.disableLeerOptions();
		_thisObj.eventsFrontend();
		_thisObj.changeSlugByUrl();
		_thisObj.runCustomJs();
		_thisObj.addCustomCss();
		_thisObj.chageRangeFieldWidth();
		_thisObj.hideFiltersLoader();
		_thisObj.addSpecificPluginActions();
	});

	WpfFrontendPage.prototype.showFiltersLoader = (function() {
		jQuery('.wpfMainWrapper').each(function () {
			var wrapper = jQuery(this);
			wrapper.css('position','relative');
			if (!wrapper.find('.wpfLoaderLayout').length){
				jQuery('<div/>').addClass('wpfLoaderLayout').appendTo(wrapper);
				wrapper.find('.wpfLoaderLayout').append('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"/>');
			}

			wrapper.find('.wpfLoaderLayout').show();
		});
	});

	WpfFrontendPage.prototype.hideFiltersLoader = (function() {
		jQuery('.wpfMainWrapper').each(function () {
			var wrapper = jQuery(this);

			hideFilterLoader(wrapper);
		});
	});

	WpfFrontendPage.prototype.runCustomJs = (function () {
		var _thisObj = this.$obj;
		jQuery('.wpfMainWrapper').each(function () {
			var wrapper = jQuery(this),
				jsCodeStr = '',
				settings = _thisObj.getFilterMainSettings(wrapper);
			if(settings){
				settings = settings.settings;
				jsCodeStr = settings.js_editor;
			}
			if(jsCodeStr.length > 0){
				try {
					eval(jsCodeStr);
				}catch(e) {
					console.log(e);
				}

			}
		});
	});

	WpfFrontendPage.prototype.addCustomCss = (function () {
		var _thisObj = this.$obj,
			cssCodeStr = '';
		jQuery('style#wpfCustomCss').remove();
		jQuery('.wpfMainWrapper').each(function () {
			var wrapper = jQuery(this),
				customCss = jQuery('style#wpfCustomCss-'+wrapper.attr('data-viewid'));
			if(customCss.length) {
				cssCodeStr += customCss.html();
				customCss.remove();
			}
		});
		if(cssCodeStr.length > 0){
			jQuery('<style type="text/css" id="wpfCustomCss">'+cssCodeStr+'</style>').appendTo('head');
		}
	});

	WpfFrontendPage.prototype.chageRangeFieldWidth = (function () {
		var _thisObj = this.$obj;
		jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"]').each(function () {
			var filter = jQuery(this),
				input1 = filter.find('#wpfMinPrice'),
				input2 = filter.find('#wpfMaxPrice'),
				fontSize1 = input1.css('font-size'),
				fontSize2 = input2.css('font-size'),
				buffer1 = filter.find('.input-buffer-min'),
				buffer2 = filter.find('.input-buffer-max');
			if(fontSize1) buffer1.css('font-size', fontSize1);
			if(fontSize2) buffer2.css('font-size', fontSize2);

			jQuery(buffer1).text(input1.val());
			jQuery(buffer2).text(input2.val());

			if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
				jQuery(input1).width(buffer1.width()+20);
				jQuery(input2).width(buffer2.width()+20);
			} else {
				jQuery(input1).width(buffer1.width()+10);
				jQuery(input2).width(buffer2.width()+10);
			}
		});
	});

	WpfFrontendPage.prototype.eventsPriceFilter = (function () {
		var _thisObj = this.$obj;

		jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"]').each(function () {
			_thisObj.initDefaultSlider(jQuery(this));
		});

		//change price filters
		jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"]').on('wpfPriceChange', function(event){
			var filter = jQuery(this),
				mainWrapper = filter.closest('.wpfMainWrapper');

			mainWrapper.find('.wpfFilterWrapper[data-filter-type="wpfPriceRange"] input').prop('checked', false);
			mainWrapper.find('.wpfFilterWrapper[data-filter-type="wpfPriceRange"] select')
				.val(mainWrapper.find('.wpfFilterWrapper[data-filter-type="wpfPriceRange"] select option:first').val());

			filter.removeClass('wpfNotActive');
			mainWrapper.find('.wpfFilterWrapper[data-filter-type="wpfPriceRange"]').addClass('wpfNotActive');
		});

		//change price range
		jQuery('.wpfFilterWrapper[data-filter-type="wpfPriceRange"] input, .wpfFilterWrapper[data-filter-type="wpfPriceRange"] select').on('change', function(e){
			e.preventDefault();

			jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"]').addClass('wpfNotActive');
		});

	});
	
	WpfFrontendPage.prototype.initDefaultSlider = (function (filter, type) {
		var _thisObj = this.$obj,
			wrapper = filter.closest('.wpfMainWrapper'),
			filterType = typeof type !== 'undefined' ? type : 'price',
			getAttr = filter.data('get-attribute'),
			minInputId = '#wpfMinPrice',
			maxInputId = '#wpfMaxPrice',
			triggerName = 'wpfPriceChange';
		
		if (filterType === 'attr') {
			minInputId = '#wpfMinAttrNum';
			maxInputId = '#wpfMaxAttrNum';
			triggerName = 'wpfAttrSliderChange';
		}
		
		var minSelector = filter.closest('.wpfFilterWrapper').find(minInputId),
			maxSelector = filter.closest('.wpfFilterWrapper').find(maxInputId),
			wpfDataStep = filter.closest('.wpfFilterWrapper').find('#wpfDataStep').val()
		if (wpfDataStep == '0.001') {
			wpfDataStep = '0.00000001';
		}
		wpfDataStep = Number(wpfDataStep);
		var valMin = parseFloat(minSelector.attr('min')),
			valMax = parseFloat(maxSelector.attr('max')),
			curUrl = window.location.href,
			urlParams = _thisObj.findGetParameter(curUrl),
			minPriceGetParams = urlParams.min_price ? parseFloat(urlParams.min_price) : valMin,
			maxPriceGetParams = urlParams.max_price ? parseFloat(urlParams.max_price) : valMax;
		
		if (filterType === 'attr') {
			if (urlParams[getAttr]) {
				var idsAnd = urlParams[getAttr].split(','),
					idsOr = urlParams[getAttr].split('|'),
					isAnd = idsAnd.length > idsOr.length;
				var filterTypeValues = isAnd ? idsAnd : idsOr;
			}
			minPriceGetParams = urlParams[getAttr] ? parseFloat(filterTypeValues[0]) : valMin;
			maxPriceGetParams = urlParams[getAttr] ? parseFloat(filterTypeValues.pop()) : valMax;
		}
			
		var sliderWrapper = filter.find("#wpfSliderRange"),
			autoFilteringEnable = (wrapper.find('.wpfFilterButton').length == 0),
			skin = filter.attr('data-price-skin');
		if(skin === 'default'){
			sliderWrapper.slider({
				range: true,
				orientation: "horizontal",
				min: valMin,
				max: valMax,
				step: wpfDataStep,
				values: [minPriceGetParams, maxPriceGetParams],
				slide: function (event, ui) {
					minSelector.val(ui.values[0]);
					maxSelector.val(ui.values[1]);
					filter.trigger(triggerName);
				},
				start: function () {
					filter.trigger(triggerName);
				},
				stop: function () {
					if(autoFilteringEnable){
						_thisObj.filtering(wrapper);
					}
				}
			});
			minSelector.val(sliderWrapper.slider("values", 0));
			maxSelector.val(sliderWrapper.slider("values", 1));
		}else if(skin === 'skin1'){
			var sliderWrapper = jQuery("#wpfSlider");
			sliderWrapper.attr('value', minPriceGetParams + ';' + maxPriceGetParams);
			var countCall = 0,
				timeOut = 0;
			setTimeout(function() {
				sliderWrapper.slideregor({
					from: valMin,
					to: valMax,
					limits: true,
					step: wpfDataStep,
					skin: "round",
					onstatechange : function(value) {
						countCall++;
						if(countCall > 2){
							var _this = this,
								values = _this.getValue().split(';');
							minSelector.val(values[0]);
							maxSelector.val(values[1]);
							if(!sliderWrapper.closest('.wpfFilterWrapper').hasClass('wpfNotActiveSlider')){
								sliderWrapper.closest('.wpfFilterWrapper').removeClass('wpfNotActive');
								clearTimeout(timeOut);
								timeOut = setTimeout(function(){
									filter.trigger(triggerName);
									if(ajaxModeEnable){
										_thisObj.filtering(wrapper);
									}
								},1000);
							}
							
						}
					}
				});
			}, 200);
		}
	});

	// add/remove get query param
	WpfFrontendPage.prototype.QStringWork = (function ($attr, $value, $noWooPage, $filterWrapper, $type) {
		$noWooPage = false;
		if (window.wpfAdminPage) {
			$noWooPage = true;
		}
		if($type === 'change'){
			var curUrl = changeUrl($attr, $value, $noWooPage, $filterWrapper );
			$filterWrapper.attr('data-hide-url', decodeURI(curUrl));
		}else if($type === 'remove'){
			var curUrl = removeQString($attr, $noWooPage, $filterWrapper);
			$filterWrapper.attr('data-hide-url', decodeURI(curUrl));
		}
	});

	WpfFrontendPage.prototype.eventChangeFilter = (function (e) {
		var _thisObj = this.$obj,
			_this = jQuery(e.target),
			mainWrapper = _this.closest('.wpfMainWrapper'),
			settings = _thisObj.getFilterMainSettings(mainWrapper);

		_this.closest('.wpfFilterWrapper').removeClass('wpfNotActive');
		if(typeof(_thisObj.eventChangeFilterPro) == 'function') {
			_thisObj.eventChangeFilterPro(_this, settings);
		}
		if(mainWrapper.find('.wpfFilterButton').length == 0) {
			_thisObj.filtering(mainWrapper);
		}
	});


	WpfFrontendPage.prototype.eventsFrontend = (function () {
		var _thisObj = this.$obj;

		jQuery('.wpfMainWrapper').find('select[multiple]').each(function(){
			var select = jQuery(this),
				selectAll = select.attr('data-placeholder'),
				search = JSON.parse(select.attr('data-search'));
			select.multiselect({search: search.show, columns: 1, placeholder: selectAll ? selectAll : 'Select options', optionAttributes: ['style','data-term-id'], searchOptions: {'default': search.placeholder}});
		});

		if(jQuery('.wpfFilterWrapper[data-filter-type="wpfSortBy"]').length == 0) {
			jQuery('.woocommerce-ordering').css('display', 'block');
		}

		//if no enabled filters hide all html
		if(jQuery('.wpfFilterWrapper').length < 1){
			jQuery('.wpfMainWrapper').addClass('wpfHidden');
		}

		//Start filtering
		jQuery('.wpfFilterButton').on('click', function(e){
			e.preventDefault();
			_thisObj.filtering(jQuery(this).closest('.wpfMainWrapper'));
		});

		//Clear filters
		jQuery('.wpfClearButton').on('click', function(e){
			e.preventDefault();
			var $filterWrapper = jQuery(this).closest('.wpfMainWrapper');
			_thisObj.clearFilters($filterWrapper.find('.wpfFilterWrapper'), true);
			_thisObj.filtering($filterWrapper);
		});

		//price range choose only one checkbox
		jQuery('.wpfFilterWrapper[data-filter-type="wpfPriceRange"] .wpfFilterContent input').on('change', function (e) {
			e.preventDefault();
			var input = jQuery(this),
				inputs = input.closest('.wpfFilterWrapper').find('input');
			if(input.is(":checked")){
				inputs.prop('checked', false);
				input.prop('checked', true);
			}
		});

		//category/brand list choose only one checkbox
		jQuery('.wpfFilterWrapper[data-filter-type="wpfCategory"], .wpfFilterWrapper[data-filter-type="wpfPerfectBrand"]').each(function() {
			var categoryFilter = jQuery(this),
				displayType = categoryFilter.data('display-type'),
				categoryMulti = displayType == 'multi';

			categoryFilter.find('.wpfFilterContent input').on('change', function (e) {
				e.preventDefault();
				var input = jQuery(this);
				if(categoryMulti) {
					var mainWrapper = input.closest('.wpfMainWrapper'),
						filterWrapper = input.closest('.wpfFilterWrapper'),
						expandSelectedToChild = _thisObj.getFilterParam('f_multi_extend_parent_select', mainWrapper, filterWrapper);

					if(expandSelectedToChild && input.is(':checked')) {
						input.closest('li').find('ul input').prop('checked', true);
					}

					if (expandSelectedToChild && ! input.is(':checked') ) {
						input.closest('li').find('ul input').prop('checked', false);
					}
				}
			});
		});

		//rating choose only one checkbox
		jQuery('.wpfFilterWrapper[data-filter-type="wpfRating"] .wpfFilterContent input').on('change', function (e) {
			e.preventDefault();
			var input = jQuery(this),
				inputs = input.closest('.wpfFilterWrapper').find('input');
			if(input.is(":checked")){
				inputs.prop('checked', false);
				input.prop('checked', true);
			}
		});

		//after change input or dropdown make this filter active
		//check if ajax mode enable and filtering on filter elements change
		jQuery('.wpfFilterWrapper select, .wpfFilterWrapper input:not(.passiveFilter)').on('change', function (e) {
			e.preventDefault();
			var isExeptionCase = _thisObj.checkExeptionCasesBeforeFiltering(this);

			if (!isExeptionCase) {
				setTimeout(function() {
					_thisObj.eventChangeFilter(e);
				}, 100);
			}
		});

		jQuery('.wpfFilterWrapper input:not(.passiveFilter)').on('change', function (e) {
			e.preventDefault();
			//check if input change move to top
			_thisObj.moveCheckedToTop(jQuery(this));
			// check multy or single input (radio or checkbox)
			_thisObj.detectSingleCheckbox(jQuery(this))
		});

		//search field work
		jQuery('.wpfFilterWrapper .wpfSearchFieldsFilter').on('keyup', function (e) {
			var _this = jQuery(this),
				wrapper = _this.closest('.wpfFilterWrapper'),
				searchVal = _this.val().toLowerCase();
			wrapper.find('.wpfFilterContent li:not(.wpfShowFewerWrapper)').filter(function() {
				if (jQuery(this).find('.wpfValue').text().toLowerCase().indexOf(searchVal) > -1) {
					jQuery(this).removeClass('wpfSearchHidden');
				} else {
					jQuery(this).addClass('wpfSearchHidden');
				}
			});
			if(typeof(_thisObj.initShowMore) == 'function') {
				_thisObj.initShowMore(wrapper.find('.wpfFilterVerScroll'));
			}
		});
		jQuery('.wpfFilterWrapper .wpfSearchFieldsFilter').on('change', function (e) {
			jQuery(this).closest('.wpfFilterWrapper').find('.wpfFilterContent li.wpfSearchHidden .wpfCheckbox input').prop('checked', false);
		});

		//uncheck one slug
		jQuery('body').off('click', '.wpfSlugDelete').on('click', '.wpfSlugDelete', function(){
			var _this = jQuery(this),
				wrapper = _this.closest('.wpfSlug'),
				filterType = wrapper.attr('data-filter-type'),
				filterAttr = wrapper.attr('data-get-attribute'),
				filterWrapper = false;

			jQuery('.wpfFilterWrapper[data-filter-type="'+filterType+'"][data-get-attribute="'+filterAttr+'"]').each(function(){
				var $this = jQuery(this),
					filterType = $this.attr("data-filter-type");
				if (filterType == 'wpfPrice' || filterType == 'wpfPriceRange') {
					_thisObj.clearFilters($("[data-filter-type='wpfPrice']:not(.wpfSelectedParameter)"));
					_thisObj.clearFilters($("[data-filter-type='wpfPriceRange']:not(.wpfSelectedParameter)"));
				} else {
					_thisObj.clearFilters($this);
				}
				if(filterWrapper == false) {
					filterWrapper = $this.closest('.wpfMainWrapper');
				}
			});
			if(filterWrapper != false) {
				_thisObj.filtering(filterWrapper);
			}
		});

		jQuery('body').off('click', '.wpfFilterWrapper .wpfTitleToggle').on('click', '.wpfFilterWrapper .wpfTitleToggle',  function(e){
			e.preventDefault();
			var _this = jQuery(this),
				content = _this.closest('.wpfFilterWrapper').find('.wpfFilterContent'),
				toggle = _this.closest('.wpfFilterWrapper').find('i.wpfTitleToggle'),
				isPlus = toggle.hasClass('fa-plus');
			if(isPlus) {
				_thisObj.openFilterToggle(toggle, content, true);
			} else {
				_thisObj.closeFilterToggle(toggle, content, true);
			}
		});

		jQuery('.wpfMainWrapper .wpfFilterWrapper').each(function() {
			var _this = jQuery(this),
				wrapper = _this.closest('.wpfMainWrapper'),
				settings = _thisObj.getFilterMainSettings(wrapper),
				isMobile = false,
				screenSize = jQuery(window).width();

			if (settings.settings !== undefined) {
				var isMobileBreakpoin = settings.settings.desctop_mobile_breakpoint_switcher,
					mobileBreakpoinWidth = settings.settings.desctop_mobile_breakpoint_width,
					mobileBreakpoinWidth = settings.settings.desctop_mobile_breakpoint_width,

					displayFor = settings.settings.display_for,

					filterWidthDesktop          = settings.settings.filter_width,
					filterWidthDesktopUnit      = settings.settings.filter_width_in,
					filterWidthMobile           = settings.settings.filter_width_mobile,
					filterWidthMobileUnit       = settings.settings.filter_width_in_mobile,
					filterBlockWidthDesktop     = settings.settings.filter_block_width,
					filterBlockWidthDesktopUnit = settings.settings.filter_block_width_in,
					filterBlockWidthMobile      = settings.settings.filter_block_width_mobile,
					filterBlockWidthMobileUnit  = settings.settings.filter_block_width_in_mobile;

				if (isMobileBreakpoin && '0' !== isMobileBreakpoin && mobileBreakpoinWidth && '0' !== mobileBreakpoinWidth) {
					if (screenSize <= mobileBreakpoinWidth) {
						isMobile = true;
					}

					// "Filter Width" and "Filter Block Width" options
					if (isMobile && filterBlockWidthMobile != '0') {
						wrapper.css('width', filterWidthMobile+filterWidthMobileUnit);
						_this.css('width', filterBlockWidthMobile+filterBlockWidthMobileUnit);
						if (filterBlockWidthMobile+filterBlockWidthMobileUnit != '100%') {
							_this.css('float', 'left');
						}
					} else if (!isMobile && filterBlockWidthDesktop != '0') {
						wrapper.css('width', filterWidthDesktop+filterWidthDesktopUnit);
						_this.css('width', filterBlockWidthDesktop+filterBlockWidthDesktopUnit);
						if (filterBlockWidthDesktop+filterBlockWidthDesktopUnit != '100%') {
							_this.css('float', 'left');
						}
					}

					// "Display filter on"  option
					if (isMobile && displayFor == 'desktop') {
						wrapper.hide();
					} else if (!isMobile && displayFor == 'mobile') {
						wrapper.hide();
					}

					// filters title
					_this.find('.wpfFilterTitle[data-show-on-mobile]').each(function() {
						jQuery(this).closest('.wpfFilterMainWrapper').find('wpfLoaderLayout').show();
							var showDesctop = jQuery(this).data('show-on-desctop'),
								showMobile = jQuery(this).data('show-on-mobile'),
								content = jQuery(this).closest('.wpfFilterWrapper').find('.wpfFilterContent'),
								toggle = jQuery(this).closest('.wpfFilterWrapper').find('i.wpfTitleToggle'),
								title = jQuery(this).find('.wfpTitle');

						if (isMobile) {
							if (showMobile == 'yes_open') {
								_thisObj.openFilterToggle(toggle, content);
							} else if(showMobile == 'yes_close') {
								_thisObj.closeFilterToggle(toggle, content);
							} else if(showMobile == 'no') {
								_thisObj.openFilterToggle(toggle, content);
								toggle.remove();
								title.remove();
							}
						} else {
							if (showDesctop == 'yes_open') {
								_thisObj.openFilterToggle(toggle, content);
							} else if(showDesctop == 'yes_close') {
								_thisObj.closeFilterToggle(toggle, content);
							} else if(showDesctop == 'no') {
								_thisObj.openFilterToggle(toggle, content);
								toggle.remove();
								title.remove();
							}
						}
					});
				}
			}
		});

		jQuery('body').off('click','.wpfFilterWrapper .wfpTitle').on('click','.wpfFilterWrapper .wfpTitle', function(){
			var chevron = $(this).siblings('.fa');
			if(chevron) chevron.trigger('click');
		});

		jQuery('body').off('click', '.wpfFilterWrapper .wpfBlockClear').on('click', '.wpfFilterWrapper .wpfBlockClear',  function(){
			var parent = jQuery(this).closest(".wpfFilterWrapper"),
				parentAttr = parent.attr("data-filter-type");
			if (parentAttr == 'wpfPrice' || parentAttr == 'wpfPriceRange') {
				_thisObj.clearFilters($("[data-filter-type='wpfPrice']:not(.wpfSelectedParameter)"));
				_thisObj.clearFilters($("[data-filter-type='wpfPriceRange']:not(.wpfSelectedParameter)"));
			} else {
				_thisObj.clearFilters(parent);
			}
			_thisObj.filtering(parent.closest('.wpfMainWrapper'));
		});

		jQuery('body').off('wpffiltering').on('wpffiltering', function () {
			_thisObj.setPagination(1);
			_thisObj.filtering();
			_thisObj.setPagination(0);
		});

		//click on new pagination link with page number
		jQuery('body').off('click', '.wpfNoWooPage .woocommerce-pagination li .page-numbers').on('click', '.wpfNoWooPage .woocommerce-pagination li .page-numbers', function (e) {
			e.preventDefault();
			var _this = jQuery(this),
				paginationWrapper = _this.closest('.woocommerce-pagination'),
				currentNumber = paginationWrapper.find('.current').text();
			if(!_this.hasClass('next') && !_this.hasClass('prev') ){
				var number = _this.text();
			}else if(_this.hasClass('next')){
				var number = parseInt(currentNumber) + 1;
			}else if(_this.hasClass('prev')){
				var number = (parseInt(currentNumber) - 1) < 1 ? parseInt(currentNumber) - 1 : 1;
			}
			var wrapper = jQuery('.wpfMainWrapper').first(),
				$queryVars = wrapper.attr('data-settings');
			try{
				var settings = JSON.parse($queryVars);
			}catch(e){
				var settings = false;
			}
			if(settings){
				settings.paged = number;
				settings.pagination = 1;
				wrapper.attr('data-settings', JSON.stringify(settings) );
			}

			// todo: testing for two+ filters on page
			_thisObj.filtering( jQuery('.wpfMainWrapper') );
			_thisObj.setPagination(0);
		});

	});

	WpfFrontendPage.prototype.checkExeptionCasesBeforeFiltering = (function (filterInput) {
		var isExeption = false;
		// exeption when custom price do not set but input activated
		if ( jQuery(filterInput).parent().hasClass('wpfPriceCheckboxCustom')) {
			var customPriceWrapper = jQuery(filterInput).closest('li'),
				customMin = customPriceWrapper.find('input[name=wpf_custom_min]').val(),
				customMax = customPriceWrapper.find('input[name=wpf_custom_max]').val();

			if (!customMin && !customMax) {
				isExeption = true;
			}
		}

		return isExeption;
	});

	WpfFrontendPage.prototype.detectSingleCheckbox = (function (checkedInput) {
		var filterWrapper = checkedInput.closest('.wpfFilterWrapper'),
			displayType = filterWrapper.data('display-type'),
			filterType = filterWrapper.data('filter-type');

		if (filterType == 'wpfCategory' || filterType == 'wpfPerfectBrand'|| filterType == 'wpfBrand') {
			var isOne = displayType == 'list';
		} else {
			var isOne = displayType == 'radio';
		}

		if (isOne) {
			var inputs = filterWrapper.find('input');

			if (checkedInput.is(':checked')) {
				inputs.prop('checked', false);
				checkedInput.prop('checked', true);
			}
		}
	});

	WpfFrontendPage.prototype.moveCheckedToTop = (function (checkedInput) {
		var _thisObj = this.$obj;
		setTimeout(function() {
			var checkboxWrapper = checkedInput.closest('li'),
				checkboxesWrapper = checkedInput.closest('.wpfFilterVerScroll'),
				mainWrapper = checkedInput.closest('.wpfMainWrapper'),
				filterWrapper = checkedInput.closest('.wpfFilterWrapper'),
				isHierarchical = filterWrapper.data('show-hierarchical'),
				settings = _thisObj.getFilterMainSettings(mainWrapper);
			if(settings && !isHierarchical) {
				settings = settings.settings;
				var checkedItemsTop = settings.checked_items_top === '1',
					isExeptionCase = _thisObj.checkExeptionCasesBeforeFiltering(checkedInput);
				if(checkedItemsTop && !isExeptionCase){
					if (checkedInput.is(":checked")) {
						checkboxesWrapper.prepend(checkboxWrapper);
					} else {
						checkboxesWrapper.append(checkboxWrapper);
					}
				}
			}
		}, 200);
	});

	WpfFrontendPage.prototype.closeFilterToggle = (function (toggle, content, isTimeout = false) {
		toggle.removeClass('fa-minus');
		toggle.addClass('fa-plus');
		content.addClass('wpfBlockAnimated');
		if (isTimeout) {
			setTimeout(function() {
				if(content.hasClass('wpfBlockAnimated')) content.addClass('wpfHide');
			}, 10);
		} else {
			if(content.hasClass('wpfBlockAnimated')) content.addClass('wpfHide');
		}
	});

	WpfFrontendPage.prototype.openFilterToggle = (function (toggle, content, isTimeout = false) {
		toggle.removeClass('fa-plus');
		toggle.addClass('fa-minus');
		content.removeClass('wpfHide');
		if (isTimeout) {
			setTimeout(function() {
				if(!content.hasClass('wpfHide')) content.removeClass('wpfBlockAnimated');
			}, 400);
		} else {
			if(!content.hasClass('wpfHide')) content.removeClass('wpfBlockAnimated');
		}
	});

	WpfFrontendPage.prototype.setPagination = (function (pagination) {
		var wrapper = jQuery('.wpfMainWrapper').first(),
			$queryVars = wrapper.attr('data-settings');
		try{
			var settings = JSON.parse($queryVars);
		}catch(e){
			var settings = false;
		}
		if(settings){
			settings.pagination = pagination;
			wrapper.attr('data-settings', JSON.stringify(settings) );
		}
	});

	WpfFrontendPage.prototype.filtering = (function ($filterWrapper) {
		var _thisObj = this.$obj;
		_thisObj.chageRangeFieldWidth();
		if(_thisObj.isAdminPreview) return;

		_thisObj.createOverlay();

		if(typeof $filterWrapper == 'undefined' || $filterWrapper.length == 0) {
			$filterWrapper = jQuery('.wpfMainWrapper').first();
		}

		var $filtersDataBackend = [],
			$filtersDataFrontend = [],
			noWooPage = _thisObj.noWoo;

		$filterWrapper.find('.wpfFilterWrapper:not(.wpfNotActive), .wpfFilterWrapper.wpfPreselected').each(function () {
			var $filter = jQuery(this),
				filterType = $filter.attr('data-filter-type'),
				filterName = $filter.attr('data-get-attribute'),
				uniqId = $filter.attr('data-uniq-id'),
				allSettings = _thisObj.getFilterOptionsByType($filter, filterType),
				valueToPushBackend = {},
				valueToPushFrontend = {},
				logic = $filter.attr('data-query-logic');
			if(typeof logic === 'undefined') {
				logic = 'or';
			}
			var withChildren = $filter.attr('data-query-children');
			if(typeof withChildren === 'undefined') {
				withChildren = '1';
			}

			if (allSettings['backend'].length && typeof allSettings['backend'] !== 'undefined' || filterType === 'wpfSearchText') {
				valueToPushBackend['id'] = filterType;
				valueToPushBackend['uniqId'] = uniqId;
				valueToPushBackend['logic'] = logic;
				valueToPushBackend['children'] = withChildren;
				valueToPushBackend['settings'] = allSettings['backend'];
				valueToPushBackend['name'] = filterName;
				$filtersDataBackend.push(valueToPushBackend);
			}

			valueToPushFrontend['id'] = filterType;

			var logicDelimetrList = {
				or: '|',
				and: ',',
				not_in: ';'
			}

			valueToPushFrontend['delim'] = logicDelimetrList[logic];
			valueToPushFrontend['children'] = withChildren;
			valueToPushFrontend['settings'] = allSettings['frontend'];
			valueToPushFrontend['name'] = filterName;

			$filtersDataFrontend.push(valueToPushFrontend);
		});
		if(jQuery('.wpfFilterWrapper[data-filter-type="wpfSortBy"]').length == 0) {
			var $wooCommerceSort = jQuery('.woocommerce-ordering select');
			if($wooCommerceSort.length > 0) {
				$filtersDataBackend.push({'id': 'wpfSortBy', 'settings': $wooCommerceSort.eq(0).val()});
			}
		}

		_thisObj.changeUrlByFilterParams($filtersDataFrontend);
		_thisObj.changeSlugByUrl();
		_thisObj.QStringWork('wpf_reload', '', noWooPage, $filterWrapper, 'remove');
		var settings = _thisObj.getFilterMainSettings($filterWrapper);
		if(settings && settings.settings.enable_ajax !== '1'){
			location.reload();
			return;
		}

		//get paged params from html
		var $queryVars = $filterWrapper.attr('data-settings'),
			$generalSettings = $filterWrapper.attr('data-filter-settings');

		$generalSettings = JSON.parse($generalSettings);
		var $settings = {
			'filter_recount' : $generalSettings['settings']['filter_recount'] && ($generalSettings['settings']['filter_recount'] == '1') ? true : false,
			'filter_recount_price' : $generalSettings['settings']['filter_recount_price'] && ($generalSettings['settings']['filter_recount_price'] == '1') ? true : false,
			'text_no_products' : $generalSettings['settings']['text_no_products'] ? $generalSettings['settings']['text_no_products'] : '',
			'sort_by_title' : $filtersDataBackend.length && $generalSettings['settings']['sort_by_title'] && ($generalSettings['settings']['sort_by_title'] == '1') ? true : false,
			'count_product_shop': $generalSettings['settings']['count_product_shop'] ? parseInt($generalSettings['settings']['count_product_shop']) : 0,
			'f_multi_logic': $generalSettings['settings']['f_multi_logic'] ? $generalSettings['settings']['f_multi_logic'] : 'and',
			'remove_actions': $generalSettings['settings']['remove_actions'] && ($generalSettings['settings']['remove_actions'] == '1') ? true : false,
			'filtering_by_variations': $generalSettings['settings']['filtering_by_variations'] && ($generalSettings['settings']['filtering_by_variations'] == '1') ? true : false,
			'display_product_variations': $generalSettings['settings']['display_product_variations'] && ($generalSettings['settings']['display_product_variations'] == '1') ? true : false
		};

		if($settings['count_product_shop'] > 0) {
			_thisObj.QStringWork('wpf_count', $settings['count_product_shop'], noWooPage, $filterWrapper, 'change');
		}
		if($settings['sort_by_title']) {
			_thisObj.QStringWork('wpf_order', 'title', noWooPage, $filterWrapper, 'change');
		}
		if($settings['filtering_by_variations']) {
			_thisObj.QStringWork('wpf_fbv', 1, noWooPage, $filterWrapper, 'change');
			
			if($settings['display_product_variations']) {
				_thisObj.QStringWork('wpf_dpv', 1, noWooPage, $filterWrapper, 'change');
			}
		}
		
		var customEvent = document.createEvent('Event');
		customEvent.initEvent('wpfAjaxStart', false, true);
		document.dispatchEvent(customEvent);
		
		//ajax call to server
		_thisObj.sendFiltersOptionsByAjax($filtersDataBackend, $queryVars, $settings, $generalSettings);
	});

	WpfFrontendPage.prototype.createOverlay = (function () {
		jQuery('#wpfOverlay').css({'display':'block'});
	});

	WpfFrontendPage.prototype.removeOverlay = (function () {
		jQuery('#wpfOverlay').css({'display':'none'});
	});

	WpfFrontendPage.prototype.clearFilters = (function (filter, clearAll) {
		var _thisObj = this.$obj,
			noWooPage = _thisObj.noWoo,
			clearAll = typeof clearAll == 'undefined' ? false : true;

		(filter ? filter : jQuery('.wpfFilterWrapper')).each(function () {
			var $filter = jQuery(this),
				$filterWrapper = $filter.closest('.wpfMainWrapper'),
				filterAttribute = $filter.attr('data-get-attribute'),
				filterType = $filter.attr('data-display-type');

			filterAttribute = filterAttribute.split(",");
			var count = filterAttribute.length;
			for(var i = 0; i<count; i++){
				_thisObj.QStringWork(filterAttribute[i], '', noWooPage, $filterWrapper, 'remove');
			}

			if($filter.hasClass('wpfHidden')){
				$filter.removeClass('wpfNotActive');
			} else {
				$filter.find('input').prop('checked', false);
				if(filterType == 'mul_dropdown') {
					$filter.find('select').val('');
					$filter.find('select.jqmsLoaded').multiselect('reload');
				} else if(filterType == 'text') {
					$filter.find('input').val('');
				} else {
					// $filter.find('select option:first').attr('selected', true);
					$filter.find("select").val($filter.find("select option:first").val());
				}
				$filter.addClass('wpfNotActive');
			}

			if($filter.attr('data-filter-type') === 'wpfPrice'){
				var min = $filter.find('#wpfMinPrice').attr('min'),
					max = $filter.find('#wpfMaxPrice').attr('max');
				$filter.find('#wpfMinPrice').val(min);
				$filter.find('#wpfMaxPrice').val(max);

				jQuery( "#wpfSliderRange" ).slider( "option", "values", [ min, max ] );
			}

			if(typeof(_thisObj.clearFiltersPro) == 'function') {
				_thisObj.clearFiltersPro($filter);
			}
			if(typeof(_thisObj.eventChangeFilterPro) == 'function') {
				_thisObj.eventChangeFilterPro($filter);
			}
			if(clearAll) {
				_thisObj.QStringWork('wpf_order', '', noWooPage, $filterWrapper, 'remove');
			}
		});
	});

	WpfFrontendPage.prototype.getFilterMainSettings = (function ($selector) {
		var settingsStr = $selector.attr('data-filter-settings');
		try{
			var settings = JSON.parse(settingsStr);
		}catch(e){
			var settings = false;
		}
		return settings;
	});

	WpfFrontendPage.prototype.getFilterParam = (function (paramSlug, mainWrapper, filterWrapper) {
		var paramValue = null,
			_thisObj = this.$obj,
			mainSettings = _thisObj.getFilterMainSettings(mainWrapper),
			filterOder = _thisObj.getFilterOrderInMainWrapper(paramSlug, mainWrapper, filterWrapper);

		if (mainSettings.settings.filters.order && filterOder) {
			var filtersOderList = JSON.parse(mainSettings.settings.filters.order),
				filterParamList = filtersOderList[filterOder-1].settings;

			if ( typeof filterParamList[paramSlug] !== undefined ) {
				paramValue = filterParamList[paramSlug];
			}
		}

		return paramValue;
	});

	WpfFrontendPage.prototype.getFilterOrderInMainWrapper = (function (paramSlug, mainWrapper, filterWrapper) {
		var i  = 1,
			order = 0;
		// get filter order in filter main  wrapper
		mainWrapper.find('.wpfFilterWrapper').each(function() {
			if (jQuery(this).attr('id') == filterWrapper.attr('id')) {
				order = i;
			}
			i++;
		});

		return order;
	});

	WpfFrontendPage.prototype.checkNoWooPage = (function () {
		var noWooPage = false;
		if(jQuery('.wpfMainWrapper').first().attr('data-nowoo')){
			noWooPage = true;
		}
		return noWooPage;
	});
	
	WpfFrontendPage.prototype.changeLmpButton = (function () {
		var lmpBtn = jQuery('.br_lmp_button_settings .lmp_button');
		if (lmpBtn.length) {
			var parentStyle = lmpBtn.parent().attr('style').replace(' ', '');
			if (parentStyle.indexOf('display:none') > -1) {
				return;
			}
			var url = lmpBtn.attr('href').split('?')[0];
			url += window.location.search;
			url = url.indexOf('/page/2') > -1 ? url : url.replace(/\/page\/[0-9]{1,}/ig, '/page/2');
			lmpBtn.attr('href', url);
			setTimeout(function(){
				jQuery('.woocommerce-pagination').addClass('wpfHidden');
			}, 1000);
		}
	});

	WpfFrontendPage.prototype.changeUrlByFilterParams = (function ($filtersDataFrontend) {
		var _thisObj = this.$obj,
			noWooPage = _thisObj.noWoo;
		if (typeof $filtersDataFrontend !== 'undefined' && $filtersDataFrontend.length > 0) {
			// the array is defined and has at least one element
			var count = $filtersDataFrontend.length,
				filterWrapper = jQuery('.wpfMainWrapper'),
				priceFlag = true;
			for(var i = 0; i < count; i++){
				switch ($filtersDataFrontend[i]['id']){
					case 'wpfPrice':
						if(priceFlag){
							var minPrice = $filtersDataFrontend[i]['settings']['min_price'],
								maxPrice = $filtersDataFrontend[i]['settings']['max_price'];

							if (typeof minPrice !== 'undefined' && minPrice.length > 0) {
								_thisObj.QStringWork('min_price', minPrice, noWooPage, filterWrapper, 'change');
							}else{
								_thisObj.QStringWork('min_price', '', noWooPage, filterWrapper, 'remove');
							}
							if (typeof maxPrice !== 'undefined' && maxPrice.length > 0) {
								_thisObj.QStringWork('max_price', maxPrice, noWooPage, filterWrapper, 'change');
							}else{
								_thisObj.QStringWork('max_price', '', noWooPage, filterWrapper, 'remove');
							}
							priceFlag = false;
						}
						break;
					case 'wpfPriceRange':
						if(priceFlag){
							var minPrice = $filtersDataFrontend[i]['settings']['min_price'],
								maxPrice = $filtersDataFrontend[i]['settings']['max_price'];
							if (typeof minPrice !== 'undefined' && minPrice.length > 0 ) {
								_thisObj.QStringWork('min_price', minPrice, noWooPage, filterWrapper, 'change');
							}else{
								_thisObj.QStringWork('min_price', '', noWooPage, filterWrapper, 'remove');
							}
							if (typeof maxPrice !== 'undefined' && maxPrice.length > 0) {
								_thisObj.QStringWork('max_price', maxPrice, noWooPage, filterWrapper, 'change');
							}else{
								_thisObj.QStringWork('max_price', '', noWooPage, filterWrapper, 'remove');
							}

							priceFlag = false;
						}
						break;
					case 'wpfSortBy':
						var orderby = $filtersDataFrontend[i]['settings']['orderby'];
						if (typeof orderby !== 'undefined' && orderby.length > 0 ) {
							_thisObj.QStringWork('orderby', orderby, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('orderby', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfCategory':
					case 'wpfPerfectBrand':
						var product_cat = $filtersDataFrontend[i]['settings']['settings'],
							name = $filtersDataFrontend[i]['name'],
							delim = $filtersDataFrontend[i]['delim'];
						product_cat = product_cat.join(delim ? delim : '|');
						if (typeof product_cat !== 'undefined' && product_cat.length > 0) {
							_thisObj.QStringWork(name, product_cat, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork(name, '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfTags':
						var product_tag = $filtersDataFrontend[i]['settings']['settings'],
							name = $filtersDataFrontend[i]['name'],
							delim = $filtersDataFrontend[i]['delim'];
						product_tag = product_tag.join(delim ? delim : '|');
						if (typeof product_tag !== 'undefined' && product_tag.length > 0) {
							_thisObj.QStringWork(name, product_tag, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork(name, '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfAttribute':
						var product_taxonomy = $filtersDataFrontend[i]['settings']['taxonomy'],
							product_attr = $filtersDataFrontend[i]['settings']['settings'],
							delim = $filtersDataFrontend[i]['delim'];
						product_attr = product_attr.join(delim ? delim : '|');

						if (typeof product_attr !== 'undefined' && product_attr.length > 0) {
							_thisObj.QStringWork(product_taxonomy, product_attr, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork(product_taxonomy, '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfAuthor':
						var authorVal = $filtersDataFrontend[i]['settings']['settings'];
						if (typeof authorVal !== 'undefined' && authorVal.length > 0) {
							_thisObj.QStringWork('pr_author', authorVal, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('pr_author', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfFeatured':
						var featureVal = $filtersDataFrontend[i]['settings']['settings'];
						if (typeof featureVal !== 'undefined' && featureVal.length > 0) {
							_thisObj.QStringWork('pr_featured', featureVal, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('pr_featured', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfOnSale':
						var onSaleVal = $filtersDataFrontend[i]['settings']['settings'];
						if (typeof onSaleVal !== 'undefined' && onSaleVal.length > 0) {
							_thisObj.QStringWork('pr_onsale', onSaleVal, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('pr_onsale', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfInStock':
						var pr_stock = $filtersDataFrontend[i]['settings']['settings'];
						pr_stock = pr_stock.join(delim ? delim : '|');
						if (typeof pr_stock !== 'undefined' && pr_stock.length > 0 ) {
							_thisObj.QStringWork('pr_stock', pr_stock, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('pr_stock', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					case 'wpfRating':
						var ratingVal = $filtersDataFrontend[i]['settings']['settings'];
						if (typeof ratingVal !== 'undefined' && checkArray(ratingVal) && ratingVal.length > 0 ) {
							_thisObj.QStringWork('pr_rating', ratingVal, noWooPage, filterWrapper, 'change');
						}else{
							_thisObj.QStringWork('pr_rating', '', noWooPage, filterWrapper, 'remove');
						}
						break;
					default:
						if(typeof(_thisObj.changeUrlByFilterParamsPro) == 'function') {
							_thisObj.changeUrlByFilterParamsPro($filtersDataFrontend[i], noWooPage, filterWrapper);
						}
						break;
				}
			}
		}else{
			return false;
		}

	});

	WpfFrontendPage.prototype.changeSlugByUrl = (function () {
		jQuery('.wpfSlugWrapper .wpfSlug').remove();
		var _thisObj = this.$obj,
			noWooPage = _thisObj.noWoo;
		if(noWooPage){
			if( jQuery('.wpfMainWrapper').first().attr('data-hide-url')){
				var searchParams =  jQuery.toQueryParams(jQuery('.wpfMainWrapper').first().attr('data-hide-url'));
			}
		}else{
			var searchParams = jQuery.toQueryParams(window.location.search);
		}

		for (var key in searchParams) {
			if(key === 'min_price' || key === 'max_price'){
				key = 'min_price,max_price';
			}

			if(jQuery('.wpfFilterWrapper[data-get-attribute="'+key+'"]').length > 0){
				var elem = jQuery('.wpfFilterWrapper[data-get-attribute="'+key+'"]').first(),
					$slug = elem.attr('data-slug'),
					$label = elem.attr('data-label'),
					$title = elem.attr('data-title'),
					$getAttr = elem.attr('data-get-attribute'),
					$filterType = elem.attr('data-filter-type');
				if(typeof $title != 'undefined') $label = $title;
				else if(typeof $label == 'undefined') $label = $slug;

				var html = '';
				if(jQuery('.wpfSlugWrapper').length > 0){
					if( !jQuery('.wpfSlugWrapper .wpfSlug[data-slug="'+$slug+'"]').length > 0 ) {
						html += '<div class="wpfSlug" data-slug="'+$slug+'" data-get-attribute="'+$getAttr+'" data-filter-type="'+$filterType+'"><div class="wpfSlugTitle">'+$label+'</div><div class="wpfSlugDelete">x</div></div>';
						jQuery('.wpfSlugWrapper').append(html);
					}
				}else{
					if( !jQuery('.wpfSlugWrapper .wpfSlug[data-slug="'+$slug+'"]').length > 0 ) {
						html += '<div class="wpfSlugWrapper">';
						html += '<div class="wpfSlug" data-slug="'+$slug+'" data-get-attribute="'+$getAttr+'" data-filter-type="'+$filterType+'"><div class="wpfSlugTitle">'+$label+'</div><div class="wpfSlugDelete">x</div></div>';
						html += '</div>';
						jQuery('.storefront-sorting').append(html);
					}
				}
			}
		}

	});

	WpfFrontendPage.prototype.sendFiltersOptionsByAjax = (function ($filtersData, $queryVars, $filterSettings, $generalSettings) {
		var _thisObj = this.$obj,
			$wrapperSettings = [];
		if ( window.wpfAdminPage ) {
			return false;
		}
		_thisObj.enableFiltersLoader();

		var $filtersOptions = JSON.stringify($filtersData);
		if ($filterSettings === undefined) {
			$filterSettings = [];
		} else {
			$filterSettings = JSON.stringify($filterSettings);
		}

		if (typeof $generalSettings === 'undefined') {
			$generalSettings = [];
		} else {
			$wrapperSettings = $generalSettings['settings'];
			$generalSettings = $generalSettings['settings']['filters']['order']
		}

		/**
		 * There are some cases when we do not provide ajax functionlity
		 */
		if ( window.InfiniteScroll && window.InfiniteScroll.prototype ) {
			/**
			 * Flatsome theme compability
			 * We do not privide ajax functionlity with infinite scroll
			 *
			 * @link https://themeforest.net/item/flatsome-multipurpose-responsive-woocommerce-theme/5484319
			 */
			if (jQuery('body').hasClass('theme-flatsome')) {
				location.reload();
				return;
			}
		}

		jQuery.sendFormWpf({
			data: {
				mod: 'woofilters',
				action: 'filtersFrontend',
				options: $filtersOptions,
				queryvars: $queryVars,
				settings: $filterSettings,
				general: $generalSettings,
				currenturl: window.location.href,
			},
			onSuccess: function(res) {
				if(!res.error) {
					var catSelector = wpfGetSelector(res.data['categoryHtml'], true, 'ul.products'),
						loopSelector = wpfGetSelector(res.data['loopStartHtml'], true, 'ul.products', 3);
					if (typeof jQuery('.product-categories-wrapper') !== 'undefined' && jQuery('.product-categories-wrapper > ul.products').length > 0) {
						//replace cats html
						jQuery('.product-categories-wrapper > ul.products').html(res.data['categoryHtml']);
						//replace products html
						jQuery('ul.products').eq(1).html(res.data['productHtml']);
					} else if (jQuery('.elementor-widget-container ul.products').length > 0) {
						jQuery('.elementor-widget-container ul.products').html(res.data['categoryHtml']+res.data['productHtml']);
					} else if (jQuery('.woocommerce > .products[data-filterargs][data-innerargs]').length > 0) {
						jQuery('.woocommerce > .products[data-filterargs][data-innerargs]').html(res.data['categoryHtml']+res.data['productHtml']);
					} else {
						var loopContainer = jQuery(loopSelector);
						if(!loopContainer.length 
							&& !jQuery('.wpfMainWrapper').attr('data-nowoo') !== typeof undefined
							&& !jQuery('.wpfMainWrapper').attr('data-nowoo') !== false) {
								location.reload();
						}

						loopContainer.each(function(){
							//replace cats html
							jQuery(this).html(res.data['categoryHtml']);
							//replace products html
							jQuery(this).append(res.data['productHtml']);
						});
					}
					var countSelector = wpfGetSelector(res.data['resultCountHtml'], true, '.woocommerce-result-count'),
						wooCount = jQuery(countSelector);
					if(wooCount.length > 0) {
						wooCount.replaceWith(res.data['resultCountHtml']);
					}

					var isLeerPagination = res.data['paginationHtml'] == '',
						paginationSelector = wpfGetSelector(res.data[isLeerPagination ? 'paginationLeerHtml' : 'paginationHtml'], false, '.woocommerce-pagination'),
						wooPagination = jQuery(paginationSelector),
						newPagination = res.data['paginationHtml'];


					if(wooPagination.length > 0) {
						if(typeof _thisObj.paginationClasses == 'undefined') {
							_thisObj.paginationClasses = wooPagination.attr('class');
						}
						if(isLeerPagination) {
							wooPagination.css({'display':'none'});
						} else {
							wooPagination.replaceWith(newPagination);
						}
					} else if(!isLeerPagination) {
						var afterLoop = jQuery('.after-shop-loop');
						if(afterLoop.length > 0) {
							afterLoop.prepend(newPagination);
						} else {
							wooCount = jQuery('.storefront-sorting ' + countSelector);
							if(wooCount.length > 0) {
								wooCount.after(newPagination);
							} else {
								jQuery(loopSelector).eq(0).after(newPagination);
							}
						}
					}
					if(typeof _thisObj.paginationClasses != 'undefined') {
						jQuery(paginationSelector).attr('class', _thisObj.paginationClasses);
					}

					//if changed min/max price and we have wpfPrice filter
					//we need change slider
					_thisObj.getUrlParamsChangeFiltersValues();

					_thisObj.disableFiltersLoader();
					_thisObj.removeOverlay();
					toggleClear();

					if ( jQuery('body').find('.products').hasClass('oceanwp-row') ) {
						jQuery('body').find('.products li:first').addClass('entry').addClass('has-media').addClass('col').addClass('span_1_of_4').addClass('owp-content-left');
					}
					
					_thisObj.changeLmpButton();
					
					// for yith quick view
					jQuery(document).trigger('yith_wcqv_wcajaxnav_update');
					
					if (typeof(_thisObj.scrollToProductsPro) == 'function') {
						_thisObj.scrollToProductsPro($wrapperSettings);
					}

					// event for custom javascript from js editor, example: document.addEventListener('wpfAjaxSuccess', function(event) {alert('Custom js');});
					//document.dispatchEvent(new Event('wpfAjaxSuccess')); - not work in IE11
					var customEvent = document.createEvent('Event');
					customEvent.initEvent('wpfAjaxSuccess', false, true); 
					document.dispatchEvent(customEvent);
				}
			}
		});

	});

	WpfFrontendPage.prototype.enableFiltersLoader =(function(){
		var preview = jQuery('.wpfPreviewLoader').first().clone().removeClass('wpfHidden');
		jQuery('ul.products').html(preview);
	});

	WpfFrontendPage.prototype.disableFiltersLoader =(function(){
		jQuery('.wpfPreviewLoader').first().clone().addClass('wpfHidden');
	});

	//after load change price and price range filters params if find get params
	WpfFrontendPage.prototype.getUrlParamsChangeFiltersValues = (function(){
		var _thisObj = this.$obj,
			noWooPage = _thisObj.noWoo;
		if(noWooPage){
			var curUrl = jQuery('.wpfMainWrapper').first().attr('data-hide-url');
		}else{
			var curUrl = window.location.href;
		}
		if(!curUrl){
			return;
		}
		//get all get params
		var urlParams = _thisObj.findGetParameter(curUrl);
		jQuery('.wpfFilterWrapper').each(function () {
			var $filter = jQuery(this),
				filterType = $filter.attr('data-filter-type'),
				settings = _thisObj.getFilterMainSettings($filter.closest('.wpfMainWrapper'));

			switch(filterType){
				case 'wpfPrice':
					var minPrice = urlParams.min_price ? urlParams.min_price : $filter.attr('data-minvalue'),
						maxPrice = urlParams.max_price ? urlParams.max_price : $filter.attr('data-maxvalue'),
						skin = 'default';

					if(minPrice){
						$filter.find('#wpfMinPrice').val(minPrice);
					}
					if(maxPrice){
						$filter.find('#wpfMaxPrice').val(maxPrice);
					}
					if(settings){
						skin = $filter.attr('data-price-skin');
					}
					if(skin === 'default'){
						var sliderWrapper = $filter.find("#wpfSliderRange");
					}else{
						var sliderWrapper = $filter.find('.ion-range-slider').data('ionRangeSlider');
						$filter.addClass('wpfNotActiveSlider');
					}
					if(minPrice && maxPrice){
						if(skin === 'default'){
							sliderWrapper.slider({
								values: [minPrice, maxPrice]
							});
						}else if(typeof(sliderWrapper) != 'undefined') {
							sliderWrapper.update({from: minPrice, to: maxPrice});
							$filter.removeClass('wpfNotActiveSlider');
						}
					}
					_thisObj.chageRangeFieldWidth();
					if(typeof(_thisObj.eventChangeFilterPro) == 'function') {
						_thisObj.eventChangeFilterPro($filter, settings);
					}
					break;
				case 'wpfPriceRange':
					var minPrice = urlParams.min_price ? parseFloat(urlParams.min_price) : false,
						maxPrice = urlParams.max_price ? parseFloat(urlParams.max_price) : false,
						$options = $filter.find('li');
					$options.find('input[type="checkbox"]').prop('checked', false);
					$options.each(function () {
						var _this = jQuery(this),
							range = _this.attr('data-range');
						if(typeof range != 'undefined') {
							range = range.split(',');
							var minRange = range[0] == '' ? false : parseFloat(range[0]),
								maxRange = range[1] == '' ? false : parseFloat(range[1]);
							if(minPrice === minRange && maxPrice === maxRange ){
								_this.find('input[type="checkbox"]').prop('checked', true);
								return false;
							}
						}
					});
					if(typeof(_thisObj.eventChangeFilterPro) == 'function') {
						_thisObj.eventChangeFilterPro($filter, settings);
					}
					break;
			}
		});

	});

	WpfFrontendPage.prototype.findGetParameter =(function(url){
		// This function is anonymous, is executed immediately and
		// the return value is assigned to QueryString!
		var query_string = {},
			usefulParam = url.split("?")[1] || "",
			query = usefulParam || "",
			vars = query.split("&");

		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split("=");

			// If first entry with this name
			if (typeof query_string[pair[0]] === "undefined") {
				query_string[pair[0]] = decodeURIComponent(pair[1]);
				// If second entry with this name
			} else if (typeof query_string[pair[0]] === "string") {
				var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
				query_string[pair[0]] = arr;
				// If third or later entry with this name
			} else {
				query_string[pair[0]].push(decodeURIComponent(pair[1]));
			}
		}
		return query_string;
	});

	WpfFrontendPage.prototype.getClearLabel = (function (label, withCount) {
		if(withCount) {
			var cnt = label.lastIndexOf('(');
			if(cnt == -1) cnt = label.lastIndexOf('<span');
			if(cnt != -1) label = label.substring(0, cnt).trim();
		}

		label = label.replace(/&nbsp;/g,'');

		return label;
	});

	WpfFrontendPage.prototype.getFilterOptionsByType = (function($filter, filterType){
		var _thisObj = this.$obj;
		return _thisObj['get' + filterType.replace('wpf', '') + 'FilterOptions']($filter);
	});

	WpfFrontendPage.prototype.getPriceFilterOptions = (function ($filter) {
		var optionsArray = [],
			options = [],
			minPrice = $filter.find('#wpfMinPrice').val(),
			maxPrice = $filter.find('#wpfMaxPrice').val(),
			str = minPrice + ',' + maxPrice;
		options.push(str);

		//options for frontend(change url)
		var frontendOptions = [],
			getParams = $filter.attr('data-get-attribute');
		getParams = getParams.split(",");
		for (var i = 0; i < getParams.length; i++) {
			if(i === 0){
				frontendOptions[getParams[i]] = minPrice;
			}
			if(i === 1){
				frontendOptions[getParams[i]] = maxPrice;
			}
		}
		var symbol = $filter.find('.wpfCurrencySymbol'),
			symbolB = symbol.length && !symbol.is(':last-child') ? symbol.html() : '',
			symbolA = symbol.length && symbol.is(':last-child') ? symbol.html() : '',
			selectedOptions = {'is_one': true, 'list': [symbolB + minPrice + symbolA + ' - ' + symbolB + maxPrice + symbolA]};

		optionsArray['backend'] = options;
		optionsArray['frontend'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getPriceRangeFilterOptions = (function ($filter) {
		var optionsArray = [],
			options = [],
			frontendOptions = [],
			selectedOptions = {'is_one': true, 'list': []},
			i = 0;

		if($filter.attr('data-display-type') === 'list'){
			if($filter.find("input:checked").length){
				var li = $filter.find('input:checked').closest('li');
				options[i] = li.attr('data-range');
				selectedOptions['list'][i] = li.find('.wpfValue').html();
			}
		}else if($filter.attr('data-display-type') === 'dropdown'){
			if($filter.find(":selected").attr('data-range')){
				var option = $filter.find(":selected");
				options[i] = option.attr('data-range');
				selectedOptions['list'][i] = option.html();
			}

		}

		//options for frontend(change url)
		if (typeof options !== 'undefined' && options.length > 0) {
			var getParams = $filter.attr('data-get-attribute');
			getParams = getParams.split(",");
			if (typeof options[0] !== 'undefined' && options[0].length > 0) {
				var prices = options[0].split(',');
				frontendOptions[getParams[0]] = prices[0];
				frontendOptions[getParams[1]] = prices[1];
			}
		}
		if(options.length == 0) {
			var defRange = $filter.attr('data-default');
			if(typeof defRange != 'undefined' && defRange.length) {
				options[i] = defRange;
			}
		}
		optionsArray['backend'] = options;
		optionsArray['frontend'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getSortByFilterOptions = (function ($filter) {
		var optionsArray = [],
			option = $filter.find('option:selected'),
			value = option.val();

		//options for backend (filtering)
		optionsArray['backend'] = value;

		//options for frontend(change url)
		var frontendOptions = [],
			selectedOptions = {'is_one': true, 'list': []},
			getParams = $filter.attr('data-get-attribute');
		if(value.length) {
			selectedOptions['list'][0] = option.html();
		}

		frontendOptions[getParams] = value;
		optionsArray['frontend'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;
		return optionsArray;
	});

	WpfFrontendPage.prototype.getInStockFilterOptions = (function ($filter) {
		var optionsArray = [],
			frontendOptions = [],
			options = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType === 'dropdown'), 'list': []},
			i = 0;

		//options for backend (filtering)
		if(filterType === 'dropdown'){
			var option = $filter.find(":selected"),
				value = option.attr('data-slug');
			if(value != '') {
				options[i] = value;
				frontendOptions[i] = value;
				selectedOptions['list'][i] = option.html();
			}
		} else {
			$filter.find('input:checked').each(function () {
				var li = jQuery(this).closest('li'),
					slug = li.attr('data-term-slug');
				options[i] = slug;
				frontendOptions[i] = slug;
				selectedOptions['list'][li.attr('data-term-id')] = li.find('.wpfValue').html();
				i++;
			});
		}
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getCategoryFilterOptions = (function ($filter) {
		var _thisObj = this.$obj,
			optionsArray = [],
			frontendOptions = [],
			options = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'list' || filterType == 'dropdown'), 'list': []},
			i = 0;

		//options for backend (filtering)
		if(filterType === 'dropdown'){
			var option = $filter.find(":selected"),
				id = option.attr('data-term-id'),
				value = option.val();
			if(value != '') {
				options[i] = value;
				selectedOptions['list'][id] = _thisObj.getClearLabel(option.html(), $filter.hasClass('wpfShowCount'));
			}
			frontendOptions[i] = id;
		} else if(filterType === 'mul_dropdown'){
			$filter.find(':selected').each(function () {
				var option = jQuery(this);
				options[i] = option.val();
				frontendOptions[i] = option.attr('data-term-id');
				selectedOptions['list'][option.attr('data-term-id')] = _thisObj.getClearLabel(option.html(), $filter.hasClass('wpfShowCount'));
				i++;
			});
		} else {
			var removeSelectedList = [];
			$filter.find('input').each(function () {
				var inputCurent = jQuery(this),
					liCurent = inputCurent.closest('li'),
					id = liCurent.data('term-id'),
					isParent = liCurent.children('ul').length > 0,
					isChecked = inputCurent.is(':checked'),
					hierarchicalLogic = $filter.attr('data-logic-hierarchical'),
					type = $filter.attr('data-display-type'),
					isHierarchical = $filter.attr('data-show-hierarchical'),
					isHierarchicalLogic = isHierarchical === 'true' && type === 'multi' || type === 'text';

				if ( isParent ) {
					var isAllChildChecked = true,
						childList = [];

					liCurent.find('ul li').each(function() {
						var childId = jQuery(this).data('term-id'),
							childLi = jQuery(this),
							childInput = childLi.find('input'),
							isChildChecked = childInput.prop('checked');

						childList.push(childId);

						if (!isChildChecked) {
							isAllChildChecked = false;
							return false;
						}
					});

					if (isChecked && isAllChildChecked) {
						removeSelectedList = removeSelectedList.concat(childList);

						var onlyUnique = function(value, index, self) {
							return self.indexOf(value) === index;
						}

						removeSelectedList = removeSelectedList.filter( onlyUnique );
						selectedOptions.removeSelected = removeSelectedList;
					}
				}

				if (jQuery(this).is(':checked')) {
					if ( isHierarchicalLogic && hierarchicalLogic == 'child' ) {
						var liElements = liCurent.find('li'),
							isChildChicked = false;

						for (var j = 0; j < liElements.length; ++j) {
							 var li = liElements[j];
							if (jQuery(li).find('input').prop('checked')) {
								isChildChicked = true;
							}
						}
						if (!isChildChicked) {
							options[i] = id;
							frontendOptions[i] = id;
						}
					} else if(isHierarchicalLogic && hierarchicalLogic == 'parent') {
						var parents = liCurent.parents('li'),
							isChildChicked = false;

						for (var j = 0; j < parents.length; ++j) {
							 var li = parents[j];
							if (jQuery(li).find('input').prop('checked')) {
								isChildChicked = true;
							}
						}
						if (!isChildChicked) {
							options[i] = id;
							frontendOptions[i] = id;
						}
					} else {
						options[i] = id;
						frontendOptions[i] = id;
					}
					selectedOptions['list'][id] = liCurent.find('.wpfValue').html();
					i++;
				}
			});
		}

		var options = options.filter(function (el) {
			return el != null;
		});
		var frontendOptions = frontendOptions.filter(function (el) {
			return el != null;
		});


		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getPerfectBrandFilterOptions = (function ($filter) {
		return this.$obj.getCategoryFilterOptions($filter);
	});

	WpfFrontendPage.prototype.getTagsFilterOptions = (function ($filter) {
		var _thisObj = this.$obj,
			optionsArray = [],
			options = [],
			frontendOptions = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'dropdown'), 'list': []},
			withCount = $filter.hasClass('wpfShowCount'),
			i = 0;

		//options for backend (filtering)
		if(filterType === 'dropdown'){
			var option = $filter.find(":selected"),
				value = option.val();
			if(value != '') {
				options[i] = value;
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = _thisObj.getClearLabel(option.html(), withCount);
			}
		}else if(filterType === 'mul_dropdown'){
			$filter.find(':selected').each(function () {
				var option = jQuery(this);
				options[i] = option.val();
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = _thisObj.getClearLabel(option.html(), withCount);
				i++;
			});
		} else {
			$filter.find('input:checked').each(function () {
				var li = jQuery(this).closest('li'),
					id = li.attr('data-term-id');
				options[i] = id;
				frontendOptions[i] = li.attr('data-term-slug');
				selectedOptions['list'][id] = li.find('.wpfValue').html();
				i++;
			});
		}
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});
	
	WpfFrontendPage.prototype.getAttributeFilterOptions = (function ($filter) {
		var _thisObj = this.$obj,
			optionsArray = [],
			options = [],
			frontendOptions = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'dropdown'), 'list': []},
			withCount = $filter.hasClass('wpfShowCount'),
			i = 0;

		//options for backend (filtering)
		if(filterType === 'colors'){
			$filter.find('input:checked').each(function () {
				var input = jQuery(this),
					id = input.attr('data-term-id');
				options[i] = id;
				frontendOptions[i] = input.attr('data-term-slug');
				selectedOptions['list'][id] = input.parent().find('label.icon').attr('data-term-name');
				i++;
			});
		}else if(filterType === 'dropdown'){
			var option = $filter.find(":selected"),
				value = option.val();
			if(value != '') {
				options[i] = value;
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = _thisObj.getClearLabel(option.html(), withCount);
			}
		}else if(filterType === 'mul_dropdown'){
			$filter.find(':selected').each(function () {
				var option = jQuery(this);
				options[i] = option.val();
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = _thisObj.getClearLabel(option.html(), withCount);
				i++;
			});
		}else if(filterType === 'slider'){
			var values = $filter.find('.wpfAttrNumFilterRange').data('values').split(','),
				slugs = $filter.find('.wpfAttrNumFilterRange').data('slugs').split(','),
				termIds = $filter.find('.wpfAttrNumFilterRange').data('term-ids').split(','),
				minAttrNum = $filter.find('#wpfMinAttrNum').val(),
				maxAttrNum = $filter.find('#wpfMaxAttrNum').val(),
				forceNumeric = $filter.find('.ion-range-slider').data('force-numeric') ? $filter.find('.ion-range-slider').data('force-numeric') : 0;
			slugs.forEach(function(slug, index){
				var value = forceNumeric ? parseFloat(values[index]) : values[index];
				if ( index >= minAttrNum && index <= maxAttrNum ) {
					options[i] = termIds[index];
					frontendOptions[i] = slug;
					selectedOptions['list'][slug] = $filter.attr('data-slug');
					i++;
				}
			});
		}else{
			$filter.find('input:checked').each(function () {
				var li = jQuery(this).closest('li'),
					id = li.attr('data-term-id');
				options[i] = id;
				frontendOptions[i] = li.attr('data-term-slug');
				selectedOptions['list'][id] = li.find('.wpfValue').html();
				i++;
			});
		}
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getAuthorFilterOptions = (function ($filter) {
		var optionsArray = [],
			options = [],
			frontendOptions = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'dropdown'), 'list': []},
			i = 0;

		//options for backend (filtering)
		if(filterType === 'list'){
			$filter.find('input:checked').each(function () {
				var li = jQuery(this).closest('li'),
					id = li.attr('data-term-id');
				options[i] = id;
				frontendOptions[i] = li.attr('data-term-slug');
				selectedOptions['list'][id] = li.find('.wpfValue').html();
				i++;
			});
		}else if(filterType === 'dropdown'){
			var option = $filter.find(":selected"),
				value = option.val();
			options[i] = value;
			if(value != '') {
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = option.html();
			}
		}
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getFeaturedFilterOptions = (function ($filter) {
		var optionsArray = [],
			options = [],
			frontendOptions = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'dropdown'), 'list': []},
			i = 0;

		$filter.find('input:checked').each(function () {
			var li = jQuery(this).closest('li'),
				id = li.attr('data-term-id');
			options[i] = id;
			frontendOptions[i] = li.attr('data-term-slug');
			selectedOptions['list'][id] = li.find('.wpfValue').html();
			i++;
		});
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getOnSaleFilterOptions = (function ($filter) {
		var optionsArray = [],
			options = [],
			frontendOptions = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': (filterType == 'dropdown'), 'list': []},
			i = 0;

		$filter.find('input:checked').each(function () {
			var li = jQuery(this).closest('li'),
				id = li.attr('data-term-id');
			options[i] = id;
			frontendOptions[i] = li.attr('data-term-slug');
			selectedOptions['list'][id] = li.find('.wpfValue').html();
			i++;
		});
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.getRatingFilterOptions = (function ($filter) {
		var optionsArray = [],
			frontendOptions = [],
			options = [],
			filterType = $filter.attr('data-display-type'),
			selectedOptions = {'is_one': true, 'list': []},
			i = 0;

		//options for backend (filtering)
		if(filterType == 'linestars' || filterType == 'liststars'){
			var input = $filter.find('input.wpfStarInput:checked'),
				rating = input.val();
			options[i] = rating;
			frontendOptions[i] = rating;
			selectedOptions['list'][i] = input.attr('data-label');
		}else if(filterType == 'list'){
			$filter.find('input:checked').each(function () {
				var li = jQuery(this).closest('li'),
					id = li.attr('data-term-id');
				options[i] = id;
				frontendOptions[i] = li.attr('data-term-slug');
				selectedOptions['list'][id] = li.find('.wpfValue').html();
				i++;
			});
		}else if(filterType == 'dropdown'){
			var option = $filter.find(":selected"),
				value = option.val();
			options[i] = value;
			if(value != '') {
				frontendOptions[i] = option.attr('data-slug');
				selectedOptions['list'][option.attr('data-term-id')] = option.html();
			}
		}
		optionsArray['backend'] = options;

		//options for frontend(change url)
		var getParams = $filter.attr('data-get-attribute');

		optionsArray['frontend'] = [];
		optionsArray['frontend']['taxonomy'] = getParams;
		optionsArray['frontend']['settings'] = frontendOptions;
		optionsArray['selected'] = selectedOptions;

		return optionsArray;
	});

	WpfFrontendPage.prototype.disableLeerOptions = (function () {
		var _thisObj = this.$obj;
		jQuery('.wpfMainWrapper').each(function () {
			var mainWrapper = jQuery(this),
				settings = _thisObj.getFilterMainSettings(mainWrapper);
			if(settings && settings.settings.filter_null_disabled === '1') {
				var filters = mainWrapper.find('.wpfFilterWrapper.wpfShowCount');
				if(filters.length) {
					filters.find('option[data-term-id]').prop('disabled', false);
					filters.find('option[data-term-id][data-count="0"]').prop('disabled', true);
					filters.find('.wpfCount').each(function(){
						var cntObj = jQuery(this),
							leer = cntObj.html() == '(0)',
							el = cntObj.closest('[data-term-id]'),
							input = false;
						if(el.length == 0) {
							el = cntObj.closest('[data-term-slug]');
						}
						if(el.length) {
							if(el.is('input')) {
								input = el;
								el = input.parent();
							} else {
								input = el.find('input');
							}
							input.prop('disabled', leer);
							if(leer) el.addClass('wpfOptionDisabled');
							else el.removeClass('wpfOptionDisabled');
						}
					});
				}
			}
		});
	});

	WpfFrontendPage.prototype.addSpecificPluginActions = (function() {
		// elementor plugin, action when widgets in admin panek are ready
		jQuery(window).on('load', function() {
			if ( window.elementorFrontend ) {
				if ( window.elementorFrontend.hooks ) {
					elementorFrontend.hooks.addAction( 'frontend/element_ready/widget', function( $scope ) {
						var wrapper = $scope.find('.wpfMainWrapper');

						hideFilterLoader(wrapper);
					});
				}
			} else if ( jQuery('.elementor .wpfMainWrapper').length ) {
				setTimeout(function() {
					jQuery('.elementor .wpfMainWrapper').each(function() {
						var wrapper = jQuery(this);
						hideFilterLoader(wrapper);
					});
				}, 2000);
			}
		});
	});

	jQuery(document).ready(function () {
		window.wpfFrontendPage = new WpfFrontendPage();
		window.wpfFrontendPage.init();
	});

}(window.jQuery));

var objQueryString={};

toggleClear();

function wpfGetSelector(html, controlExist, defSelector, countChilds) {
	if(html.length == 0) return defSelector;

	var elem = jQuery(html),
		selector = '',
		i = 0;
	if(typeof countChilds == 'undefined') countChilds = 1;
	while(i < countChilds && elem && elem.length) {
		if(i > 0) selector += ' ';
		i++;
		if(elem.length > 1) elem = elem.last();
		var elemId = elem.attr('id');
		if(typeof elemId != 'undefined') {
			selector += '#' + elemId;
		} else {
			var elemClass = elem.attr('class');
			if(typeof elemClass != 'undefined') {
				if(elemClass == 'container' && countChilds == 1) countChilds = 2;
				selector += elem.get(0).tagName + '.' + elemClass.trim().replace(/ +/g, '.');
			} 
		}
		if(controlExist && selector != '' && jQuery(selector).length == 0) {
			selector = '';
			break;
		}
		var elem = elem.children();
	}
	return selector.length == 0 ? defSelector : selector;
}


function getUrlParams () {
	var params={};
	window.location.search
	  .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
		params[key] = value;
	  }
	);
	return params;
}

function toggleClear() {
	var params = getUrlParams();
	jQuery(".wpfBlockClear").hide();
	jQuery(".wpfFilterWrapper").each(function(){
		var attr = jQuery(this).attr('data-get-attribute');
		if (attr in params) {
			jQuery(this).find(".wpfBlockClear").show();
		}
	});
	if ('min_price' in params || 'max_price' in params) {
		jQuery("[data-filter-type='wpfPrice']").find(".wpfBlockClear").show();
		jQuery("[data-filter-type='wpfPriceRange']").find(".wpfBlockClear").show();
	}
}

//Get querystring value
function getParameterByName(name, searchUrl) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(searchUrl);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

//Add or modify querystring
function changeUrl(filterSlug, filterValue, $wooPage, $filterWrapper) {
	removePageQString();
	//Get query string filterValue
	$wooPage = (typeof $wooPage != 'undefined' ? $wooPage: false);
	if(!$wooPage){
		var searchUrl = decodeURIComponent(location.search);
	}else{
		if($filterWrapper.attr('data-hide-url')){
			var searchUrl=$filterWrapper.attr('data-hide-url');
		} else {
			var searchUrl = '';
		}
	}

	if(searchUrl.indexOf("?")== "-1") {
		var urlValue='?'+filterSlug+'='+filterValue;
		if(!$wooPage){
			history.pushState({state:1, rand: Math.random()}, '', encodeURI(urlValue).indexOf('%25') === -1 ? encodeURI(urlValue) : urlValue);
		}
	} else {
		//Check for filterSlug in query string, if not present
		if(searchUrl.indexOf('&'+filterSlug+'=')== "-1" && searchUrl.indexOf('?'+filterSlug+'=')== "-1") {
			var urlValue=searchUrl+'&'+filterSlug+'='+filterValue;
		//If filterSlug present in query string
		} else {
			var oldValue = getParameterByName(filterSlug, searchUrl);
			if(searchUrl.indexOf("?"+filterSlug+"=")!= "-1") {
				var urlValue = searchUrl.replace('?'+filterSlug+'='+oldValue,'?'+filterSlug+'='+filterValue);
			// add existing in url filter with another option
			} else {
				var urlValue = searchUrl.replace('&'+filterSlug+'='+oldValue,'&'+filterSlug+'='+filterValue);
			}
		}
		if(!$wooPage){
			history.pushState({state:1, rand: Math.random()}, '', encodeURI(urlValue).indexOf('%25') === -1 ? encodeURI(urlValue) : urlValue);
		}
		//history.pushState function is used to add history state.
		//It takes three parameters: a state object, a title (which is currently ignored), and (optionally) a URL.
	}
	objQueryString.key=filterValue;
	return urlValue;
}

function removePageQString() {
	var path = document.location.pathname,
		page = path.indexOf('/page/');
	if(page != -1 && history.pushState) {
		history.pushState({state:1, rand: Math.random()}, '', path.substr(0, page + 1) + location.search);
	}
}
//Function used to remove querystring
function removeQString(key, $wooPage, $filterWrapper) {
	removePageQString();
	//Get query string value
	var urlValue=decodeURI(document.location.href);
	$wooPage = (typeof $wooPage != 'undefined' ? $wooPage: false);
	if(!$wooPage){
		var searchUrl=decodeURIComponent(location.search);
	}else{
		if($filterWrapper.attr('data-hide-url')){
			var searchUrl=decodeURI($filterWrapper.attr('data-hide-url'));
		}else {
			var searchUrl = '';
		}
		var urlValue=document.location.href + searchUrl;
	}
	if(key!="") {
		var oldValue = getParameterByName(key, searchUrl),
			removeVal=key+"="+oldValue;

		if(searchUrl.indexOf('?'+removeVal+'&')!= "-1") {
			urlValue=urlValue.replace('?'+removeVal+'&','?');
		}
		else if(searchUrl.indexOf('&'+removeVal+'&')!= "-1") {
			urlValue=urlValue.replace('&'+removeVal+'&','&');
		}
		else if(searchUrl.indexOf('?'+removeVal)!= "-1") {
			urlValue=urlValue.replace('?'+removeVal,'');
		}
		else if(searchUrl.indexOf('&'+removeVal)!= "-1") {
			urlValue=urlValue.replace('&'+removeVal,'');
		}
		if($wooPage){
			urlValue = urlValue.replace(document.location.href,'');
		}
	} else {
		if(!$wooPage){
			var searchUrl=decodeURIComponent(location.search);
			urlValue=urlValue.replace(searchUrl,'');
		}else{
			var searchUrl=$filterWrapper.attr('data-hide-url');
			urlValue=urlValue.replace(searchUrl,'');
			urlValue = urlValue.replace(document.location.href,'');
		}
	}
	if(!$wooPage){
		history.pushState({state:1, rand: Math.random()}, '', encodeURI(urlValue).indexOf('%25') === -1 ? encodeURI(urlValue) : urlValue);
	}
	return urlValue.indexOf('%25') !== -1 ? decodeURI(urlValue) : urlValue;
}
function checkArray(my_arr){
	for(var i=0;i<my_arr.length;i++){
		if(my_arr[i] === "")
			return false;
	}
	return true;
}
jQuery.toQueryParams = function(str, separator) {
	separator = separator || '&';
	var obj = {};
	if (str.length == 0)
		return obj
	var c = str.substr(0,1),
		s = c=='?' || c=='#'  ? str.substr(1) : str,
		a = s.split(separator);
	for (var i=0; i<a.length; i++) {
		var p = a[i].indexOf('=');
		if (p < 0) {
			obj[a[i]] = '';
			continue
		}
		var k = decodeURIComponent(a[i].substr(0,p)),
			v = decodeURIComponent(a[i].substr(p+1)),
			bps = k.indexOf('[');
		if (bps < 0) {
			obj[k] = v
			continue;
		}

		var bpe = k.substr(bps+1).indexOf(']');
		if (bpe < 0) {
			obj[k] = v
			continue;
		}

		var bpv = k.substr(bps+1, bps+bpe-1),
			k = k.substr(0,bps);
		if (bpv.length <= 0) {
			if (typeof(obj[k]) != 'object') obj[k] = [];
			obj[k].push(v);
		} else {
			if (typeof(obj[k]) != 'object') obj[k] = {};
			obj[k][bpv] = v;
		}
	}
	return obj;
}
function wpfChangeFiltersCount (wpfExistTerms) {
	jQuery("body").find(".wpfShowCount").find(".wpfCount").html("(0)");
	jQuery("body").find(".wpfShowCount select:not([multiple]) option[data-count]").each(function(){
		var attr = jQuery(this).attr("data-term-name");
		jQuery(this).attr('data-count', 0).html(attr+" (0)");
	});
	jQuery("body").find(".wpfShowCount select[multiple]").find("option").each(function(){
		attr = jQuery(this).attr("data-term-name");
		jQuery(this).attr('data-count', 0).html(attr+" (0)");
	});

	jQuery("body").find(".wpfShowCount").each(function(filterCounter){
		var filter = jQuery(this);
		if (filter.attr("data-filter-type").length > 0) {
			var taxonomy = filter.data('taxonomy');
			if(typeof taxonomy == 'undefined' || taxonomy.length == 0) taxonomy = filter.data('slug');
			if (taxonomy in wpfExistTerms) {
				jQuery.each(wpfExistTerms[taxonomy], function( index, value ){
					index = index.toLowerCase();
					var els = jQuery('body').find('div.wpfShowCount[data-taxonomy="'+taxonomy+'"] [data-term-id="'+index+'"]');
					if (els.length === 0) {
						els = jQuery('body').find('div.wpfShowCount[data-slug="'+taxonomy+'"] [data-term-id="'+index+'"]');
					}
					if(els.length > 0) {
						els.each(function() {
							var el = jQuery(this);
							if (el.find('.wpfCount').length > 0) {
								el.find('.wpfCount').html('('+value+')');
							} else if (el.parent().attr('class') == 'wpfColorsColBlock') {
								el.parent().find('.wpfCount').html('('+value+')');
							} else {
								var attrname = el.attr('data-term-name');
								var tooltipstered = el.siblings('.tooltipstered');
								if (tooltipstered.length) {
									attrname = tooltipstered.attr('data-term-name');
									tooltipstered.tooltipster('content', ''+attrname+' ('+value+')');
								} else if (attrname !== undefined) {
									el.html(''+attrname+' ('+value+')');
								} else {
									el.html(''+index+' ('+value+')');
								}
							}
							if(el.is('option')) {
								el.attr('data-count', value);
							}
						});
					}
				});
			}
		}
	});
	window.wpfFrontendPage.disableLeerOptions();
	jQuery('.wpfShowCount').find('select[multiple]').multiselect('reload');
};

function wpfShowHideFiltersAtts(wpfExistTerms) {
	jQuery('.wpfFilterWrapper').filter('[data-show-all="0"]').each(function(){
		var filter = jQuery(this),
			taxonomy = filter.data('taxonomy'),
			getAttr = filter.data('get-attribute'),
			isInSearch = getParameterByName(getAttr, location.search);

		if (typeof taxonomy == 'undefined' || taxonomy.length == 0) {
			taxonomy = filter.data('slug');
		}

		if (!isInSearch && getAttr.indexOf('pr_filter') == -1) {
			if (taxonomy in wpfExistTerms) {
				var termIds = wpfExistTerms[taxonomy];
				filter.find('[data-term-id]').each(function () {
					var elem = jQuery(this),
						id = elem.data('term-id');
					if (id in termIds) {
						if(elem.closest('.wpfButtonsFilter').length) elem.css('display', 'inline-block');
						else elem.show();
						if (elem.parent().hasClass('wpfColorsColBlock')) {
							elem.parent().parent().show();
						}
						if (elem.closest('.wpfColorsRow').length) {
							elem.parent().show();
						}
					} else {
						elem.hide();
						elem.find('input').prop('checked', false);
						if (elem.parent().hasClass('wpfColorsColBlock')) {
							elem.parent().parent().hide();
						}
					}
				});
				if (filter.find('[data-term-id][style*="none"]').length == filter.find('[data-term-id]').length) filter.hide();
				else filter.show();
			} else {
				filter.find('input').prop('checked', false);
				filter.find('select').val('');
				filter.hide();
			}
		}
		filter.find('select[multiple]').multiselect('reload');
		if(typeof(window.wpfFrontendPage.wpfShowHideFiltersAttsPro) == 'function') {
			window.wpfFrontendPage.wpfShowHideFiltersAttsPro(filter);
		}
	});
}

function wpfChangePriceFiltersCount(prices) {
	var _thisObj = window.wpfFrontendPage,
		noWooPage = _thisObj.noWoo,
		filterWrapper = jQuery('.wpfMainWrapper'),
		priceFilters = jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"].wpfNotActive');
	
	jQuery('.wpfFilterWrapper[data-filter-type="wpfPrice"]').each(function () {
		var wpfPrice = jQuery(this);
		wpfPrice.attr('data-minvalue', prices.min_price).attr('data-maxvalue', prices.max_price);
		
		wpfPrice.find('#wpfMinPrice').attr('min', prices.min_price).val(prices.min_price);
		wpfPrice.find('#wpfMaxPrice').attr('max', prices.max_price).val(prices.max_price);
		
		if (wpfPrice.find(".ion-range-slider").length) {
			wpfPrice.find(".ion-range-slider").each(function () {
				jQuery(this).attr('data-min', prices.min_price).attr('data-max', prices.max_price);
				var ionSlider = jQuery(this).data("ionRangeSlider");
				ionSlider.update({
					min: prices.min_price,
					max: prices.max_price,
				});
			});
		}
	});
	window.wpfFrontendPage.eventsPriceFilter();
	/*if (typeof(window.wpfFrontendPage.eventsFrontendPro) == 'function') {
		window.wpfFrontendPage.eventsFrontendPro();
	}*/
}

function hideFilterLoader( wrapper ) {
	wrapper.find('.wpfLoaderLayout').hide();
	wrapper.css({
		position: ''
	});
	wrapper.find('.wpfFilterWrapper').css({
		visibility: 'visible'
	});
}
