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
    $buildings   = get_posts(array('post_type' => 'building'));
    $buildings   = array_combine(array_column($buildings, 'ID'), array_column($buildings, 'post_title'));
    $height      = get_post_meta($post->ID, 'height', true);
    $weight      = get_post_meta($post->ID, 'weight', true);
    $npcs        = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'npcs' AND post_status = 'publish'");
    $npcs        = array_combine(array_column($npcs, 'ID'), array_column($npcs, 'post_title'));
    $links       = get_post_meta($post->ID, 'family_links', true);
    $clothing    = get_post_meta($post->ID, 'clothing', true);
    $possessions = get_post_meta($post->ID, 'possessions', true);
    $profession  = get_post_meta($post->ID, 'profession', true);
    $buildingID  = get_post_meta($post->ID, 'wp_building_id', true);
    $type        = get_post_meta($post->ID, 'type', true);
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
            <td><label for="building_id">Building</label></td>
            <td colspan="4">
                <select name="building_id" id="building_id">
                    <?php foreach ($buildings as $id => $building): ?>
                        <option value="<?= $id ?>" <?= $id == $buildingID ? 'selected' : '' ?>><?= $building ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="type">Type</label></td>
            <td><input type="text" name="type" id="type" value="<?= $type ?>"></td>
        </tr>
        <tr>
            <td><label for="profession">Profession</label></td>
            <td><input type="text" name="profession" id="profession" value="<?= $profession ?>"></td>
        </tr>
        <tr>
            <td><label for="height">Height</label></td>
            <td><input type="text" name="height" id="height" value="<?= $height ?>"></td>
        </tr>
        <tr>
            <td><label for="weight">Weight</label></td>
            <td><input type="text" name="weight" id="weight" value="<?= $weight ?>"></td>
        </tr>
        <tr id="add_link_tr">
            <td><label for="npc_link_id">Name</label></td>
            <td><input type="text" name="npc_link_id" id="npc_link_id" list="npcs"></td>
            <td><label for="npc_link_type">Type</label></td>
            <td>
                <select id="npc_link_type" name="npc_link_type">
                    <option value="0">Spouse</option>
                    <option value="1">Child</option>
                </select>
            </td>
            <td>
                <button type="button" onclick="mp_dd_add_new_family_link()">Link Family</button>
            </td>
        </tr>
        <tr>
            <td><label for="clothing">Clothing</label></td>
            <td colspan="4"><input type="text" name="clothing" id="clothing" value="<?= $clothing ?>" style="width: 100%;"></td>
        </tr>
        <tr>
            <td><label for="possessions">Possessions</label></td>
            <td colspan="4"><textarea name="possessions" id="possessions" style="width: 100%;"><?= $possessions ?></textarea></td>
        </tr>
    </table>
    <script>
        var i = 0;
        function mp_dd_add_new_family_link() {
            var npcID = document.getElementById('npc_link_id').value;
            var npcName = document.querySelector('datalist#npcs option[value="' + npcID + '"]').text;
            var linkTypeObject = document.getElementById('npc_link_type');
            var linkType = linkTypeObject.options[linkTypeObject.selectedIndex].value;
            if (!npcID) {
                document.getElementById("npc_link_id").setAttribute("placeholder", "fill in a valid Field ID");
            } else {
                document.getElementById("npc_link_id").setAttribute("placeholder", "");
                mp_dd_add_family_link('add_link_tr', i, npcID, npcName, linkType);
                i++;
            }
            document.getElementById('npc_link_id').value = '';
        }

        <?php if (is_array($links)): ?>
        <?php foreach ($links as $link): ?>
        mp_dd_add_family_link('add_link_tr', i, <?= $link['npc_id'] ?>, "<?= $npcs[$link['npc_id']] ?>", "<?= $link['link_type'] ?>");
        i++;
        <?php endforeach; ?>
        <?php endif; ?>
    </script>
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
        if (isset($_POST['link_ids'])) {
            $links = array();
            foreach ($_POST['link_ids'] as $linkID) {
                $links[] = array(
                    'link_type' => $_POST['link_' . $linkID . '_link_type'],
                    'npc_id'    => $_POST['link_' . $linkID . '_npc_id'],
                );
            }
            update_post_meta($post_id, 'family_links', $links);
        }
        foreach (array('height', 'weight', 'clothing', 'possessions', 'profession') as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $_POST[$key]);
            }
        }
    }
    return $post_id;
}

add_action('save_post_npcs', 'mp_dd_npc_save_meta');
#endregion

#region Post Content
function mp_dd_filter_npc_content($content)
{
    if (preg_match_all("/\[npc-([0-9]+)\]/", $content, $npcURLMatches)) {
        foreach ($npcURLMatches[1] as $npcID) {
            $npc     = get_post($npcID);
            $html    = '<h3>' . $npc->post_title . '</h3>';
            $html    .= '<p>';
            $html    .= '<b>Height:</b> ' . get_post_meta($npcID, 'height', true) . ' <b>Weight:</b> ' . get_post_meta($npcID, 'weight', true) . '<br/>';
            $html    .= $npc->post_content . '<br/>';
            $html    .= '<b>Wearing:</b> ' . get_post_meta($npcID, 'clothing', true) . '<br/>';
            $html    .= '<b>Possessions:</b> ' . get_post_meta($npcID, 'possessions', true) . '<br/>';
            $html    .= '</p>';
            $content = str_replace("[npc-$npcID]", $html, $content);
        }
    }
    if (preg_match_all("/\[npc-([0-9]+)-li\]/", $content, $npcURLMatches)) {
        foreach ($npcURLMatches[1] as $npcID) {
            $npc     = get_post($npcID);
            $html    = '<li>';
            $html    .= '<div class="collapsible-header">';
            $html    .= $npc->post_title;
            $html    .= '</div>';
            $html    .= '<div class="collapsible-body">';
            $html    .= '<p>';
            $html    .= '<b>Height:</b> ' . get_post_meta($npcID, 'height', true) . ' <b>Weight:</b> ' . get_post_meta($npcID, 'weight', true) . '<br/>';
            $html    .= $npc->post_content . '<br/>';
            $html    .= '<b>Wearing:</b> ' . get_post_meta($npcID, 'clothing', true) . '<br/>';
            $html    .= '<b>Possessions:</b> ' . get_post_meta($npcID, 'possessions', true) . '<br/>';
            $html    .= '</p>';
            $html    .= '</div>';
            $html    .= '</li>';
            $content = str_replace("[npc-$npcID-li]", $html, $content);
        }
    }

    return $content;
}

add_filter('the_content', 'mp_dd_filter_npc_content');
#endregion
