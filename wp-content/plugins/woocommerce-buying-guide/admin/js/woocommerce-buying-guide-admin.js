(function( $ ) {

	// USE STRICT
    "use strict";

    var woocommerce_buying_guide_admin = {

        init : function (options) {

        	woocommerce_buying_guide_admin.defaults = {
				live_filter: "1",
			};

			woocommerce_buying_guide_admin.settings = $.extend( {}, woocommerce_buying_guide_admin.defaults, options );
			woocommerce_buying_guide_admin._defaults = woocommerce_buying_guide_admin.defaults;

			woocommerce_buying_guide_admin.nestableList = $('#nestable');
			woocommerce_buying_guide_admin.nestableMenu = $('#nestable-menu');
			woocommerce_buying_guide_admin.notice = $('.questions-right-notice');
			woocommerce_buying_guide_admin.loader = $('.questions-loader-container');
			woocommerce_buying_guide_admin.right_item = $('.question-right-item');
			
			if(typeof buying_guide_data.length == "undefined") {
				return false;
			}

            woocommerce_buying_guide_admin.initNestable();
            woocommerce_buying_guide_admin.initNestableMenu();
            woocommerce_buying_guide_admin.itemClick();

        },
        // Init Nestable
		initNestable : function () {
			
			var that = this;

	        that.nestableList.nestable({
	            group: 1,
	            json: buying_guide_data,
	            contentCallback: function(item) {
	                var content = item.content || '' ? item.content : item.id;
	                content += ' <i>(id = ' + item.id + ')</i>';
	                return content;
	            }
	        });
	   	},
	   	// Init the Menu
	   	initNestableMenu : function() {

			var lastId = 12;
			var that = this;

			that.nestableMenu.on('click', function(e) {
	            var target = $(e.target),
	                action = target.data('action');
	            if(action === 'expand-all') {
	                $('.dd').nestable('expandAll');
	            }
	            if(action === 'collapse-all') {
	                $('.dd').nestable('collapseAll');
	            }
	            if(action === 'add-item') {
	                var newItem = {
	                    "id": ++lastId,
	                    "content": "Item " + lastId,
	                };
	                $('#nestable').nestable('add', newItem);
	            }
	        });

	   	},
	   	// Get & Display Item Data
	   	itemClick : function() {

	   		var that = this;

			that.nestableList.on('mousedown', '.dd-item', function(e) {

				var id = $(this).data('id');
				if(!id) {
					alert('ID missing!');
					return false;
				}

				that.right_item.hide(0, function() {
					that.loader.show(0);
					that.notice.hide(0);

					$.ajax({
						url: ajaxurl,
						type: 'post',
						dataType: 'JSON',
						data: {
							action: 'woocommerce_buying_guide_get_item',
							id: id
						},
						success : function( response ) {

				  			$.each(response, function(i, index) {
				  				console.log(i);
				  				console.log(index);

				  				$('.question-right-item input[name="' + i + '"]').val(index);
				  				$('.question-right-item select[name="' + i + '"]').val(index);
				  				$('.question-right-item textarea[name="' + i + '"]').val(index);
				  			});

					  		that.loader.hide(0, function() {
					  			that.right_item.show(0);	
					  		});
						},
						error: function(jqXHR, textStatus, errorThrown) {
						    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
						}
					});
				});
	        });	
	   	},

    };

    jQuery(document).ready(function() {
    	var checkExists = $('.post-type-buying-guide');
    	if(checkExists.length > 0) {
    		woocommerce_buying_guide_admin.init()
    	}
    });

	$(document).ready(function() {

		/*
		Update Output
		*/
		// var updateOutput = function (e) {
		// 	var list = e.length ? e : $(e.target),
		// 		output = list.data('output');

		// 	if (window.JSON) {
		// 		output.val(window.JSON.stringify(list.nestable('serialize')));
		// 	} else {
		// 		output.val('JSON browser support required for this demo.');
		// 	}
		// };

		/*
		Nestable 1
		*/

		var json = [
		            {
		                "id": 1,
		                "content": "First item",
		                "type" : "question",
		                // "classes": ["dd-nochildren"]
		            },
		            {
		                "id": 2,
		                "content": "Second item",
		                "children": [
		                    {
		                        "id": 3,
		                        "content": "Item 3"
		                    },
		                    {
		                        "id": 4,
		                        "content": "Item 4"
		                    },
		                    {
		                        "id": 5,
		                        "content": "Item 5",
		                        "value": "Item 5 value",
		                        "foo": "Bar",
		                        "children": [
		                            {
		                                "id": 6,
		                                "content": "Item 6"
		                            },
		                            {
		                                "id": 7,
		                                "content": "Item 7"
		                            },
		                            {
		                                "id": 8,
		                                "content": "Item 8"
		                            }
		                        ]
		                    }
		                ]
		            },
		            {
		                "id": 9,
		                "content": "Item 9"
		            },
		            {
		                "id": 10,
		                "content": "Item 10",
		                "children": [
		                    {
		                        "id": 11,
		                        "content": "Item 11",
		                        "children": [
		                            {
		                                "id": 12,
		                                "content": "Item 12"
		                            }
		                        ]
		                    }
		                ]
		            }
		        ];

	        
	        // activate Nestable for list 1



		

		

		//.on('change', updateOutput);


		// updateOutput($('#nestable').data('output', $('#nestable-output')));


		var questionContainers = $('.postbox[id^=q-]');
		var choices = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
		console.log(questionContainers);

		var postTypeCheck = $('body.post-type-buying-guide');

		if(postTypeCheck.length > 0) {

		    $('.meta-box-sortables').sortable({
		        disabled: true
		    });

		    $('#authordiv').hide();

		    $('.postbox .handlediv').unbind('click.postboxes');

			$.each(questionContainers, function(i, index) {

				var questionContainer = $(this);
				var questionID = questionContainer.attr("id");
				if(questionID.length !== 3 && questionID.length !== 4) {
					return;
				}

				questionID = parseInt($(this).attr("id").replace('q-',''), 10);

				var question = $('#woocommerce_buying_guide_question' + questionID).val();
				if(question == "") {
					questionContainer.slideUp();
					$.each(choices, function(i, choice) {
						$('#q-' + questionID + '-c-' + i).slideUp();
					});
					$('#q-' + questionID + '-ac').slideUp();
				}

				var attributes = $('#woocommerce_buying_guide_attributes' + questionID).val();
				if(attributes !== "") {
					$.each(choices, function(i, choice) {
						$('#q-' + questionID + '-c-' + i).slideUp();
					});
					$('#q-' + questionID + '-ac').slideUp();
				}

				$.each(choices, function(i, choice) { 

					var choiceText = $('#woocommerce_buying_guide_choice' + questionID + i).val();
					if(choiceText == "" && i !== 1) {
						$('#q-' + questionID + '-c-' + i).slideUp();	
					}
					
				});

				$('#woocommerce_buying_guide_attributes' + questionID).on('change', function(e) {
					var attribute_val = $(this).val();
					if(attribute_val !== "") {
						$('#q-' + questionID + '-ac').slideUp();
						$.each(choices, function(i, choice) {
							$('#q-' + questionID + '-c-' + i).slideUp();
						});
					} else {
						$('#q-' + questionID + '-c-1').slideDown();
						$.each(choices, function(i, choice) {

							var choiceText = $('#woocommerce_buying_guide_choice' + questionID + i).val();
							if(choiceText != "") {
								$('#q-' + questionID + '-c-' + i).slideDown();
							}
						});
						$('#q-' + questionID + '-ac').slideDown();
					}
				});

				$('#q-' + questionID + '-ac').on('click', function(e) {
					$.each(choices, function(i, choice) {
						var toCheck = $('#q-' + questionID + '-c-' + i);
						if(toCheck.css('display') == 'none') {
							toCheck.slideDown();
							return false;
						}
					});
				});
			});

			$('.choice .toggle-indicator').on('click', function(e) {
				var choiceContainer = $(this).closest('.choice');

				choiceContainer.find('.rwmb-text').val('');
				if(choiceContainer.attr('id').indexOf('c-1') < 0) {
					choiceContainer.slideUp();
				} else {
					choiceContainer    
					.animate({'left':(-10)+'px'},200)
				    .animate({'left':(+20)+'px'},200)
				    .animate({'left':(-10)+'px'},200)
				    .animate({'left':(0)+'px'},200);
				}
			});

			$('#add_question').on('click', function(e) {
				$.each(questionContainers, function(i, index) {

					var $this = $(this);
					var questionID = $this.attr("id");

					if(questionID.length !== 3 && questionID.length !== 4) {
						return;
					}
					questionID = parseInt($(this).attr("id").replace('q-',''), 10);
					if($this.css('display') == 'none') {
						$this.slideDown();
						$('#q-' + questionID + '-c-1').slideDown();
						$('#q-' + questionID + '-ac').slideDown();
						return false;
					}
				});
			});

			$('.question .toggle-indicator').on('click', function(e) {
				var questionContainer = $(this).closest('.question');

				questionContainer.find('.question-title .rwmb-text').val('');
				if(questionContainer.attr('id').indexOf('q-1') < 0) {
					questionContainer.slideUp();

					var questionID = questionContainer.attr("id");
					questionID = parseInt(questionID.replace('q-',''), 10);

					$.each(choices, function(i, choice) {
						var choiceCont = $('#q-' + questionID + '-c-' + i);
						choiceCont.find('.rwmb-text').val('');
						choiceCont.slideUp();
						$('#q-' + questionID + '-ac').hide();
					});
				} else {
					questionContainer    
					.animate({'left':(-10)+'px'},200)
				    .animate({'left':(+20)+'px'},200)
				    .animate({'left':(-10)+'px'},200)
				    .animate({'left':(0)+'px'},200);
				}
			});
		}
	});

})( jQuery );
