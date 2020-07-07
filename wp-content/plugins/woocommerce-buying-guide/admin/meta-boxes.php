<?php
/**
 * Registering meta boxes
 *
 * All the definitions of meta boxes are listed below with comments.
 * Please read them CAREFULLY.
 *
 * You also should read the changelog to know what has been changed before updating.
 *
 * For more information, please visit:
 * @link http://metabox.io/docs/registering-meta-boxes/
 */

add_filter( 'rwmb_meta_boxes', 'woocommerce_buying_guide_register_meta_boxes' );

/**
 * Register meta boxes
 *
 * Remember to change "woocommerce_buying_guide" to actual prefix in your project
 *
 * @param array $meta_boxes List of meta boxes
 *
 * @return array
 */

function woocommerce_buying_guide_register_meta_boxes( $meta_boxes )
{
	global $woocommerce_buying_guide_options;

	$attr_tax = wc_get_attribute_taxonomy_names();
	$temp = array();
	foreach ($attr_tax as $key => $value) {
		$temp[$value] = wc_attribute_taxonomy_name($value);
	}

	$attr_tax = array_combine($attr_tax, $attr_tax);

	$prefix = "woocommerce_buying_guide_";

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'product',
	);
	$products = get_posts( $args );
	$forSelect = array();
	foreach ($products as $product) {
		$forSelect[$product->ID] = $product->post_title . ' (ID: ' . $product->ID . ')';
	}
	
	// $meta_boxes[] = array(
	// 	'id'         => 'general',
	// 	'title'      => esc_html__( 'General', 'woocommerce-buying-guide' ),
	// 	'post_types' => array( 'buying-guide' ),
	// 	'context'    => 'normal',
	// 	'priority'   => 'high',
	// 	'autosave'   => true,
	// 	'fields'     => array(
	// 		array(
	// 			'name'        => esc_html__( 'Style', 'woocommerce-buying-guide' ),
	// 			'id'          => "{$prefix}style",
	// 			'type'        => 'select',
	// 			'options'     => array(
	// 				'text' => esc_html__( 'Text', 'woocommerce-buying-guide' ),
	// 				'image' => esc_html__( 'Image', 'woocommerce-buying-guide' ),
	// 				'modal' => esc_html__( 'Modal', 'woocommerce-buying-guide' ),
	// 			),
	// 			'std'         => 'text',
	// 			'placeholder' => esc_html__( 'Select a style', 'woocommerce-buying-guide' ),
	// 		),
	// 		array(
	// 			'name'        => esc_html__( 'Progress Style', 'woocommerce-buying-guide' ),
	// 			'id'          => "{$prefix}progress_style",
	// 			'type'        => 'select',
	// 			'options'     => array(
	// 				'bar' => esc_html__( 'Progress bar', 'woocommerce-buying-guide' ),
	// 				'breadcrumb' => esc_html__( 'Breadcrumb', 'woocommerce-buying-guide' ),
	// 			),
	// 			'std'         => 'bar',
	// 			'placeholder' => esc_html__( 'Select a style', 'woocommerce-buying-guide' ),
	// 		),
 //            array(
 //            	'name'        => esc_html__( 'Position', 'woocommerce-buying-guide' ),
 //                'id'       => "{$prefix}position",
 //                'type'     => 'select',
 //                'options'  => array(
 //                    'woocommerce_before_main_content' => esc_html__('Before Main Content', 'woocommerce-buying-guide'),
 //                    'woocommerce_archive_description' => esc_html__('After category description', 'woocommerce-buying-guide'),
 //                    'woocommerce_before_shop_loop' => esc_html__('Before Shop Loop', 'woocommerce-buying-guide'),
 //                    'woocommerce_after_shop_loop' => esc_html__('After Shop Loop', 'woocommerce-buying-guide'),
 //                    'woocommerce_after_main_content' => esc_html__('After Main Content', 'woocommerce-buying-guide'),
 //                    'woocommerce_sidebar' => esc_html__('In WooCommerce Sidebar', 'woocommerce-buying-guide'),
 //                ),
 //                'std' => 'woocommerce_archive_description',
 //            ),
	// 		array(
 //            	'name'        => esc_html__( 'Show Choices Made', 'woocommerce-buying-guide' ),
 //            	'desc'  		=> esc_html__( 'Show the choices, that your customers made after the success text. ', 'woocommerce-buying-guide' ),
 //                'id'       => "{$prefix}choices_made",
 //                'type'     => 'select',
 //                'options'  => array(
 //                    'no' => esc_html__('No', 'woocommerce-buying-guide'),
 //                    'yes' => esc_html__('Yes', 'woocommerce-buying-guide'),
 //                ),
 //                'std' => 'no',
 //            ),
	// 		array(
 //            	'name'        => esc_html__( 'Show on Shop Page', 'woocommerce-buying-guide' ),
 //            	'desc'  		=> esc_html__( 'Show the this buying guide on the main shop page base.', 'woocommerce-buying-guide' ),
 //                'id'       => "{$prefix}shop_page",
 //                'type'     => 'select',
 //                'options'  => array(
 //                    'no' => esc_html__('No', 'woocommerce-buying-guide'),
 //                    'yes' => esc_html__('Yes', 'woocommerce-buying-guide'),
 //                ),
 //                'std' => 'no',
 //            ),
	// 	),	
	// );

	add_filter('postbox_classes_buying-guide_general', 'add_metabox_general_classes');

	$questions = 4;
	$choices = 4;
	if( !empty($woocommerce_buying_guide_options['maxQuestions']) ) {
		$questions = $woocommerce_buying_guide_options['maxQuestions'];
	} 

	if( !empty($woocommerce_buying_guide_options['maxChoices']) ) {
		$choices = $woocommerce_buying_guide_options['maxChoices'];
	} 

	// Render Questions Meta Fields
	for ($i=1; $i <= $questions; $i++) { 

		$meta_boxes[] = array(
			'id'         => 'q-'. $i,
			'title'      => esc_html__( 'Question ' . $i, 'woocommerce-buying-guide' ),
			'post_types' => array( 'buying-guide' ),
			'context'    => 'normal',
			'priority'   => 'low',
			'autosave'   => true,
			'fields'     => array(
				array(
					'name'  => esc_html__( 'Question', 'woocommerce-buying-guide' ),
					'id'    => "{$prefix}question" . $i,
					'class' => 'question-title',
					'type'  => 'text',
					'placeholder' => esc_html__( 'Question ' . $i, 'woocommerce-buying-guide' ),
				),
				array(
					'name'  => esc_html__( 'Breadcrumb', 'woocommerce-buying-guide' ),
					'id'    => "{$prefix}breadcrumb" . $i,
					'type'  => 'text',
					'placeholder' => esc_html__( 'Breadcrumb ' . $i, 'woocommerce-buying-guide' ),
				),
				array(
					'name' => esc_html__( 'Intro text', 'woocommerce-buying-guide' ),
					'id'   => "{$prefix}intro" . $i,
					'type' => 'textarea',
					'cols' => 20,
					'rows' => 3,
				),
				array(
					'name' => esc_html__( 'Tooltip', 'woocommerce-buying-guide' ),
					'id'   => "{$prefix}tooltip" . $i,
					'type' => 'textarea',
					'cols' => 20,
					'rows' => 3,
				),
 				array(
	            	'name'        => esc_html__( 'Show Skip choice', 'woocommerce-buying-guide' ),
	            	'desc'  		=> esc_html__( 'Show a choice, that skips the question. This will take all products from all choices below over to the next question.', 'woocommerce-buying-guide' ),
	                'id'       => "{$prefix}skip" . $i,
	                'type'     => 'select',
	                'options'  => array(
	                    'no' => esc_html__('No', 'woocommerce-buying-guide'),
	                    'yes' => esc_html__('Yes', 'woocommerce-buying-guide'),
	                ),
	                'std' => 'no',
	            ),
				array(
					'name'  => esc_html__( 'Skip Choice Text', 'woocommerce-buying-guide' ),
					'desc'  		=> esc_html__( 'E.g. "I do not care", "I do not know", "Skip" etc.', 'woocommerce-buying-guide' ),
					'id'    => "{$prefix}skip_text" . $i,
					'type'  => 'text',
					'placeholder' => esc_html__( 'Skip', 'woocommerce-buying-guide' ),
				),
				array(
					'name'        => esc_html__( 'Choices by Attribute', 'woocommerce-buying-guide' ),
					'desc'  		=> esc_html__( 'Automatically create choices on Attributes.', 'woocommerce-buying-guide' ),
					'id'          => "{$prefix}attributes" . $i,
					'type'        => 'select',
					'options'     => $attr_tax,
					'multiple'    => false,
					'placeholder' => esc_html__( 'Select attributes', 'woocommerce-buying-guide' ),
				),
			),	
		);

		add_filter('postbox_classes_buying-guide_q-'. $i, 'add_metabox_question_classes');

		// Render Choices Meta Fields
		for ($ii=1; $ii <= $choices; $ii++) { 

			$meta_boxes[] = array(
				'id'         => 'q-' . $i . '-c-' . $ii,
				'title'      => esc_html__( 'Choice ' . $ii, 'woocommerce-buying-guide' ),
				'post_types' => array( 'buying-guide' ),
				'context'    => 'normal',
				'priority'   => 'low',
				'autosave'   => true,
				'fields'     => array(
					array(
						'name'  => esc_html__( 'Choice', 'woocommerce-buying-guide' ),
						'id'    => "{$prefix}choice" . $i . $ii,
						'type'  => 'text',
						'clone' => false,
						'placeholder' => esc_html__( 'Choice ' . $ii, 'woocommerce-buying-guide' ),
					),
					array(
						'name'        => esc_html__( 'Products', 'woocommerce-buying-guide' ),
						'desc'  		=> esc_html__( 'Products, that match your Choice', 'woocommerce-buying-guide' ),
						'id'          => "{$prefix}products" . $i . $ii,
						'type'        => 'select_advanced',
						'options'     => $forSelect,
						'multiple'    => true,
						'placeholder' => esc_html__( 'Select products', 'woocommerce-buying-guide' ),
					),
					array(
						'name'             => esc_html__( 'Icon', 'woocommerce-buying-guide' ),
						'id'               => "{$prefix}icon" . $i . $ii,
						'type'             => 'image_advanced',
						'force_delete'     => false,
						'max_file_uploads' => 1,
						'max_status'       => false,
					),
					array(
						'name' => esc_html__( 'Tooltip', 'woocommerce-buying-guide' ),
						'id'   => "{$prefix}tooltip" . $i . $ii,
						'type' => 'textarea',
						'cols' => 20,
						'rows' => 3,
					),
				),	
			);

			add_filter('postbox_classes_buying-guide_q-' . $i . '-c-' . $ii, 'add_metabox_choice_classes');
		}

		$meta_boxes[] = array(
				'id'         => 'q-' . $i . '-ac',
				'title'      => esc_html__( 'Add Choice', 'woocommerce-buying-guide' ),
				'post_types' => array( 'buying-guide' ),
				'priority'   => 'low',
				'context'    => 'normal',
				'fields'     => array(
					array(
						'id'   => "{$prefix}add_choice" . $i,
						'type' => 'button',
						'name' => ' ', // Empty name will "align" the button to all field inputs
					),
				),
		);
		add_filter('postbox_classes_buying-guide_q-' . $i . '-ac', 'add_metabox_add_choice_classes');

		$meta_boxes[] = array(
				'id'      => 'break' . $i,
				'title'      => esc_html__( 'break ' . $i, 'woocommerce-buying-guide' ),
				'post_types' => array( 'buying-guide' ),
				'priority'   => 'low',
				'fields'     => array(
					array(
						'id'   => "{$prefix}break" . $i,
						'type' => 'hidden',
						// Hidden field must have predefined value
						'std'  => esc_html__( 'Hidden value', 'your-prefix' ),
					),
				),
		);
		add_filter('postbox_classes_buying-guide_break' . $i, 'add_metabox_add_break_classes');

	}

	$meta_boxes[] = array(
			'id'      => 'add_question',
			'title'      => esc_html__( 'Add Question', 'woocommerce-buying-guide' ),
			'post_types' => array( 'buying-guide' ),
			'priority'   => 'low',
			'fields'     => array(
					array(
						'id'   => "{$prefix}add_question",
						'type' => 'button',
						'name' => ' ', // Empty name will "align" the button to all field inputs
					),
			),
	);
	add_filter('postbox_classes_buying-guide_add_question', 'add_metabox_add_question_classes');

	return $meta_boxes;
}

function add_metabox_general_classes($classes) {
    array_push($classes, 'general');
    return $classes;
}

function add_metabox_question_classes($classes) {
    array_push($classes, 'question');
    return $classes;
}

function add_metabox_choice_classes($classes) {
    array_push($classes, 'choice');
    return $classes;
}

function add_metabox_add_choice_classes($classes) {
    array_push($classes, 'add-choice');
    return $classes;
}

function add_metabox_add_break_classes($classes) {
    array_push($classes, 'break');
    return $classes;
}
function add_metabox_add_question_classes($classes) {
    array_push($classes, 'add-question');
    return $classes;
}
