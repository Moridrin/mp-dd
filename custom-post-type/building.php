<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a building is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_buildings_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['buildings'] = array(
        0  => '',
        1  => 'Building updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View Building</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'Building updated.',
        5  => isset($_GET['revision']) ? 'Building restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'Building published. <a href="' . esc_url(get_permalink($post_ID)) . '">View building</a>',
        7  => 'Building saved.',
        8  => 'Building submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview building</a>',
        9  => 'Building scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview building</a>',
        10 => 'Building draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview building</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_buildings_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Buildings
 */
function mp_dd_buildings_post()
{

    $labels = array(
        'name'               => 'All Buildings',
        'singular_name'      => 'Building',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Building',
        'edit_item'          => 'Edit Building',
        'new_item'           => 'New Building',
        'view_item'          => 'View Building',
        'search_items'       => 'Search Buildings',
        'not_found'          => 'No Buildings found',
        'not_found_in_trash' => 'No Buildings found in Trash',
        'parent_item_colon'  => 'Parent Building:',
        'menu_name'          => 'Buildings',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Buildings filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array('building_category'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-admin-home',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('building', $args);
}

add_action('init', 'mp_dd_buildings_post');
#endregion

#region Category Taxonomy
/**
 * This function registers a taxonomy for the categories.
 */
function mp_dd_building_category_taxonomy()
{
    register_taxonomy(
        'building_category',
        'building',
        array(
            'hierarchical' => true,
            'label'        => 'Building Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'building_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_building_category_taxonomy');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_building_meta_boxes()
{
    add_meta_box('mp_dd_building_include_tag', 'Tags', 'mp_dd_building_include_tag', 'building', 'after_title', 'high');
    add_meta_box('mp_dd_building_info', 'Info', 'mp_dd_building_info', 'building', 'after_title', 'high');
}

add_action('add_meta_boxes', 'mp_dd_building_meta_boxes');

function mp_dd_building_include_tag()
{
    global $post;
    ?>
    <p>You can insert one of these tags in a post to include the building.</p>
    <p><code>[building-url-<?= $post->ID ?>]</code> place this in the href="" part of a link to open the building as popup.</p>
    <p><code>[building-link-<?= $post->ID ?>]</code> place this to show a link (with the building title) to open the building as a popup.</p>
    <p><code>[building-title-<?= $post->ID ?>]</code> place this somewhere you want to display the building title.</p>
    <p><code>[building-content-<?= $post->ID ?>]</code> place this somewhere you want to display the building content.</p>
    <?php
}

function mp_dd_building_info()
{
    global $post;
    global $wpdb;
    $npcs   = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'npc' AND post_status = 'publish'");
    $npcs   = array_combine(array_column($npcs, 'ID'), array_column($npcs, 'post_title'));
    $cities = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'city' AND post_status = 'publish'");
    $cities = array_combine(array_column($cities, 'ID'), array_column($cities, 'post_title'));
    $owner  = get_post_meta($post->ID, 'owner', true);
    $info   = get_post_meta($post->ID, 'info', true);
    $inCity = get_post_meta($post->ID, 'city', true);
    ?>
    <table>
        <tr>
            <td><label for="city">City</label></td>
            <td>
                <select id="city" name="city">
                    <?php foreach ($cities as $id => $city): ?>
                        <option value="<?= $id ?>" <?= $id == $inCity ? 'selected' : '' ?>><?= $city ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="info">Info</label></td>
            <td><input type="text" name="info" id="info" value="<?= $info ?>"></td>
        </tr>
        <tr>
            <td><label for="owner">Owner</label></td>
            <td>
                <select id="owner" name="owner">
                    <?php foreach ($npcs as $id => $title): ?>
                        <option value="<?= $id ?>" <?= $id == $owner ? 'selected' : '' ?>><?= $title ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

#endregion

#region Post Content
function mp_dd_filter_building_content($content)
{
    global $post;
    $ownerID = get_post_meta($post->ID, 'owner', true);
    if ($ownerID) {
        $content         = preg_replace('/\[npc(-li)?-owner\]/', "[npc$1-$ownerID]", $content);
        $ownerWithFamily = "[npc$1-$ownerID]";
        $spouse          = get_post_meta($ownerID, 'spouse', true);
        if ($spouse) {
            $ownerWithFamily .= "[npc$1-$spouse]";
        }
        $children = get_post_meta($ownerID, 'children', true);
        foreach ($children as $child) {
            $ownerWithFamily .= "[npc$1-$child]";
        }
        $content = preg_replace('/\[npc(-li)?-owner-with-family\]/', $ownerWithFamily, $content);
    }

    if (preg_match_all("/\[building-url-([0-9]+)\]/", $content, $buildingURLMatches)) {
        foreach ($buildingURLMatches[1] as $buildingID) {
            $building = get_post($buildingID);
            $content  = str_replace("[building-url-$buildingID]", "#modal_$buildingID", $content);
            if (strpos($content, "id=\"modal_$buildingID\"") === false) {
                $content .= "<div id=\"modal_$buildingID\" class=\"modal modal-fixed-footer\">";
                $content .= "<div class=\"modal-content\">";
                $content .= '<h2>' . $building->post_title . '</h2>';
//                $content .= preg_replace('/\[npc-([0-9]+)\]/', "[npc-li-$1]", $building->post_content);
                $content .= $building->post_content;
                $content .= '</div></div>';
            }
            $ownerID = get_post_meta($building->ID, 'owner', true);
            if ($ownerID) {
                $content         = preg_replace('/\[npc(-li)?-owner\]/', "[npc$1-$ownerID]", $content);
                $ownerWithFamily = "[npc$1-$ownerID]";
                $spouse          = get_post_meta($ownerID, 'spouse', true);
                if ($spouse) {
                    $ownerWithFamily .= "[npc$1-$spouse-spouse]";
                }
                $children = get_post_meta($ownerID, 'children', true);
                foreach ($children as $child) {
                    $ownerWithFamily .= "[npc$1-$child-child]";
                }
                $content = preg_replace('/\[npc(-li)?-owner-with-family\]/', $ownerWithFamily, $content);
            }
        }
    }
    if (preg_match_all("/\[building-link-([0-9]+)\]/", $content, $buildingLinkMatches)) {
        foreach ($buildingLinkMatches[1] as $buildingID) {
            $building      = get_post($buildingID);
            $buildingTitle = $building->post_title;
            $content       = str_replace("[building-link-$buildingID]", "<a href=\"#modal_$buildingID\">$buildingTitle</a>", $content);
            if (strpos($content, "id=\"modal_$buildingID\"") === false) {
                $content .= "<div id=\"modal_$buildingID\" class=\"modal modal-fixed-footer\">";
                $content .= "<div class=\"modal-content\">";
                $content .= "<h2>$buildingTitle</h2>";
                $content .= $building->post_content;
                $content .= "</div></div>";
            }
            $ownerID = get_post_meta($building->ID, 'owner', true);
            if ($ownerID) {
                $content         = preg_replace('/\[npc(-li)?-owner\]/', "[npc$1-$ownerID]", $content);
                $ownerWithFamily = "[npc$1-$ownerID]";
                $spouse          = get_post_meta($ownerID, 'spouse', true);
                if ($spouse) {
                    $ownerWithFamily .= "[npc$1-$spouse]";
                }
                $children = get_post_meta($ownerID, 'children', true);
                foreach ($children as $child) {
                    $ownerWithFamily .= "[npc$1-$child]";
                }
                $content = preg_replace('/\[npc(-li)?-owner-with-family\]/', $ownerWithFamily, $content);
            }
        }
    }
    if (preg_match_all("/\[building-title-([0-9]+)\]/", $content, $buildingTitleMatches)) {
        foreach ($buildingTitleMatches[1] as $buildingID) {
            $building = get_post($buildingID);
            $content  = str_replace("[building-title-$buildingID]", $building->post_title, $content);
        }
    }
    if (preg_match_all("/\[building-content-([0-9]+)\]/", $content, $buildingContentMatches)) {
        foreach ($buildingContentMatches[1] as $buildingID) {
            $building = get_post($buildingID);
            $content  = str_replace("[building-content-$buildingID]", $building->post_content, $content);
        }
    }

    return $content;
}

add_filter('the_content', 'mp_dd_filter_building_content');
#endregion
