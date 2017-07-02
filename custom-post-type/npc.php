<?php

use mp_dd\MP_DD;

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a npc is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_npcs_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['npcs'] = array(
        0  => '',
        1  => 'NPC updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View NPC</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'NPC updated.',
        5  => isset($_GET['revision']) ? 'NPC restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'NPC published. <a href="' . esc_url(get_permalink($post_ID)) . '">View NPC</a>',
        7  => 'NPC saved.',
        8  => 'NPC submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview NPC</a>',
        9  => 'NPC scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview NPC</a>',
        10 => 'NPC draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview NPC</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_npcs_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for NPCs
 */
function mp_dd_npcs_post()
{

    $labels = array(
        'name'               => 'All NPCs',
        'singular_name'      => 'NPC',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New NPC',
        'edit_item'          => 'Edit NPC',
        'new_item'           => 'New NPC',
        'view_item'          => 'View NPC',
        'search_items'       => 'Search NPCs',
        'not_found'          => 'No NPCs found',
        'not_found_in_trash' => 'No NPCs found in Trash',
        'parent_item_colon'  => 'Parent NPC:',
        'menu_name'          => 'NPCs',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'NPCs filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array(),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-groups',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('npc', $args);
}

add_action('init', 'mp_dd_npcs_post');
#endregion

#region Category Taxonomy
/**
 * This function registers a taxonomy for the categories.
 */
function mp_dd_npc_category_taxonomy()
{
    register_taxonomy(
        'npc_type',
        'npc',
        array(
            'hierarchical' => true,
            'label'        => 'NPC Type',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'npc_type',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_npc_category_taxonomy');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_npc_meta_boxes()
{
    add_meta_box('mp_dd_npc_info', 'Info', 'mp_dd_npc_info', 'npc', 'after_title', 'high');
    add_meta_box('mp_dd_npc_include_tag', 'Tags', 'mp_dd_npc_include_tag', 'npc', 'after_title', 'normal');
}

add_action('add_meta_boxes', 'mp_dd_npc_meta_boxes');

function mp_dd_npc_include_tag()
{
    global $post;
    ?>
    <p>You can insert one of these tags in a post to include the npc.</p>
    <p><code>[npc-<?= $post->ID ?>]</code> place this somewhere you want to display the NPC.</p>
    <p><code>[npc-<?= $post->ID ?>-li]</code> place this inside a &lt;ul&gt; block where you want to display the NPC (as collection item).</p>
    <?php
}

function mp_dd_npc_info()
{
    global $post;
    global $wpdb;
    $height       = get_post_meta($post->ID, 'height', true);
    $weight       = get_post_meta($post->ID, 'weight', true);
    $npcs         = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'npc' AND post_status = 'publish'");
    $npcs         = array_combine(array_column($npcs, 'ID'), array_column($npcs, 'post_title'));
    $clothing     = get_post_meta($post->ID, 'clothing', true);
    $possessions  = get_post_meta($post->ID, 'possessions', true);
    $armsAndArmor = get_post_meta($post->ID, 'arms_armor', true);
    $class        = get_post_meta($post->ID, 'class', true);
    $level        = get_post_meta($post->ID, 'level', true);
    $profession   = get_post_meta($post->ID, 'profession', true);
    $spouse       = get_post_meta($post->ID, 'spouse', true);
    $children     = get_post_meta($post->ID, 'children', true);
    ?>
    <datalist id="npcs">
        <?php foreach ($npcs as $id => $title): ?>
            <?php if ($id != $post->ID): ?>
                <option value="<?= $id ?>"><?= $title ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </datalist>
    <table>
        <tr>
            <td><label for="spouse">Spouse</label></td>
            <td>
                <select id="spouse" name="spouse">
                    <?php foreach ($npcs as $id => $title): ?>
                        <?php if ($id != $post->ID): ?>
                            <option value="<?= $id ?>" <?= $id == $spouse ? 'selected' : '' ?>><?= $title ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="children">Children</label></td>
            <td>
                <select id="children" name="children[]" multiple>
                    <?php foreach ($npcs as $id => $title): ?>
                        <?php if ($id != $post->ID): ?>
                            <option value="<?= $id ?>" <?= in_array($id, $children) ? 'selected' : '' ?>><?= $title ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="profession">Profession</label></td>
            <td><input type="text" name="profession" id="profession" value="<?= $profession ?>"></td>
        </tr>
        <?php if ($class && $level): ?>
            <tr>
                <td><label for="class">Class</label></td>
                <td><input type="text" name="class" id="class" value="<?= $class ?>"></td>
                <td><label for="level">Level</label></td>
                <td><input type="text" name="level" id="level" value="<?= $level ?>"></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><label for="height">Height</label></td>
            <td><input type="text" name="height" id="height" value="<?= $height ?>"></td>
            <td><label for="weight">Weight</label></td>
            <td><input type="text" name="weight" id="weight" value="<?= $weight ?>"></td>
        </tr>
        <tr>
            <td><label for="clothing">Clothing</label></td>
            <td colspan="4"><input type="text" name="clothing" id="clothing" value="<?= $clothing ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <td><label for="possessions">Possessions</label></td>
            <td colspan="4"><textarea name="possessions" id="possessions" style="width: 100%;"><?= $possessions ?></textarea></td>
        </tr>
        <tr>
            <td><label for="arms_armor">Arms and Armor</label></td>
            <td colspan="4"><textarea name="arms_armor" id="arms_armor" style="width: 100%;"><?= $armsAndArmor ?></textarea></td>
        </tr>
    </table>
    <?php
}

#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_dd_npc_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach (array('spouse', 'children', 'profession', 'class', 'level', 'height', 'weight', 'clothing', 'possessions', 'arms_armor') as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $_POST[$key]);
            }
        }
    }
    return $post_id;
}

add_action('save_post_npc', 'mp_dd_npc_save_meta');
#endregion

