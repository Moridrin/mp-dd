<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

require_once 'map-content.php';

function mp_dd_register_maps_post_type()
{

    $labels = array(
        'name'               => 'Maps',
        'singular_name'      => 'Map',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Map',
        'edit_item'          => 'Edit Map',
        'new_item'           => 'New Map',
        'view_item'          => 'View Map',
        'search_items'       => 'Search Maps',
        'not_found'          => 'No Maps found',
        'not_found_in_trash' => 'No Maps found in Trash',
        'parent_item_colon'  => 'Parent Map:',
        'menu_name'          => 'D&D Maps',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Maps filterable by category',
        'supports'            => array('title', 'editor', 'thumbnail', 'revisions'),
        'taxonomies'          => array('map_category'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-location-alt',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('map', $args);
}

add_action('init', 'mp_dd_register_maps_post_type');

function mp_dd_register_maps_taxonomy()
{
    register_taxonomy(
        'map_category',
        'map',
        array(
            'hierarchical' => true,
            'label'        => 'Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'map_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_register_maps_taxonomy');

function mp_dd_add_map_meta_boxes()
{
    add_meta_box('mp_dd_map_import', 'Import', 'mp_dd_map_import', 'map', 'advanced', 'default');
    add_meta_box('mp_dd_map_map_editor', 'Map Editor', 'mp_dd_map_map_editor', 'map', 'advanced', 'default');
    add_meta_box('mp_dd_map_info_editor', 'Info Editor', 'mp_dd_map_info_editor', 'map', 'advanced', 'default');
    add_meta_box('mp_dd_map_rooms_editor', 'Rooms Editor', 'mp_dd_map_rooms_editor', 'map', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_map_meta_boxes');

function mp_dd_map_import()
{
    $image_library_url = get_upload_iframe_src('media', null, 'type');
    ?>
    <p>
        You can generate dungeons from <a href="http://donjon.bin.sh/5e/dungeon/index.cgi">donjon.bin.sh</a> and upload the html file here.<br/>
        After you save the page, it will convert the uploaded HTML file and remove it afterwards.
    </p>
    <p>
        <a title="Upload File" href="<?php echo esc_url($image_library_url); ?>" id="upload-html-file" class="button thickbox">Upload File</a>
    </p>
    <?php
}

function mp_dd_map_map_editor()
{
    global $post;
    $content   = get_post_meta($post->ID, 'map', true);
    $editor_id = 'map_editor';
    wp_editor($content, $editor_id, $settings = array());
}

function mp_dd_map_info_editor()
{
    global $post;
    $content   = get_post_meta($post->ID, 'info', true);
    $editor_id = 'info_editor';
    wp_editor($content, $editor_id, $settings = array());
}

function mp_dd_map_rooms_editor()
{
    global $post;
    $content   = get_post_meta($post->ID, 'rooms', true);
    $editor_id = 'rooms_editor';
    wp_editor($content, $editor_id, $settings = array());
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_map_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $post_id;
    }

    //Editors
    update_post_meta($post->ID, 'map_editor', $_POST['map_editor']);
    update_post_meta($post->ID, 'info_editor', $_POST['info_editor']);
    update_post_meta($post->ID, 'rooms_editor', $_POST['rooms_editor']);

    //Import
    $attachedHTMLFiles = get_attached_media('text/html', $post_id);
    /** @var WP_Post $file */
    foreach ($attachedHTMLFiles as $file) {
        $converted = DonjonConverter::Convert($file->guid);
        update_post_meta($post->ID, 'map', $converted['map']);
        update_post_meta($post->ID, 'info', $converted['info']);
        update_post_meta($post->ID, 'rooms', $converted['rooms']);
        wp_delete_attachment($file->ID);
    }

    return $post_id;
}

add_action('save_post_map', 'mp_dd_save_map_meta', 1, 2);

function filter_wp_handle_upload($array, $var)
{
    if ($array['type'] == 'text/html') {
//        DonjonConverter::Convert($array['url']);
    }
    return $array;
}

// add the filter
add_filter('wp_handle_upload', 'filter_wp_handle_upload', 10, 2);

