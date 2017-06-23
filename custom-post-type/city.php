<?php

use mp_dd\MP_DD;

if (!defined('ABSPATH')) {
    exit;
}

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
        1  => 'City updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View City</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'City updated.',
        5  => isset($_GET['revision']) ? 'City restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'City published. <a href="' . esc_url(get_permalink($post_ID)) . '">View city</a>',
        7  => 'City saved.',
        8  => 'City submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview city</a>',
        9  => 'City scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview city</a>',
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
        'taxonomies'          => array(),
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

#region Meta Boxes
function mp_dd_edit_form_after_title()
{
    global $post;
    do_meta_boxes(get_current_screen(), 'after_title', $post);
}

add_action('edit_form_after_title', 'mp_dd_edit_form_after_title');

/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_meta_boxes()
{
    add_meta_box('mp_dd_buildings', 'Buildings', 'mp_dd_buildings', 'cities', 'after_title', 'high');
}

add_action('add_meta_boxes', 'mp_dd_meta_boxes');

function mp_dd_buildings()
{
    global $post;
    $buildings = get_post_meta($post->ID, 'buildings', true);
    ?>
    <!--suppress CssUnusedSymbol -->
    <style>
        button.accordion {
            background-color: #eee;
            color: #444;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            transition: 0.4s;
        }

        button.accordion.active, button.accordion:hover {
            background-color: #ddd;
        }

        div.panel {
            padding: 0 18px;
            background-color: white;
            display: none;
        }
    </style>
    <div id="test" style="margin: 10px 0;">
        <div id="buildings-placeholder" class="sortable"></div>
    </div>
    <button type="button" onclick="mp_dd_add_new_building()">Add building</button>
    <!--suppress JSUnusedLocalSymbols -->
    <script>
        <?php
        $buildingID = 1;
        foreach ($buildings as $building) {
            $building = json_encode($building);
            echo "mp_dd_add_building($buildingID, $building, false);";
            $buildingID++;
        }
        ?>
        var fieldID = <?= $buildingID ?>;
        function mp_dd_add_new_building() {
            mp_dd_add_building(fieldID, "", true);
            fieldID++;
        }
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
function mp_dd_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    //TODO Save
    return $post_id;
}

add_action('save_post_cities', 'mp_dd_save_meta');

function filter_post_data($data)
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
                $buildings[] = $child->innerHTML;
            }
        }
        $buildingsContainer->innerHTML = '';
        update_post_meta($post->ID, 'buildings', $buildings);
        $html                 = str_replace($file->saveHTML($buildingsContainer), MP_DD::HTML_BUILDINGS_PLACEHOLDER, $file->saveHTML());
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

add_filter('wp_insert_post_data', 'filter_post_data');

#endregion
