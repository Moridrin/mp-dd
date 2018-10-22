<?php

namespace mp_dd_bod\PostType;

use Exception;
use mp_general\base\BaseFunctions;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Location
{
    public static function filterContent($content): string
    {
        global $post;
        if ($post->post_type !== 'location') {
            return $content;
        }
        ob_start();
        ?>
            <table class="highlight">
                <tr><th>Religion</th><td><?= BaseFunctions::escape(get_post_meta($post->ID, 'religion', true), 'html') ?></td></tr>
                <tr><th>Ruler</th><td><?= BaseFunctions::escape(get_post_meta($post->ID, 'ruler', true), 'html') ?></td></tr>
            </table>
        <?php
        return ob_get_clean() . $content;
    }

    public static function setupPostType(): void
    {
        $labels = [
            'name'               => 'Locations',
            'singular_name'      => 'Location',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Location',
            'edit_item'          => 'Edit Location',
            'new_item'           => 'New Location',
            'view_item'          => 'View Location',
            'search_items'       => 'Search Locations',
            'not_found'          => 'No Locations found',
            'not_found_in_trash' => 'No Locations found in Trash',
            'parent_item_colon'  => 'Parent Location:',
            'menu_name'          => 'Locations',
        ];

        $args = [
            'labels'              => $labels,
            'hierarchical'        => true,
            'description'         => 'Locations filterable by category',
            'supports'            => [
                'title',
                'editor',
                'author',
                'thumbnail',
                'trackbacks',
                'custom-fields',
                'comments',
                'revisions',
                'page-attributes',
            ],
            'taxonomies'          => ['location_category', 'location_monsters'],
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-calendar-alt',
            'show_in_nav_menus'   => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'has_archive'         => true,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
            'capability_type'     => 'post',
        ];

        register_post_type('location', $args);
    }

    public static function setupTaxonomy(): void
    {
        register_taxonomy(
            'location_category',
            'location',
            [
                'hierarchical' => true,
                'label'        => 'Location Categories',
                'query_var'    => true,
                'rewrite'      => [
                    'slug'       => 'location_category',
                    'with_front' => false,
                ],
            ]
        );
    }

    public static function addMetaBoxes(): void
    {
        add_meta_box('dd_location_properties', 'Properties', [Location::class, 'propertiesMetaBox'], 'location', 'advanced', 'high');
    }

    public static function propertiesMetaBox(): void
    {
        global $post;
        $religion = get_post_meta($post->ID, 'religion', true);
        $ruler = get_post_meta($post->ID, 'ruler', true);
        ?>
        <table width="100%">
            <tr>
                <th style="text-align: left;">Active</th>
                <th>Name</th>
            </tr>
            <tr>
                <th>Religion</th>
                <td><input name="religion" value="<?= $religion ?>"></td>
            </tr>
            <tr>
                <th>Ruler</th>
                <td><input name="ruler" value="<?= $ruler ?>"></td>
            </tr>
        </table>
        <?php
    }

    public static function saveMetadata($postId): int
    {
        if (!current_user_can('edit_post', $postId)) {
            return $postId;
        }
        update_post_meta($postId, 'religion', BaseFunctions::getParameter('religion', 'text'));
        update_post_meta($postId, 'ruler', BaseFunctions::getParameter('ruler', 'text'));
        return $postId;
    }
}

add_filter('the_content', [Location::class, 'filterContent'], 13);
add_action('init', [Location::class, 'setupPostType']);
add_action('init', [Location::class, 'setupTaxonomy']);
add_action('add_meta_boxes', [Location::class, 'addMetaBoxes']);
add_action('save_post_location', [Location::class, 'saveMetadata']);
