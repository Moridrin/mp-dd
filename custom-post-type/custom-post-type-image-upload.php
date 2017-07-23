<?php

/*
Plugin Name: Custom Post Type Image Upload
Plugin URI: http://www.foxrunsoftware.net/articles/wordpress/custom-post-type-with-image-uploads/
Description: Custom Post Type Image Upload Demo
Author: Justin Stern
Author URI: http://www.foxrunsoftware.net
Version: 1.0

	Copyright: � 2012 Justin Stern (email : justin@foxrunsoftware.net)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

class Custom_Post_Type_Image_Upload
{

    public function __construct()
    {

        add_action('init', [&$this, 'init']);

        if (is_admin()) {
            add_action('admin_init', [&$this, 'admin_init']);
        }
    }


    /** Frontend methods ******************************************************/

    /**
     * Register the custom post type
     */
    public function init()
    {
        register_post_type('book', ['public' => true, 'label' => 'Books']);
    }


    /** Admin methods ******************************************************/

    /**
     * Initialize the admin, adding actions to properly display and handle
     * the Book custom post type add/edit page
     */
    public function admin_init()
    {
        global $pagenow;

        if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php') {

            add_action('add_meta_boxes', [&$this, 'meta_boxes']);
            add_filter('enter_title_here', [&$this, 'enter_title_here'], 1, 2);

            add_action('save_post', [&$this, 'meta_boxes_save'], 1, 2);
        }
    }

    /**
     * Save meta boxes
     * Runs when a post is saved and does an action which the write panel save scripts can hook into.
     */
    public function meta_boxes_save($post_id, $post)
    {
        if (empty($post_id) || empty($post) || empty($_POST)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (is_int(wp_is_post_revision($post))) {
            return;
        }
        if (is_int(wp_is_post_autosave($post))) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if ($post->post_type != 'book') {
            return;
        }

        $this->process_book_meta($post_id, $post);
        update_post_meta($post_id, 'building_label_12_location', $_POST['building_label_12_location']);
        update_post_meta($post_id, 'building_label_13_location', $_POST['building_label_13_location']);
    }

    /**
     * Function for processing and storing all book data.
     */
    private function process_book_meta($post_id, $post)
    {
        update_post_meta($post_id, '_image_id', $_POST['upload_image_id']);
    }

    /**
     * Set a more appropriate placeholder text for the New Book title field
     */
    public function enter_title_here($text, $post)
    {
        if ($post->post_type == 'book') {
            return __('Book Title');
        }
        return $text;
    }

    /**
     * Add and remove meta boxes from the edit page
     */
    public function meta_boxes()
    {
        add_meta_box('book-image', __('Book Image'), [&$this, 'book_image_meta_box'], 'book', 'normal', 'high');
    }

    /**
     * Display the image meta box
     */
    public function book_image_meta_box()
    {
        global $post;



        $image_id = get_post_meta($post->ID, '_image_id', true);
        $building_label_12_locationTranslate = get_post_meta($post->ID, 'building_label_12_location', true);
        if ($building_label_12_locationTranslate) {
            list($building12Left, $building12Top) = explode(', ', explode(')', explode('(', $building_label_12_locationTranslate)[1])[0]);
        } else {
            $building12Left = 0;
            $building12Top  = 0;
        }
        $building_label_13_locationTranslate = get_post_meta($post->ID, 'building_label_13_location',true);
        $image_src = wp_get_attachment_url($image_id);

        ?>
        <div style="overflow-x: auto; overflow-y: hidden;">
            <div id="map" style="width: 700px; margin: auto; position: relative">
                <img id="book_image" src="<?php echo $image_src ?>" style="max-width:100%;" />
                <aside draggable="true" id="dragme" style="position:  absolute; top: -20px; color: #FFFFFF; background: black; height: 30px; width: 30px; text-align: center; display: block; border: 3px solid #6a1b9a; border-radius: 20%;font-size: 9px;line-height: 25px;">12</aside>
            </div>
        </div>
        <input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $image_id; ?>"/>
        <p>
            <a title="<?php esc_attr_e('Set book image') ?>" href="#"
               id="set-map-image"><?php _e('Set book image') ?></a>
            <a title="<?php esc_attr_e('Remove book image') ?>" href="#" id="remove-map-image"
               style="<?php echo(!$image_id ? 'display:none;' : ''); ?>"><?php _e('Remove book image') ?></a>
        </p>
        <?php
    }
}

// finally instantiate our plugin class and add it to the set of globals
$GLOBALS['custom_post_type_image_upload'] = new Custom_Post_Type_Image_Upload();
