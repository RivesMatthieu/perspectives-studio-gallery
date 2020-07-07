<?php
/**
 * Custom Post Type for Buying Guides and Taxonomies.
 */
class WooCommerce_Buying_Guide_Post_Type
{
    private $plugin_name;
    private $version;
    private $prefix;

    /**
     * Constructor.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     *
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->prefix = "woocommerce_buying_guide_";
    }

    /**
     * Init.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     *
     * @return bool
     */
    public function init()
    {
        $this->register_buying_guide_post_type();
        add_filter('screen_options_show_screen', array($this, 'screen_options'), 10, 2);
    }

    /**
     * Register Buying Guide Post Type.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     *
     * @return bool
     */
    public function register_buying_guide_post_type()
    {
        $singular = __('Buying Guide', 'woocommerce-buying-guide');
        $plural = __('Buying Guides', 'woocommerce-buying-guide');

        $labels = array(
            'name' => __('Buying Guides', 'woocommerce-buying-guide'),
            'all_items' => sprintf(__('%s', 'woocommerce-buying-guide'), $plural),
            'singular_name' => $singular,
            'add_new' => sprintf(__('New %s', 'woocommerce-buying-guide'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'woocommerce-buying-guide'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'woocommerce-buying-guide'), $singular),
            'new_item' => sprintf(__('New %s', 'woocommerce-buying-guide'), $singular),
            'view_item' => sprintf(__('View %s', 'woocommerce-buying-guide'), $plural),
            'search_items' => sprintf(__('Search %s', 'woocommerce-buying-guide'), $plural),
            'not_found' => sprintf(__('No %s found', 'woocommerce-buying-guide'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'woocommerce-buying-guide'), $plural),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'exclude_from_search' => false,
            'show_ui' => true,
            'menu_position' => 57,
            // 'show_in_menu' => 'edit.php?post_type=product',
            'rewrite' => array(
                'slug' => 'buying',
                'with_front' => FALSE
            ),
            'query_var' => 'buying-guide',
            'supports' => array('title', 'editor', 'author', 'thumbnail'),
            'menu_icon' => 'dashicons-welcome-learn-more',
            'taxonomies' => array('product_cat'),
        );

        register_post_type('buying-guide', $args);

    }

    public function screen_options($display_boolean, $wp_screen_object )
    {
        if(isset($_GET['post_type']) && ($_GET['post_type'] == "buying-guide")) {
            return false;
        }
        return true;
    }

    /**
     * Columns Head.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     *
     * @param string $columns Columnd
     *
     * @return string
     */
    public function columns_head($columns)
    {
        $output = array();
        foreach ($columns as $column => $name) {
            $output[$column] = $name;

            if ($column === 'title') {
                $output['visibility'] = __('Visibility', 'woocommerce-buying_guide');
            }
        }

        return $output;
    }

    /**
     * Columns Content.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     *
     * @param string $column_name Column Name
     *
     * @return string
     */
    public function columns_content($column_name)
    {
        global $post;

        if ($column_name == 'visibility') {
            $visibility = get_post_meta($post->ID, 'visibility', true);
            echo $visibility;
        }
    }

    /**
     * Add custom ticket metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $post_type [description]
     * @param   [type]                       $post      [description]
     */
    public function add_custom_metaboxes($post_type, $post)
    {
        add_meta_box('woocommerce-buying_guide-general', 'General', array($this, 'general'), 'buying-guide', 'normal', 'high');
        // add_meta_box('woocommerce-buying_guide-products', 'Products', array($this, 'products'), 'buying-guide', 'normal', 'high');
    }

    /**
     * Display Metabox Address
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function general()
    {
        global $post;

        wp_nonce_field(basename(__FILE__), 'woocommerce_buying_guide_meta_nonce');

        if($this->is_new_store()) {
            $style = 'text';
            $progress_style = 'bar';
            $position = 'woocommerce_archive_description';
            $choices_made = 'no';
            $shop_page = 'no';
        } else {
            $style = get_post_meta($post->ID, $this->prefix . 'style', true);
            $progress_style = get_post_meta($post->ID, $this->prefix . 'progress_style', true);
            $position = get_post_meta($post->ID, $this->prefix . 'position', true);
            $choices_made = get_post_meta($post->ID, $this->prefix . 'choices_made', true);
            $shop_page = get_post_meta($post->ID, $this->prefix . 'shop_page', true);
        }

        $styles = array(
            'text' => __( 'Text', 'woocommerce-buying-guide' ),
            'image' => __( 'Image', 'woocommerce-buying-guide' ),
            'modal' => __( 'Modal', 'woocommerce-buying-guide' ),
        );

        $progress_styles = array(
            'bar' => __( 'Progress bar', 'woocommerce-buying-guide' ),
            'breadcrumb' => __( 'Breadcrumb', 'woocommerce-buying-guide' ),
        );

        $positions = array(
            'woocommerce_before_main_content' => __('Before Main Content', 'woocommerce-buying-guide'),
            'woocommerce_archive_description' => __('After category description', 'woocommerce-buying-guide'),
            'woocommerce_before_shop_loop' => __('Before Shop Loop', 'woocommerce-buying-guide'),
            'woocommerce_after_shop_loop' => __('After Shop Loop', 'woocommerce-buying-guide'),
            'woocommerce_after_main_content' => __('After Main Content', 'woocommerce-buying-guide'),
            'woocommerce_sidebar' => __('In WooCommerce Sidebar', 'woocommerce-buying-guide'),
            'none' => __('None / Shortcode', 'woocommerce-buying-guide'),
        );

        $yes_no = array(
            'no' => __('No', 'woocommerce-buying-guide'),
            'yes' => __('Yes', 'woocommerce-buying-guide'),
        );

        echo '<div class="woocommerce-buying-guide-container">';

            echo '<div class="woocommerce-buying-guide-row">'; 

                echo '<div class="woocommerce-buying-guide-col-sm-12">';
                    echo '<label for="' . $this->prefix . 'style">' . __( 'Style', 'woocommerce-buying-guide' ) . '</label><br/>';
                    echo '<select name="' . $this->prefix . 'style" class="woocommerce-buying-guide-input-field">';
                    foreach ($styles as $key => $styleName) {
                        $selected = "";
                        if($style == $key) {
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="' . $key . '" ' . $selected . '>' . $styleName . '</option>';
                    }
                    echo '</select>';
                echo '</div>';

                echo '<div class="woocommerce-buying-guide-col-sm-12">';
                    echo '<label for="' . $this->prefix . 'progress_style">' . __( 'Progress Style', 'woocommerce-buying-guide' ) . '</label><br/>';
                    echo '<select name="' . $this->prefix . 'progress_style" class="woocommerce-buying-guide-input-field">';
                    foreach ($progress_styles as $key => $progress_style_name) {
                        $selected = "";
                        if($progress_style == $key) {
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="' . $key . '" ' . $selected . '>' . $progress_style_name . '</option>';
                    }
                    echo '</select>';
                echo '</div>';

                echo '<div class="woocommerce-buying-guide-col-sm-12">';
                    echo '<label for="' . $this->prefix . 'position">' . __( 'Position', 'woocommerce-buying-guide' ) . '</label><br/>';
                    echo '<select name="' . $this->prefix . 'position" class="woocommerce-buying-guide-input-field">';
                    foreach ($positions as $key => $position_name) {
                        $selected = "";
                        if($position == $key) {
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="' . $key . '" ' . $selected . '>' . $position_name . '</option>';
                    }
                    echo '</select>';
                echo '</div>';

                echo '<div class="woocommerce-buying-guide-col-sm-12">';
                    echo '<label for="' . $this->prefix . 'choices_made">' . __( 'Show Choices Made', 'woocommerce-buying-guide' ) . '</label><br/>';
                    echo '<select name="' . $this->prefix . 'choices_made" class="woocommerce-buying-guide-input-field">';
                    foreach ($yes_no as $key => $choices_made_name) {
                        $selected = "";
                        if($choices_made == $key) {
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="' . $key . '" ' . $selected . '>' . $choices_made_name . '</option>';
                    }
                    echo '</select>';
                echo '</div>';

                echo '<div class="woocommerce-buying-guide-col-sm-12">';
                    echo '<label for="' . $this->prefix . 'shop_page">' . __( 'Show on Shop Page', 'woocommerce-buying-guide' ) . '</label><br/>';
                    echo '<select name="' . $this->prefix . 'shop_page" class="woocommerce-buying-guide-input-field">';
                    foreach ($yes_no as $key => $shop_page_name) {
                        $selected = "";
                        if($shop_page == $key) {
                            $selected = 'selected="selected"';
                        }
                        echo '<option value="' . $key . '" ' . $selected . '>' . $shop_page_name . '</option>';
                    }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

    }

    /**
     * Display Metabox Address
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function products()
    {
        global $post;

        wp_nonce_field(basename(__FILE__), 'woocommerce_buying_guide_meta_nonce');

        $array = array(
            // Questions
            1 => array(
                'type' => 'question',
                'question' => 'Whats fav?',
                'breadcrumb' => 'Fav',
                'intro' => 'Intro Text',
                'tooltip' => 'Tool Tip URL',
                'skip' => '', // select yes / no
                'skip_text' => '',
                'attributes' => '', // if attributes remove choices
                'choices' => array(
                    1 => array(
                        'type' => 'choice',
                        'choice' => 'Choice 1',
                        'products' => '', // multiselect
                        'icon' => '',
                        'tooltip' => '',
                    ),
                ),
            ),
        );

        ?>
        <div class="questions-container">
            <div class="questions-left">

                <div id="nestable-menu">
                    <button type="button" data-action="add-item" class="button-primary">Add new item</button>
                    <button type="button" data-action="expand-all" class="button-secondary">Expand All</button>
                    <button type="button" data-action="collapse-all" class="button-secondary">Collapse All</button>
                    <!-- <button type="button" data-action="replace-item" class="button-secondary">Replace item 10</button> -->
                </div>

                <div class="dd" id="nestable">
                   
                </div>
            </div>
            <div class="questions-right">
                <div class="questions-right-notice"><?php _e('Click or Create an item on the left ...', 'woocommerce-buying-guide') ?></div>
                <div class="questions-loader-container">
                    <div class="questions-loader"></div>
                </div>
                <div class="question-right-item question-right-item-question">
                    <div class="question-input-wrapper">
                        <select name="type">
                            <option value=""><?php _e('Select a Type', 'woocommerce-buying-guide') ?></option>
                            <option value="question"><?php _e('Question', 'woocommerce-buying-guide') ?></option>
                            <option value="choice"><?php _e('Choice', 'woocommerce-buying-guide') ?></option>
                        </select>
                    </div>
                    <div class="question-input-wrapper">
                        <input type="text" name="name" placeholder="<?php _e('Question', 'woocommerce-buying-guide') ?> ...">
                    </div>
                    <div class="question-input-wrapper">
                        <input type="text" name="breadcrumb" placeholder="<?php _e('Breadcrumb title', 'woocommerce-buying-guide') ?> ...">
                    </div>
                    <div class="question-input-wrapper">
                        <textarea type="text" name="intro" placeholder="<?php _e('Intro text', 'woocommerce-buying-guide') ?> ..."></textarea>
                    </div>
                    <div class="question-input-wrapper">
                        <textarea type="text" name="tooltip" placeholder="<?php _e('Tooltip text', 'woocommerce-buying-guide') ?> ..."></textarea>
                    </div>
                    <div class="question-input-wrapper">
                        <select name="skip_choice">
                            <option value=""><?php _e('Select if Choice can be skipped', 'woocommerce-buying-guide') ?></option>
                            <option value="yes"><?php _e('Yes', 'woocommerce-buying-guide') ?></option>
                            <option value="no"><?php _e('No', 'woocommerce-buying-guide') ?></option>
                        </select>
                    </div>
                    <div class="question-input-wrapper">
                        <input type="text" name="skip_choice_text" placeholder="<?php _e('Skip text', 'woocommerce-buying-guide') ?> ...">
                    </div>
                    <div class="question-input-wrapper">
                        <select name="choices_by_attribute">
                            <option value=""><?php _e('Select if choices should created automatically', 'woocommerce-buying-guide') ?></option>
                            <option value="yes"><?php _e('Yes', 'woocommerce-buying-guide') ?></option>
                            <option value="no"><?php _e('No', 'woocommerce-buying-guide') ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="questions-clear"></div>
        </div>
        
        <?php

        // echo '<div class="woocommerce-buying_guide-container">';
        //     echo '<div class="woocommerce-buying_guide-row">';
        //         echo '<div class="woocommerce-buying_guide-col-sm-6">';
        //             echo '<label for="' . $this->prefix . 'address1">' . __( 'Address Line 1', 'woocommerce-buying_guide' ) . '</label><br/>';
        //             echo '<input class="woocommerce-buying_guide-input-field" name="' . $this->prefix . 'address1" value="' . $address1 . '" type="text">';
        //         echo '</div>';
            
        //         echo '<div class="woocommerce-buying_guide-col-sm-6">';
        //             echo '<label for="' . $this->prefix . 'address2">' . __( 'Address Line 1', 'woocommerce-buying_guide' ) . '</label><br/>';
        //             echo '<input class="woocommerce-buying_guide-input-field" name="' . $this->prefix . 'address2" value="' . $address2 . '" type="text">';
        //         echo '</div>';
        //     echo '</div>';

        //     echo '<div class="woocommerce-buying_guide-row">';
        //         echo '<div class="woocommerce-buying_guide-col-sm-6">';
        //             echo '<label for="' . $this->prefix . 'zip">' . __( 'ZIP', 'woocommerce-buying_guide' ) . '</label><br/>';
        //             echo '<input class="woocommerce-buying_guide-input-field" name="' . $this->prefix . 'zip" value="' . $zip . '" type="text">';
        //         echo '</div>';
            
        //         echo '<div class="woocommerce-buying_guide-col-sm-6">';
        //             echo '<label for="' . $this->prefix . 'city">' . __( 'City', 'woocommerce-buying_guide' ) . '</label><br/>';
        //             echo '<input class="woocommerce-buying_guide-input-field" name="' . $this->prefix . 'city" value="' . $city . '" type="text">';
        //         echo '</div>';
        //     echo '</div>';

           
        // echo '</div>';
    }

    private function is_new_store()
    {
        global $pagenow;

        if (!is_admin()) return false;

        return in_array( $pagenow, array( 'post-new.php' ) );
    }

    /**
     * Save Custom Metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @param   [type]                       $post_id [description]
     * @param   [type]                       $post    [description]
     * @return  [type]                                [description]
     */
    public function save_custom_metaboxes($post_id, $post)
    {
        global $woocommerce_buying_guide_options;

        if($post->post_type !== "buying-guide") {
            return false;
        }

        // Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID)) {
            return $post->ID;
        }

        if ($post->post_type == 'revision') {
            return false;
        }

        if (!isset($_POST['woocommerce_buying_guide_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_buying_guide_meta_nonce'], basename(__FILE__))) {
            return false;
        }

        $possible_inputs = array(
            'style',
            'progress_style',
            'position',
            'choices_made',
            'shop_page',
        );


        // Add values of $ticket_meta as custom fields
        foreach ($possible_inputs as $possible_input) {
            $possible_input = $this->prefix . $possible_input;

            $val = isset($_POST[$possible_input]) ? $_POST[$possible_input] : '';
            update_post_meta($post->ID, $possible_input, $val);
        }
    }

    public function get_item()
    {
        if(!isset($_POST['id']) || empty($_POST['id'])) {
            die('ID missing');
        }

        $id = $_POST['id'];

        $data = array(
            'type' => 'question',
            'name' => 'What are you looking for?',
            'breadcrumb' => 'Product Type',
            'intro' => 'What product type are you looking at?',
            'tooltip' => 'Tooltip',
            'skip_choice' => 'no',
            'skip_choice_text' => 'Skip',
            'choices_by_attribute' => '',
        );

        echo json_encode($data);
        die();
    }

    public function get_buying_guide_data()
    {
        global $post;

        if(!is_object($post)) {
            return false;
        }

        if($post->post_type !== "buying-guide") {
            return false;
        }

        $data = array(
            array(
                "id" => 1,
                "content" => "Question 1",
                "type" => "question",
            ),
            array(
                "id" => 2,
                "content" => "Question 2",
                "type" => "question",
                "children" => array(
                    array(
                        "id" => 3,
                        "content" => "Choice 1",
                        "type" => "choice",
                    ),
                    array(
                        "id" => 4,
                        "content" => "Choice 1",
                        "type" => "choice",
                        "value" => "Item 5 value",
                    ),
                )
            ),
        );
// {
//                         "id": 1,
//                         "content": "First item",
//                         "type" : "question",
//                         // "classes": ["dd-nochildren"]
//                     },
//                     {
//                         "id": 2,
//                         "content": "Second item",
//                         "children": [
//                             {
//                                 "id": 3,
//                                 "content": "Item 3"
//                             },
//                             {
//                                 "id": 4,
//                                 "content": "Item 4"
//                             },
//                             {
//                                 "id": 5,
//                                 "content": "Item 5",
//                                 "value": "Item 5 value",
//                                 "foo": "Bar",
//                                 "children": [
//                                     {
//                                         "id": 6,
//                                         "content": "Item 6"
//                                     },
//                                     {
//                                         "id": 7,
//                                         "content": "Item 7"
//                                     },
//                                     {
//                                         "id": 8,
//                                         "content": "Item 8"
//                                     }
//                                 ]
//                             }
//                         ]
//                     }
//         );

        ?>
        <script type="text/javascript">
            var buying_guide_data = <?php echo json_encode($data) ?>;
        </script>
        <?php
    }
}
