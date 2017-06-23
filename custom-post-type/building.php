<?php

use mp_dd\MP_DD;

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
        'name'               => 'Buildings',
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
        'taxonomies'          => array(),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => 'edit.php?post_type=cities',
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

    register_post_type('buildings', $args);
}

add_action('init', 'mp_dd_buildings_post');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_building_meta_boxes()
{
    add_meta_box('mp_dd_include_tag', 'tags', 'mp_dd_include_tag', 'buildings', 'after_title', 'high');
}

add_action('add_meta_boxes', 'mp_dd_building_meta_boxes');

function mp_dd_include_tag()
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

#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_dd_save_building_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    $id = 1;
    if (!get_post_meta($post_id, 'set_from_content', true)) {
        $buildings = array();
        while (isset($_POST['building_' . $id . '_html'])) {
            $buildingHTML = $_POST['building_' . $id . '_html'];
            if (!empty($buildingHTML)) {
                $buildings[] = $buildingHTML;
            }
            $id++;
        }
        update_post_meta($post_id, 'buildings', $buildings);
    } else {
        update_post_meta($post_id, 'set_from_content', false);
    }

    return $post_id;
}

add_action('save_post_buildings', 'mp_dd_save_building_meta');

function mp_dd_filter_building_data($data)
{
    global $post;
    $file = new DOMDocument();
    libxml_use_internal_errors(true);
    $file->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
    $file->loadHTML(MP_DD::HTML_HEAD . stripslashes($data['post_content']));

    $buildings          = array();
    $buildingsContainer = $file->getElementById('buildings');
    if ($buildingsContainer != null) {
        for ($i = 0; $i < $buildingsContainer->childNodes->length; $i++) {
            $child = $buildingsContainer->childNodes->item($i);
            if ($child instanceof DOMElement) {
                /** @noinspection PhpUndefinedFieldInspection */
                $buildings[] = $file->saveHTML($child);
            }
        }
        $buildingsContainer->innerHTML = '';
        update_post_meta($post->ID, 'buildings', $buildings);
        update_post_meta($post->ID, 'set_from_content', true);
        $html                 = str_replace($file->saveHTML($buildingsContainer), MP_DD::TAG_BUILDINGS, $file->saveHTML());
        $remove               = array(
            MP_DD::HTML_HEAD,
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
            '<html>',
            '</html>',
            '<body>',
            '</body>',
        );
        $data['post_content'] = addslashes(str_replace($remove, '', $html));
    }
    return $data;
}

add_filter('wp_insert_post_data', 'mp_dd_filter_building_data');

#endregion

#region Post Content
function mp_dd_filter_building_content($content)
{
    if (preg_match_all("/\[building-url-([0-9]+)\]/", $content, $buildingURLMatches)) {
        foreach ($buildingURLMatches[1] as $buildingID) {
            $building = get_post($buildingID);
            $content  = str_replace("[building-url-$buildingID]", "#modal_$buildingID", $content);
            if (strpos($content, "id=\"modal_$buildingID\"") === false) {
                $content .= "<div id=\"modal_$buildingID\" class=\"modal modal-fixed-footer\">";
                $content .= "<div class=\"modal-content\">";
                $content .= '<h2>' . $building->post_title . '</h2>';
                $content .= $building->post_content;
                $content .= "</div></div>";
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
