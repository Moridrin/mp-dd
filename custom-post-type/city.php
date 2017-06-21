<?php

use mp_dd\models\City;
use mp_dd\MP_DD;

if (!defined('ABSPATH')) {
    exit;
}

#region Save City
/**
 * @param $post_ID
 * @param $post_after
 *
 * @return mixed
 */
function mp_dd_save($post_ID, $post_after)
{
    if (get_post_type() != 'cities') {
        return $post_ID;
    }
    $city = new City($post_after);
    if ($city->isPublished() && !$city->isValid()) {
        wp_update_post(
            array(
                'ID'          => $post_ID,
                'post_status' => 'draft',
            )
        );
        update_option(MP_DD::OPTION_PUBLISH_ERROR, true);
    }
    return $post_ID;
}

add_action('save_post', 'mp_dd_save', 10, 2);
#endregion

#region Admin Notice
/**
 * This function displays the error message thrown by the Save or Update actions of an City.
 */
function mp_dd_admin_notice()
{
    $screen = get_current_screen();
    if ('cities' != $screen->post_type || 'post' != $screen->base) {
        return;
    }
    if (get_option(MP_DD::OPTION_PUBLISH_ERROR, false)) {
        ?>
        <div class="notice notice-error">
            <p>You cannot publish an city without a start date and time!</p>
        </div>
        <?php
    }
    update_option(MP_DD::OPTION_PUBLISH_ERROR, false);
}

add_action('admin_notices', 'mp_dd_admin_notice');
#endregion

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a city is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['cities'] = array(
        0  => '',
        1  => 'City updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View \mp_dd\models\City</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'City updated.',
        5  => isset($_GET['revision']) ? 'City restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'City published. <a href="' . esc_url(get_permalink($post_ID)) . '">View city</a>',
        7  => 'City saved.',
        8  => 'City submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview city</a>',
        9  => '\mp_dd\models\City scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview city</a>',
        10 => 'City draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview city</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Cities
 */
function mp_dd_post_category()
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
        'taxonomies'          => array('city_category'),
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

    register_post_type('cities', $args);
}

add_action('init', 'mp_dd_post_category');
#endregion

#region Category Taxonomy
/**
 * This function registers a taxonomy for the categories.
 */
function mp_dd_category_taxonomy()
{
    register_taxonomy(
        'city_category',
        'cities',
        array(
            'hierarchical' => true,
            'label'        => 'City Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'city_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_category_taxonomy');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_meta_boxes()
{
    add_meta_box('mp_dd_houses', 'Houses', 'mp_dd_houses', 'cities', 'normal', 'high');
}

add_action('add_meta_boxes', 'mp_dd_meta_boxes');

function mp_dd_houses()
{
    ?>
    <textarea title="test"></textarea>
    <?php
}
#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_dd_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    //TODO Save
    return $post_id;
}

add_action('save_post_cities', 'mp_dd_save_meta');
#endregion
