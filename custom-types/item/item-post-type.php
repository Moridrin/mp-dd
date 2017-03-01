<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

require_once 'item-content.php';

function mp_dd_register_items_post_type()
{

    $labels = array(
        'name'               => 'General Items',
        'singular_name'      => 'General Item',
        'add_new'            => 'Add General',
        'add_new_item'       => 'Add General Item',
        'edit_item'          => 'Edit Item',
        'new_item'           => 'New General Item',
        'view_item'          => 'View Item',
        'search_items'       => 'Search General Items',
        'not_found'          => 'No General Items found',
        'not_found_in_trash' => 'No General Items found in Trash',
        'menu_name'          => 'D&D Items',
        'all_items'          => 'All General Items',
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

function mp_dd_add_item_meta_boxes()
{
    add_meta_box('mp_dd_item_properties', 'Properties', 'mp_dd_item_properties', 'item', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_item_meta_boxes');

function mp_dd_item_properties()
{
    global $post;
    /** @var Item $item */
    $item = Item::load($post->ID);
    echo $item->getEditor();
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_item_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $post_id;
    }
    $item = Item::fromPOST($post_id);
    $item->save();
    return $post_id;
}

add_action('save_post_item', 'mp_dd_save_item_meta', 1, 2);
