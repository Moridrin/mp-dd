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
    add_meta_box('mp_dd_area_info', 'Info', 'mp_dd_area_info', 'area', 'after_title', 'high');
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
    $postID = $post->ID;
    $sql = "SELECT ID, post_title FROM $wpdb->posts WHERE (post_type = 'area' OR post_type = 'npc' OR post_type = 'item') AND post_status = 'publish' AND ID != $postID";
    $objects   = $wpdb->get_results($sql);
    $objects   = array_combine(array_column($objects, 'ID'), array_column($objects, 'post_title'));
    $visibleObjects = get_post_meta($postID, 'visible_objects', true);
    $visibleObjects = !is_array($visibleObjects) ? [] : $visibleObjects;
    ?>
    <select data-placeholder="Visible Objects..." class="chosen-select" multiple style="width: 100%" name="visible_objects[]">
        <option value=""></option>
        <?php foreach ($objects as $id => $title): ?>
            <option value="<?= $id ?>" <?= in_array($id, $visibleObjects) ? 'selected' : '' ?>><?= $title ?> [<?= $id ?>]</option>
        <?php endforeach; ?>
    </select>
    <br/>
    A visible object is an object you usually would display on the map of that area. For example:<br/>
    <ul style="padding-left: 20px; list-style: disc">
        <li>If the area is a city, the buildings in the city would be visible objects but the rooms in the buildings aren't.</li>
        <li>If the area is a building, the rooms and possably the residents/workers could be objects to note here.</li>
        <li>if the area is a room, the visible objects could be items (such as a magic sword) and npc's (workers/residents or just visitors/guests).</li>
        <li>If the area is a bandit camp, where you have buildings (tents), npc's (bandits sitting by a campfire) and items (loot) all as visible objects.</li>
    </ul>
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
        foreach (['visible_objects'] as $key) {
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
