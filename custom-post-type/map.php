<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a map is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_maps_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['maps'] = array(
        0  => '',
        1  => 'Map updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View Map</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'Map updated.',
        5  => isset($_GET['revision']) ? 'Map restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'Map published. <a href="' . esc_url(get_permalink($post_ID)) . '">View map</a>',
        7  => 'Map saved.',
        8  => 'Map submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview map</a>',
        9  => 'Map scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview map</a>',
        10 => 'Map draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview map</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_maps_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Maps
 */
function mp_dd_maps_post()
{

    $labels = array(
        'name'               => 'All Maps',
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
        'menu_name'          => 'Maps',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Maps filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array(),
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

    register_post_type('maps', $args);
}

add_action('init', 'mp_dd_maps_post');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_map_meta_boxes()
{
    add_meta_box('mp_dd_map_include_tag', 'Tags', 'mp_dd_map_include_tag', 'maps', 'after_title', 'high');
}

add_action('add_meta_boxes', 'mp_dd_map_meta_boxes');

function mp_dd_map_include_tag()
{
    global $post;
    ?>
    <p>You can insert one of these tags in a post to include the map.</p>
    <p><code>[map-url-<?= $post->ID ?>]</code> place this in the href="" part of a link to open the map as popup.</p>
    <p><code>[map-link-<?= $post->ID ?>]</code> place this to show a link (with the map title) to open the map as a popup.</p>
    <p><code>[map-title-<?= $post->ID ?>]</code> place this somewhere you want to display the map title.</p>
    <p><code>[map-content-<?= $post->ID ?>]</code> place this somewhere you want to display the map content.</p>
    <?php
}
#endregion

#region Post Content
function mp_dd_filter_map_content($content)
{
    if (preg_match_all("/\[map-([0-9]+)\]/", $content, $mapMatches)) {
        foreach ($mapMatches[1] as $mapID) {
            $map     = get_post($mapID);
            $content = str_replace("[map-$mapID]", $map->post_content, $content);
        }
    }

    return $content;
}

add_filter('the_content', 'mp_dd_filter_map_content');
#endregion
