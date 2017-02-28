<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

function mp_dd_register_items_post_type()
{

    $labels = array(
        'name'                  => 'General Items',
        'singular_name'         => 'General Item',
        'add_new'               => 'Add General',
        'add_new_item'          => 'Add General Item',
        'edit_item'             => 'Edit Item',
        'new_item'              => 'New General Item',
        'view_item'             => 'View Item',
        'search_items'          => 'Search General Items',
        'not_found'             => 'No General Items found',
        'not_found_in_trash'    => 'No General Items found in Trash',
        'menu_name'             => 'D&D Items',
        'all_items'             => 'All General Items',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Items',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-carrot',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'dd_item',
    );

    register_post_type('item', $args);
}

add_action('init', 'mp_dd_register_items_post_type');

function mp_dd_remove_add_new_sub_menu()
{
    remove_submenu_page('edit.php?post_type=item', 'post-new.php?post_type=item');
}

add_action('admin_menu', 'mp_dd_remove_add_new_sub_menu');
