<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a city is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_cities_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['cities'] = array(
        0  => '',
        1  => 'City updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View City</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'City updated.',
        5  => isset($_GET['revision']) ? 'City restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'City published. <a href="' . esc_url(get_permalink($post_ID)) . '">View city</a>',
        7  => 'City saved.',
        8  => 'City submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview city</a>',
        9  => 'City scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview city</a>',
        10 => 'City draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview city</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_cities_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Cities
 */
function mp_dd_cities_post()
{

    $labels = array(
        'name'               => 'Cities',
        'singular_name'      => 'City',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New City',
        'edit_item'          => 'Edit City',
        'new_item'           => 'New City',
        'view_item'          => 'View City',
        'search_items'       => 'Search Cities',
        'not_found'          => 'No Cities found',
        'not_found_in_trash' => 'No Cities found in Trash',
        'parent_item_colon'  => 'Parent City:',
        'menu_name'          => 'Cities',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Cities filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array(),
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

    register_post_type('cities', $args);
}

add_action('init', 'mp_dd_cities_post');
#endregion
