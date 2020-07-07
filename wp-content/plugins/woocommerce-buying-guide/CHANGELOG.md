# Changelog
======
1.2.0
======
- NEW:	Added shortcode functionality
		[[woocommerce_buying_guide id="275" order="ASC" oderby="date"]]
		Example: https://welaunch.io/plugins/woocommerce-buying-guide/shortcode-example/
		FAQ: https://welaunch.io/plugins/woocommerce-buying-guide/faq/shortcode/
- FIX:	Added multiple new classes / divs for better custom styline

======
1.1.12
======
- NEW: 	Option to disalbe font awesome icons loading

======
1.1.11
======
- FIX:	2 digit question container stacked wrong

======
1.1.10
======
- FIX:	Adjust choices now possible without live filtering
- FIX:	Performance improvements

======
1.1.9
======
- FIX:	Flatsome support
- FIX:	Performance Improvements

======
1.1.8
======
- FIX:  JS Diff issue with other plugin

======
1.1.7
======
- NEW:  Added prefix to all bootstrap css classes

======
1.1.6
======
- FIX:  issues with live filtering disabled

======
1.1.5
======
- FIX:  WP 4.9 compatibility

======
1.1.4
======
- NEW: 	Hide all products from the beginning
		See Settings > General > Hide Products First

======
1.1.3
======
- NEW: 	Adjust Choices (will hide next choices if no matching products were found)
		See Settings > General > Adjust Choices

======
1.1.2
======
- FIX: catalog visibilty query problem

======
1.1.1
======
- FIX: Breadcrumb notice

======
1.1.0
======
- NEW:  Apply a buying guide on the shop page 
		See buying guides > edit > "Show on Shop Page"
- NEW:  Complete redesigned Admin panel for creating / editing guides easier
- NEW: 	Filters:
		Priority of action callbacks (woocommerce_buying_guide_priority)

		Image Rendering HTML (woocommerce_buying_image_render_html)
		Modal Rendering HTML (woocommerce_buying_modal_render_html)
		Text Rendering HTML (woocommerce_buying_text_render_html)

		Questions & Choices HTML (woocommerce_buying_guide_questions_and_choices_html)

		Error Message HTML (woocommerce_buying_guide_error_message_html)
		Success Message HTML (woocommerce_buying_guide_success_message_html)

======
1.0.7
======
- FIX: Set page in query for non live filtering

======
1.0.6
======
- FIX: Pagination not correct due to product category query counting buying guides

======
1.0.5
======
- NEW: Option to remove pagination from product category pages (to use live filtering)
- NEW: Without live filtering the start again button links to the category page to show all products again
- FIX: Buying guide not redirecting to correctly when on a paged product category 

======
1.0.4
======
- NEW: Show choices made after completing the buying guide (check Buying Guide > Choice Made (yes/no))
- FIX: Progess bar set to 0 width after buying guide restart
- FIX: Breadcrumb start again - 1. not active 

======
1.0.3
======
- NEW: Show a Skip Choice for each new question (buying guide > question X > skip choice)
       Skipping a choice means, that all products from the choices will be taken over to the next question
- NEW: Set a custom skip choice text
- FIX: HTML choice id container
- FIX: Undefined index in public
- FIX: Undefined index: type in Meta Boxes plugin

======
1.0.2
======
- NEW: Show breadcrumb in progress bar
- NEW: Live filtering after a choice was made
- FIX: Meta Box order not correct when setting max choices / questions
- FIX: Max-Height for product selection to not break choice boxes in backend 
- FIX: Implode Error check

======
1.0.1
======
- NEW: 	Show product ID in Buying Guide product selection
- NEW: 	Create choices automatically by woocommerce attributes
		no need to create choices and product assignments manually
- FIX:	Do not load bootstrap activated by default
- FIX:	Max Choices do not display
- FIX:	Buying Guides should not be public - they have to be assigned 
		to a product category and appear automatically

======
1.0.0
======
- Inital release

Future
======
- Depending Questions
- Go Step back
- Show Likely Matches