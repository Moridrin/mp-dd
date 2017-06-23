<?php

use mp_dd\MP_DD;

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a city is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_cities_messages($messages)
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

add_filter('post_updated_messages', 'mp_dd_updated_cities_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Cities
 */
function mp_dd_cities_post()
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

add_action('init', 'mp_dd_cities_post');
#endregion

#region Save City
function mp_dd_filter_city_data($data)
{
    global $wpdb;
    $file = new DOMDocument();
    libxml_use_internal_errors(true);
    $file->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
    $file->loadHTML(MP_DD::HTML_HEAD . stripslashes($data['post_content']));

    $buildingsContainer = $file->getElementById('buildings');
    if ($buildingsContainer != null) {
        $buildings = array();
        for ($i = 0; $i < $buildingsContainer->childNodes->length; $i++) {
            $child = $buildingsContainer->childNodes->item($i);
            if ($child instanceof DOMElement) {
                $modalID      = $child->getAttribute('id');
                $titleElement = $child->childNodes->item(0)->childNodes->item(0);
                /** @noinspection PhpUndefinedFieldInspection */
                $title = $titleElement->innerHTML;
                $child->childNodes->item(0)->removeChild($titleElement);
                /** @noinspection PhpUndefinedFieldInspection */
                $buildingContent = $child->childNodes->item(0)->innerHTML;
                $found           = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE post_content = '$buildingContent'");
                if (isset($found->ID)) {
                    $postID = $found->ID;
                } else {
                    $postID = wp_insert_post(
                        array(
                            'post_title'   => $title,
                            'post_content' => $buildingContent,
                            'post_type'    => 'buildings',
                        )
                    );
                }
                $buildings[] = array(
                    'modal_id'     => $modalID,
                    'post_id'      => $postID,
                    'post_title'   => $title,
                    'post_content' => $buildingContent,
                );
            }
        }
        $file->loadHTML(MP_DD::HTML_HEAD . utf8_encode(stripslashes($data['post_content'])));
        $buildingsContainer = $file->getElementById('buildings');
        $buildingsContainer->parentNode->removeChild($buildingsContainer);
        $html = $file->saveHTML();

        $remove               = array(
            MP_DD::HTML_HEAD,
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
            '<html>',
            '</html>',
            '<body>',
            '</body>',
        );
        $data['post_content'] = addslashes(str_replace($remove, '', $html));
        foreach ($buildings as $building) {
            $modalID = $building['modal_id'];
            $postID = $building['post_id'];
            $data['post_content'] = str_replace("href=\"#$modalID\"", utf8_encode("href=\"[building-url-$postID]\""), $data['post_content']);
        }
        $data['post_content'] = utf8_decode($data['post_content']);
    }
    return $data;
}

add_filter('wp_insert_post_data', 'mp_dd_filter_city_data');

#endregion

#region Post Content
function mp_dd_filter_city_content($content)
{
    if (strpos($content, MP_DD::TAG_BUILDINGS) !== false) {
        global $post;
        $buildings = get_post_meta($post->ID, 'buildings', true);
        $content   = str_replace(MP_DD::TAG_BUILDINGS, implode('', $buildings), $content);
    }

    return $content;
}

add_filter('the_content', 'mp_dd_filter_city_content');
#endregion
