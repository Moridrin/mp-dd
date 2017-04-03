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
    add_meta_box('mp_dd_map_general', 'General', 'mp_dd_map_general', 'map', 'advanced', 'default');
    add_meta_box('mp_dd_map_map', 'Map', 'mp_dd_map_map', 'map', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_map_meta_boxes');

function mp_dd_map_general()
{
    global $post;
    $map = Map::load($post->ID);
    echo $map->getGeneralEditor();
}

function mp_dd_map_map()
{
    global $post;
    $map = Map::load($post->ID);
    echo $map->getMapEditor();
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
    $map = Map::fromPOST($post_id);
    $map->save();
    return $post_id;
}

add_action('save_post_map', 'mp_dd_save_map_meta', 1, 2);