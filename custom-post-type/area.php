<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a area is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_areas_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['areas'] = array(
        0  => '',
        1  => 'Area updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View Area</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'Area updated.',
        5  => isset($_GET['revision']) ? 'Area restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'Area published. <a href="' . esc_url(get_permalink($post_ID)) . '">View area</a>',
        7  => 'Area saved.',
        8  => 'Area submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview area</a>',
        9  => 'Area scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview area</a>',
        10 => 'Area draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview area</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_areas_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Areas
 */
function mp_dd_areas_post()
{

    $labels = array(
        'name'               => 'Areas',
        'singular_name'      => 'Area',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Area',
        'edit_item'          => 'Edit Area',
        'new_item'           => 'New Area',
        'view_item'          => 'View Area',
        'search_items'       => 'Search Areas',
        'not_found'          => 'No Areas found',
        'not_found_in_trash' => 'No Areas found in Trash',
        'parent_item_colon'  => 'Parent Area:',
        'menu_name'          => 'Areas',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Areas filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array('area_type'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-admin-multisite',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('area', $args);
}

add_action('init', 'mp_dd_areas_post');
#endregion

#region Category Taxonomy
/**
 * This function registers a taxonomy for the categories.
 */
function mp_dd_area_type_taxonomy()
{
    register_taxonomy(
        'area_type',
        'area',
        array(
            'hierarchical' => true,
            'label'        => 'Area Types',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'area_type',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_area_type_taxonomy');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_area_meta_boxes()
{
    add_meta_box('mp_dd_area_include_tag', 'Tags', 'mp_dd_area_include_tag', 'area', 'after_title', 'high');
    add_meta_box('mp_dd_area_info', 'Info', 'mp_dd_area_info', 'area', 'advanced', 'high');
    add_meta_box('mp_dd_area_map', 'Map', 'mp_dd_area_map', 'area', 'advanced', 'high');
}

add_action('add_meta_boxes', 'mp_dd_area_meta_boxes');

function mp_dd_area_include_tag()
{
    global $post;
    ?>
    <p>You can insert one of these tags in a post to include the area.</p>
    <p><code>[area-url-<?= $post->ID ?>]</code> place this in the href="" part of a link to open the area as popup.</p>
    <p><code>[area-link-<?= $post->ID ?>]</code> place this to show a link (with the area title) to open the area as a popup.</p>
    <p><code>[area-title-<?= $post->ID ?>]</code> place this somewhere you want to display the area title.</p>
    <p><code>[area-content-<?= $post->ID ?>]</code> place this somewhere you want to display the area content.</p>
    <?php
}

function mp_dd_area_info()
{
    global $post;
    global $wpdb;
    $postID         = $post->ID;
    $sql            = "SELECT ID, post_title FROM $wpdb->posts WHERE (post_type = 'area' OR post_type = 'npc' OR post_type = 'item') AND post_status = 'publish' AND ID != $postID";
    $objects        = $wpdb->get_results($sql);
    $objects        = array_combine(array_column($objects, 'ID'), array_column($objects, 'post_title'));
    $visibleObjects = get_post_meta($postID, 'visible_objects', true);
    $visibleObjects = !is_array($visibleObjects) ? [] : $visibleObjects;
    ?>
    <label for="visible_objects">Visible Objects</label>
    <select data-placeholder="Visible Objects..." id="visible_objects" class="chosen-select" multiple style="width: 100%" name="visible_objects[]">
        <option value=""></option>
        <?php foreach ($objects as $id => $title): ?>
            <option value="<?= $id ?>" <?= in_array($id, $visibleObjects) ? 'selected' : '' ?>><?= $title ?> [<?= $id ?>]</option>
        <?php endforeach; ?>
    </select>
    <br/>
    A visible object is an object you display on the map of that area. For example:<br/>
    <ul style="padding-left: 20px; list-style: disc">
        <li>If the area is a city, the buildings in the city would be visible objects but the rooms in the buildings aren't.</li>
        <li>If the area is a building, the rooms and possibly the residents/workers could be objects to note here.</li>
        <li>if the area is a room, the visible objects could be items (such as a magic sword) and NPC's (workers/residents or just visitors/guests).</li>
        <li>If the area is a bandit camp, where you have buildings (tents), NPC's (bandits sitting by a campfire) and items (loot) all as visible objects.</li>
    </ul>
    <?php
}

function mp_dd_area_map()
{
    global $post;
    $image_id          = get_post_meta($post->ID, 'map_image_id', true);
    $image_src         = wp_get_attachment_url($image_id);
    $visibleObjects    = get_post_meta($post->ID, 'visible_objects', true);
    $visibleObjects    = is_array($visibleObjects) ? $visibleObjects : [];
    $labelTranslations = get_post_meta($post->ID, 'label_translations', true);
    $labelTranslations = is_array($labelTranslations) ? $labelTranslations : [];

    ?>
    <div style="overflow-x: auto; overflow-y: hidden;">
        <div id="map" style="margin: auto; position: relative">
            <img id="map_image" src="<?= $image_src ?>"/>
            <?php $number = 1; ?>
            <?php foreach ($visibleObjects as $visibleObject): ?>
                <?php list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
                <aside draggable="true" class="mp-draggable merchants-label" style="left: <?= $left ?>px; top: <?= $top ?>px">
                    <?= $number ?>
                    <input type="hidden" name="label_translations[<?= $visibleObject ?>]" value="translate(0px, 0px)">
                </aside>
                <?php ++$number; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <input type="hidden" name="map_image_id" id="upload_image_id" value="<?php echo $image_id; ?>"/>
    <p>
        <a title="<?php esc_attr_e('Set book image') ?>" href="#"
           id="set-map-image"><?php _e('Set book image') ?></a>
        <a title="<?php esc_attr_e('Remove book image') ?>" href="#" id="remove-map-image"
           style="<?php echo(!$image_id ? 'display:none;' : ''); ?>"><?php _e('Remove book image') ?></a>
    </p>
    <?php
}

#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_dd_area_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $labelTranslations = get_post_meta($post_id, 'label_translations', true);
        $labelTranslations = is_array($labelTranslations) ? $labelTranslations : [];
        foreach ($_POST['label_translations'] as $key => $value) {
            preg_match("/\((.*?)p?x?, (.*?)p?x?\)/", $value, $matches);
            list($original, $left, $top) = $matches;
            if (isset($labelTranslations[$key])) {
                list($leftOld, $topOld) = $labelTranslations[$key];
                $left += $leftOld;
                $top  += $topOld;
            }
            $labelTranslations[$key] = [$left, $top];
        }
        update_post_meta($post_id, 'label_translations', $labelTranslations);
        foreach (['visible_objects', 'map_image_id'] as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $_POST[$key]);
            } else {
                update_post_meta($post_id, $key, []);
            }
        }
    }
    return $post_id;
}

add_action('save_post_area', 'mp_dd_area_save_meta');
#endregion
