<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a object is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_objects_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['objects'] = array(
        0  => '',
        1  => 'Object updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View Object</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'Object updated.',
        5  => isset($_GET['revision']) ? 'Object restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'Object published. <a href="' . esc_url(get_permalink($post_ID)) . '">View object</a>',
        7  => 'Object saved.',
        8  => 'Object submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview object</a>',
        9  => 'Object scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview object</a>',
        10 => 'Object draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview object</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_objects_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Objects
 */
function mp_dd_objects_post()
{

    $labels = array(
        'name'               => 'Objects',
        'singular_name'      => 'Object',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Object',
        'edit_item'          => 'Edit Object',
        'new_item'           => 'New Object',
        'view_item'          => 'View Object',
        'search_items'       => 'Search Objects',
        'not_found'          => 'No Objects found',
        'not_found_in_trash' => 'No Objects found in Trash',
        'parent_item_colon'  => 'Parent Object:',
        'menu_name'          => 'Objects',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Objects filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array('object_type'),
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

    register_post_type('object', $args);
}

add_action('init', 'mp_dd_objects_post');
#endregion

#region Category Taxonomy
/**
 * This function registers a taxonomy for the categories.
 */
function mp_dd_object_type_taxonomy()
{
    register_taxonomy(
        'object_type',
        'object',
        array(
            'hierarchical' => true,
            'label'        => 'Object Types',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'object_type',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_object_type_taxonomy');
#endregion
