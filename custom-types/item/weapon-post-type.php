<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

function mp_dd_register_weapons_post_type()
{

    $labels = array(
        'name'                  => 'Weapons',
        'singular_name'         => 'Weapon',
        'add_new'               => 'Add new',
        'add_new_item'          => 'Add New Weapon',
        'edit_item'             => 'Edit Weapon',
        'new_item'              => 'New Weapon',
        'view_item'             => 'View Weapon',
        'search_items'          => 'Search Weapons',
        'not_found'             => 'No Weapons found',
        'not_found_in_trash'    => 'No Weapons found in Trash',
        'menu_name'             => 'D&D Weapons',
        'all_items'             => 'All Weapons',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Weapons',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        "show_in_menu"    => 'edit.php?post_type=item',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'dd_weapon',
    );

    register_post_type('weapon', $args);
}

add_action('init', 'mp_dd_register_weapons_post_type');

function mp_dd_add_weapon_meta_boxes()
{
    add_meta_box('mp_dd_weapon_properties', 'Properties', 'mp_dd_weapon_properties', 'weapon', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_weapon_meta_boxes');

function mp_dd_weapon_properties()
{
    global $post;
    /** @var Weapon $weapon */
    $weapon = Weapon::fromJSON(get_post_meta($post->ID, 'weapon', true));
    echo $weapon->getEditor();
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_weapon_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    $weapon = Weapon::fromPOST();
    update_post_meta($post->ID, 'weapon', $weapon->getJSON());
    return $post_id;
}

add_action('save_post_weapon', 'mp_dd_save_weapon_meta', 1, 2);
