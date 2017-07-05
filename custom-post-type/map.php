<?php

if (!defined('ABSPATH')) {
    exit;
}

#region Updated Messages
/**
 * @param string[] $messages is an array of messages displayed after a map is updated.
 *
 * @return string[] the messages.
 */
function mp_dd_updated_maps_messages($messages)
{
    global $post, $post_ID;
    /** @noinspection HtmlUnknownTarget */
    $messages['maps'] = array(
        0  => '',
        1  => 'Map updated. <a href="' . esc_url(get_permalink($post_ID)) . '">View Map</a>',
        2  => 'Custom field updated.',
        3  => 'Custom field deleted.',
        4  => 'Map updated.',
        5  => isset($_GET['revision']) ? 'Map restored to revision from ' . wp_post_revision_title((int)$_GET['revision'], false) : false,
        6  => 'Map published. <a href="' . esc_url(get_permalink($post_ID)) . '">View map</a>',
        7  => 'Map saved.',
        8  => 'Map submitted. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview map</a>',
        9  => 'Map scheduled for: <strong>' . strtotime($post->post_date) . '</strong>. <a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">Preview map</a>',
        10 => 'Map draft updated. <a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">Preview map</a>',
    );

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_updated_maps_messages');
#endregion

#region Post Category
/**
 * This method initializes the post category functionality for Maps
 */
function mp_dd_maps_post()
{

    $labels = array(
        'name'               => 'All Maps',
        'singular_name'      => 'Map',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Map',
        'edit_item'          => 'Edit Map',
        'new_item'           => 'New Map',
        'view_item'          => 'View Map',
        'search_items'       => 'Search Maps',
        'not_found'          => 'No Maps found',
        'not_found_in_trash' => 'No Maps found in Trash',
        'parent_item_colon'  => 'Parent Map:',
        'menu_name'          => 'Maps',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Maps filterable by category',
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

    register_post_type('map', $args);
}

add_action('init', 'mp_dd_maps_post');
#endregion

#region Meta Boxes
/**
 * This method adds the custom Meta Boxes
 */
function mp_dd_map_meta_boxes()
{
    add_meta_box('mp_dd_map_include_info', 'Info', 'mp_dd_map_include_info', 'map', 'after_title', 'high');

    global $post;
    if (is_array(get_post_meta($post->ID, 'visible_cities', true))) {
        add_meta_box('mp_dd_map_building_labels', 'Building Labels', 'mp_dd_map_building_labels', 'map', 'normal', 'high');
    }
}

add_action('add_meta_boxes', 'mp_dd_map_meta_boxes');

function mp_dd_map_include_info()
{
    global $post;
    global $wpdb;
    ?>
    <p><code>[map-<?= $post->ID ?>]</code> You can insert this tag in a post to include the map.</p>
    <?php
    $cities        = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'city' AND post_status = 'publish'");
    $cities        = array_combine(array_column($cities, 'ID'), array_column($cities, 'post_title'));
    $visibleCities = get_post_meta($post->ID, 'visible_cities', true);
    if (!is_array($visibleCities)) {
        $visibleCities = array();
    }
    ?>
    <table>
        <tr>
            <td><label for="visible_cities">Visible Cities</label></td>
            <td>
                <select id="visible_cities" name="visible_cities[]" multiple>
                    <?php foreach ($cities as $id => $city): ?>
                        <option value="<?= $id ?>" <?= in_array($id, $visibleCities) ? 'selected' : '' ?>><?= $city ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

function mp_dd_map_building_labels()
{
    global $post;
    $visibleCities     = get_post_meta($post->ID, 'visible_cities', true);
    $allBuildingLabels = get_post_meta($post->ID, 'building_labels', true);
    $allBuildingLabels = array_combine(array_column($allBuildingLabels, 'id'), $allBuildingLabels);
    foreach ($visibleCities as $visibleCity) {
        $args           = array(
            'post_type'      => 'building',
            'meta_query'     => array(
                array(
                    'key'     => 'city',
                    'value'   => $visibleCity,
                    'compare' => '=',
                ),
            ),
            'posts_per_page' => -1,
        );
        $cityBuildings  = get_posts($args);
        $buildingLabels = array();
        /** @var WP_Post $building */
        foreach ($cityBuildings as $building) {
            if (array_key_exists($building->ID, $allBuildingLabels)) {
                $buildingLabels[$building->ID] = $allBuildingLabels[$building->ID];
            } else {
                $buildingType = get_post_meta($building->ID, 'type', true);
                switch ($buildingType) {
                    case 'merchants':
                        $color = '#6a1b9a';
                        break;
                    case 'guardhouses':
                        $color = '#1976d2';
                        break;
                    case 'churches':
                        $color = '#d50000';
                        break;
                    case 'guilds':
                        $color = '#1b5e20';
                        break;
                    default:
                        $color = '#000000';
                        break;
                }
                $buildingLabels[$building->ID] = array(
                    'id'      => $building->ID,
                    'showing' => false,
                    'label'   => $building->post_title,
                    'top'     => 0,
                    'left'    => 0,
                    'color'   => $color,
                );
            }
        }

        foreach ($buildingLabels as $buildingLabel) {
            ?>
            <table style="position: relative; display: inline-block; border: 1px solid black; margin-right: 4px;">
                <tbody>
                <tr>
                    <td><label>Building</label></td>
                    <td>
                        <input type="hidden" name="id[]" value="<?= $buildingLabel['id'] ?>">
                        <input type="hidden" name="color[]" value="<?= $buildingLabel['color'] ?>">
                        <?= $buildingLabel['id'] ?>
                    </td>
                    <td align="right">
                        <select name="showing[]" title="Showing">
                            <option value="true" <?= $buildingLabel['showing'] ? 'selected' : '' ?>>Showing</option>
                            <option value="false" <?= !$buildingLabel['showing'] ? 'selected' : '' ?>>Hidden</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Label</label></td>
                    <td colspan="2"><input type="text" name="label[]" value="<?= $buildingLabel['label'] ?>" title="Label" style="width: 125px;"></td>
                </tr>
                <tr>
                    <td><label>Top</label></td>
                    <td colspan="2"><input type="number" name="top[]" value="<?= $buildingLabel['top'] ?>" title="Top" style="width: 125px;"></td>
                </tr>
                <tr>
                    <td><label>Left</label></td>
                    <td colspan="2"><input type="number" name="left[]" value="<?= $buildingLabel['left'] ?>" title="Left" style="width: 125px;"></td>
                </tr>
                </tbody>
            </table>
            <?php
        }
    }
}

#endregion

#region Save Meta
/**
 * @param $post_id
 *
 * @return int the post_id
 */
function mp_dd_map_save_meta($post_id)
{
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['visible_cities'])) {
            update_post_meta($post_id, 'visible_cities', $_POST['visible_cities']);
        }
        if (isset($_POST['id']) && isset($_POST['color']) && isset($_POST['showing']) && isset($_POST['label']) && isset($_POST['top']) && isset($_POST['left'])) {
            $buildingLabels = array();
            for ($i = 0; $i < count($_POST['label']); $i++) {
                $buildingLabels[] = array(
                    'id'      => $_POST['id'][$i],
                    'color'   => $_POST['color'][$i],
                    'showing' => filter_var($_POST['showing'][$i], FILTER_VALIDATE_BOOLEAN),
                    'label'   => $_POST['label'][$i],
                    'top'     => $_POST['top'][$i],
                    'left'    => $_POST['left'][$i],
                );
            }
            update_post_meta($post_id, 'building_labels', $buildingLabels);
        }
    }
    return $post_id;
}

add_action('save_post_map', 'mp_dd_map_save_meta');
#endregion
