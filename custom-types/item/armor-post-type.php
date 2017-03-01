<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

function mp_dd_register_armor_post_type()
{

    $labels = array(
        'name'                  => 'Armor',
        'singular_name'         => 'Armor',
        'add_new'               => 'Add new',
        'add_new_item'          => 'Add New Armor',
        'edit_item'             => 'Edit Armor',
        'new_item'              => 'New Armor',
        'view_item'             => 'View Armor',
        'search_items'          => 'Search Armor',
        'not_found'             => 'No Armor found',
        'not_found_in_trash'    => 'No Armor found in Trash',
        'menu_name'             => 'D&D Armor',
        'all_items'             => 'All Armor',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Armor',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        "show_in_menu"    => 'edit.php?post_type=item',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'dd_armor',
    );

    register_post_type('armor', $args);
}

add_action('init', 'mp_dd_register_armor_post_type');

function mp_dd_add_armor_meta_boxes()
{
    add_meta_box('mp_dd_armor_properties', 'Properties', 'mp_dd_armor_properties', 'armor', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_armor_meta_boxes');

function mp_dd_armor_properties()
{
    global $post;
    /** @var Armor $armor */
    $armor = Armor::load($post->ID);
    echo $armor->getEditor();
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_armor_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    $armor = Armor::fromPOST($post_id);
    $armor->save();
    return $post_id;
}

add_action('save_post_armor', 'mp_dd_save_armor_meta', 1, 2);
