/*global jQuery */
(function($){
  
    // USE STRICT
    "use strict";

    var woocommerce_buying_guide = {

        init : function (options) {

        	woocommerce_buying_guide.defaults = {
				live_filter: "1",
				filter_after_choice: "1",
			};

			woocommerce_buying_guide.settings = $.extend( {}, woocommerce_buying_guide.defaults, options );
			woocommerce_buying_guide._defaults = woocommerce_buying_guide.defaults;

            // Basic variables
            woocommerce_buying_guide.url = location.href;
            woocommerce_buying_guide.url_parameters = woocommerce_buying_guide.url.split('?');
            woocommerce_buying_guide.url_without_paramter = woocommerce_buying_guide.url.split('?')[0].split("#")[0];
            woocommerce_buying_guide.base_url = woocommerce_buying_guide.url.substring(0, woocommerce_buying_guide.url.indexOf('/', 14));
            woocommerce_buying_guide.pathname = location.pathname;
            woocommerce_buying_guide.pathSplit = woocommerce_buying_guide.pathname.split("/");
            woocommerce_buying_guide.title = document.title;

            woocommerce_buying_guide.initTooltips();
            woocommerce_buying_guide.hideProducts();
            woocommerce_buying_guide.watchLink();
            // woocommerce_buying_guide.watchBack();
            woocommerce_buying_guide.watchChoices();
            woocommerce_buying_guide.checkSuccess();
            woocommerce_buying_guide.watchModal();

            woocommerce_buying_guide.products = [];
            woocommerce_buying_guide.choicesMade = [];
            woocommerce_buying_guide.questionProducts = [];

        },
		initTooltips : function () {
			if ($.fn.tooltip) { 
				$('.tooltip-trigger').tooltip();
			}
	   	},
	   	hideProducts : function() {

	   		if(woocommerce_buying_guide.settings.hide_first === "1") {
	   			$('.products .product').fadeOut();
	   		}
	   	},
	   	watchBack : function () {

	   		var that = this;

	   		$('.woocommerce-buying-guide-go-back').on('click', function(e) {

	   			var id = $(this).data('id');
	   			var question = $(this).data('question');
	   			var toBackId = $(this).data('to-back-id');

	   			$('#woocommerce-buying-guide-question-container-' + question).fadeOut();
	   			if(toBackId == 0) {
	   				$('#woocommerce-buying-guide-start-container-' + id).fadeIn();
		   			woocommerce_buying_guide.products = [];
		   			woocommerce_buying_guide.choicesMade = [];
		   			woocommerce_buying_guide.questionProducts = [];

	   			} else {
	   				$('#woocommerce-buying-guide-question-container-' + toBackId).fadeIn();
	   				// woocommerce_buying_guide.products = woocommerce_buying_guide.questionProducts[toBackId].differences(woocommerce_buying_guide.products);
	   				// 	   				// .grep(array2, function(el) {
					//         if (jQuery.inArray(el, array1) == -1) difference.push(el);
					// });
	   				delete woocommerce_buying_guide.questionProducts[toBackId]
	   				delete woocommerce_buying_guide.choicesMade[toBackId];

   					$('#woocommerce-buying-guide-choices-container-' + question + ' .woocommerce-buying-guide-choice-radio').fadeIn();
	   				console.log(woocommerce_buying_guide.products);
	   			}
   			});
		},
	   	watchLink : function () {

	   		$('.woocommerce-buying-guide-link, .woocommerce-buying-guide-start').on('click', function(e) {

	   			e.preventDefault();

	   			if(woocommerce_buying_guide.settings.hide_first === "1") {
		   			$('.products .product').fadeOut();
		   		} else {
		   			$('.products .product').fadeIn();
		   		}

	   			woocommerce_buying_guide.products = [];
	   			woocommerce_buying_guide.choicesMade = [];
	   			woocommerce_buying_guide.questionProducts = [];

	   			var buying_guide_link = $(this);
				var id = buying_guide_link.data('id');

				$('#woocommerce-buying-guide-' + id + ' .woocommerce-buying-guide-choice-radio').show();
				$('.woocommerce-buying-guide-success').fadeOut();
				$('.woocommerce-buying-guide-error').fadeOut();

	   			$('#woocommerce-buying-guide-start-container-' + id).fadeOut( function() {
	   				$('#woocommerce-buying-guide-progress-' + id ).fadeIn();
	   				$('#woocommerce-buying-guide-breadcrumb-' + id ).fadeIn();
	   				$('#woocommerce-buying-guide-progress-bar-' +  id).css('width', 0 + '%');

	   				$('#woocommerce-buying-guide-progress-breadcrumb-1').addClass('active');
	   				$('#woocommerce-buying-guide-question-choice-' + id + ' #woocommerce-buying-guide-question-container-1').fadeIn();
	   			});
	   		});
	   	},
	   	watchChoices : function () {

	   		var that = this;

	   		$('.woocommerce-buying-guide-choice-radio').on('click', function(e) {
	   			e.preventDefault();

	   			var choice = $(this);

	   			var products = choice.data('products').toString().split(',');
	   			var buying_guide = choice.data('buying-guide');
	   			var question = choice.data('question');
	   			var next = question + 1;

	   			var questionText = $('#woocommerce-buying-guide-question-container-' + question + ' .woocommerce-buying-guide-question').text();
	   			var choiceText = choice.find('.woocommerce-buying-guide-choice-radio-text').html();

	   			woocommerce_buying_guide.choicesMade[question] = '<div class="woocommerce-buying-guide-success-choices-made-choice"><span class="woocommerce-buying-guide-success-choices-made-choice-question">' + 
	   																questionText + '</span><span class="woocommerce-buying-guide-success-choices-made-choice-answer"> ' + choiceText + '</span></div>';
	   			woocommerce_buying_guide.questionProducts[question] = products;

   				if(question == 1) {
   					woocommerce_buying_guide.products = woocommerce_buying_guide.products.concat(products);
   				} else {
					woocommerce_buying_guide.products = that.intersect(woocommerce_buying_guide.products, products);
   				}
   				console.log(woocommerce_buying_guide.products);
   				
   				var totalQuestion = $('#woocommerce-buying-guide-question-choice-' +  buying_guide+ ' .woocommerce-buying-guide-question-container').length;
   				var step = 100 / totalQuestion;
   	
	   			$('#woocommerce-buying-guide-question-choice-' +  buying_guide+ ' #woocommerce-buying-guide-question-container-' + question).fadeOut(function() {
	   				var nextQuestion = $('#woocommerce-buying-guide-question-container-' + next);

	   				$('#woocommerce-buying-guide-progress-bar-' +  buying_guide).css('width', step * question + '%');
	   				$('#woocommerce-buying-guide-progress-breadcrumb-' +  question).removeClass('active');
	   				$('#woocommerce-buying-guide-progress-breadcrumb-' +  next).addClass('active');

	   				if(nextQuestion.length > 0 ) {

	   					if(woocommerce_buying_guide.settings.live_filter === "1" && woocommerce_buying_guide.settings.filter_after_choice === "1") {
		   					
							var siteProducts = $('.products .product');
							$(siteProducts).each(function(i, index) {
								var classes = $(this).attr('class');

							    var prod = classes.match(/post-\d+/);
								prod = prod[0].substring(5);

								if(jQuery.inArray(prod, woocommerce_buying_guide.products) !== -1) {
									$('.post-' + prod).fadeIn().removeClass('first');;
								} else {
									$('.post-' + prod).fadeOut();
								}
							});
   						}

						if(woocommerce_buying_guide.settings.adjust_choices === "1") {
							var nextChoices = $('#woocommerce-buying-guide-choices-container-' + next + ' .woocommerce-buying-guide-choice-radio');
							$(nextChoices).each(function(i, index) {

								var nextChoice = $(index);
								var nextChoiceProds = nextChoice.data('products').toString().split(',');

								var test = that.intersect(nextChoiceProds, woocommerce_buying_guide.products);
								if(test.length == 0) {
									nextChoice.hide();
								}
							});
						}

	   					$('#woocommerce-buying-guide-question-choice-' +  buying_guide + ' #woocommerce-buying-guide-question-container-' + next).fadeIn();

	   				} else {

						if(woocommerce_buying_guide.settings.live_filter === "1") {

							$('#woocommerce-buying-guide-modal-' + buying_guide).modal('hide');
							$('#woocommerce-buying-guide-start-container-' + buying_guide).fadeOut();

							var siteProducts = $('.products .product');
							if( woocommerce_buying_guide.products.length < 1) {
								$('#woocommerce-buying-guide-error-' + buying_guide).fadeIn();
								siteProducts.fadeOut();
							} else {
								$('#woocommerce-buying-guide-success-' + buying_guide).fadeIn();
								$('#woocommerce-buying-guide-success-choices-made-' + buying_guide).html( woocommerce_buying_guide.choicesMade.join('') + '<br>' );
	
								$(siteProducts).each(function(i, index) {
									var classes = $(this).attr('class');

								    var prod = classes.match(/post-\d+/);
									prod = prod[0].substring(5);

									if(jQuery.inArray(prod,  woocommerce_buying_guide.products) !== -1) {
										$('.post-' + prod).fadeIn().removeClass('first');;
									} else {
										$('.post-' + prod).fadeOut();
									}
								});
							}							
						} else {
							window.location.href = woocommerce_buying_guide.url_without_paramter + '?woocommerce-buying-guide=' + buying_guide + '&woocommerce-buying-guide-products=' + woocommerce_buying_guide.products.join();
						}
	   					return false;
	   				}
	   			});
   			});
	   	},
	   	checkSuccess : function () {
	   		var buying_guide_id = woocommerce_buying_guide.findGetParameter('woocommerce-buying-guide');
	   		var products_found = woocommerce_buying_guide.findGetParameter('woocommerce-buying-guide-products');

	   		
	   		if(buying_guide_id !== "") {
	   			
	   			if(products_found !== "") {
	   				
		   			$('#woocommerce-buying-guide-start-container-' + buying_guide_id).fadeOut(function() {
		   				$('#woocommerce-buying-guide-success-' + buying_guide_id).fadeIn();
		   			});
	   			} else {
	   				$('#woocommerce-buying-guide-start-container-' + buying_guide_id).fadeOut(function() {
		   				$('#woocommerce-buying-guide-error-' + buying_guide_id).fadeIn();
		   			});
	   			}
	   		}
	   	},
	   	watchModal : function () {
	   		$('.woocommerce-buying-guide-modal-start').on('click', function(e) {
	   			e.preventDefault();

	   			var buying_guide_link = $(this);
				var id = buying_guide_link.data('id');

	   			if(woocommerce_buying_guide.settings.hide_first === "1") {
		   			$('.products .product').fadeOut();
		   		} else {
		   			$('.products .product').fadeIn();
		   		}

				$('.woocommerce-buying-guide-success').fadeOut();
				$('.woocommerce-buying-guide-error').fadeOut();

				$('#woocommerce-buying-guide-modal-' + id + ' .woocommerce-buying-guide-choice-radio').show();

				$('.woocommerce-buying-guide-question-container').fadeOut();

				$('#woocommerce-buying-guide-modal-' + id).modal('show');
				$('#woocommerce-buying-guide-progress-' + id ).fadeIn();
   				$('#woocommerce-buying-guide-question-choice-' + id + ' #woocommerce-buying-guide-question-container-1').fadeIn();

	   		});

	   		$('.woocommerce-buying-guide-modal-close').on('click', function(e) {
	   			e.preventDefault();

	   			console.log('closed');

	   			var close_link = $(this);
	   			var id = close_link.data('id');

	   			$('#woocommerce-buying-guide-start-container-' + id).fadeIn();

	   		});

	   	},
		findGetParameter : function(parameterName) {
		    var result = null,
		        tmp = [];
		    location.search
		    .substr(1)
		        .split("&")
		        .forEach(function (item) {
		        tmp = item.split("=");
		        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
		    });
		    return result;
		},
		intersect : function(a, b) {
		    var t;
		    if (b.length > a.length) t = b, b = a, a = t; // indexOf to loop over shorter
		    return a.filter(function (e) {
		        return b.indexOf(e) > -1;
		    });
		},
		differences : function(a) {
		    return this.filter(function(i) {return a.indexOf(i) < 0;});
		}
    };

    jQuery(document).ready(function() {
    	var checkExists = $('.woocommerce-buying-guide');
    	if(checkExists.length > 0) {
    		woocommerce_buying_guide.init(buying_guide_options)
    	}
    }
	);

})(jQuery);