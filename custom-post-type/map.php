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
    add_meta_box('mp_dd_map_include_tag', 'Tags', 'mp_dd_map_include_tag', 'map', 'after_title', 'high');
    add_meta_box('mp_dd_map_building_labels', 'Tags', 'mp_dd_map_building_labels', 'map', 'normal', 'high');
}

add_action('add_meta_boxes', 'mp_dd_map_meta_boxes');

function mp_dd_map_include_tag()
{
    global $post;
    ?>
    <p><code>[map-<?= $post->ID ?>]</code> You can insert this tag in a post to include the map.</p>
    <?php
}

function mp_dd_map_building_labels()
{
    global $post;
    $buildingLabels = get_post_meta($post->ID, 'building_labels', true);
    foreach ($buildingLabels as $buildingLabel) {
        ?>
        <table style="position: relative; display: inline-block; border: 1px solid black;">
            <tr>
                <td><label>Label</label></td>
                <td><input type="number" name="label[]" style="width: 75px;" title="label" value="<?= $buildingLabel['id'] ?>"></td>
            </tr>
            <tr>
                <td><label>Building</label></td>
                <td><input type="number" name="wp_id[]" style="width: 75px;" title="wp_id" value="<?= isset($buildingLabel['wp_id']) ? $buildingLabel['wp_id'] : '' ?>"></td>
            </tr>
            <tr>
                <td><label>Top</label></td>
                <td><input type="number" name="top[]" style="width: 75px;" title="top" value="<?= $buildingLabel['top'] ?>"></td>
            </tr>
            <tr>
                <td><label>Left</label></td>
                <td><input type="number" name="left[]" style="width: 75px;" title="left" value="<?= $buildingLabel['left'] ?>"></td>
            </tr>
        </table>
        <?php
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
        $buildingLabels = array();
        if (isset($_POST['label']) && isset($_POST['wp_id']) && isset($_POST['top']) && isset($_POST['left'])) {
            for ($i = 0; $i < count($_POST['label']); $i++) {
                if (!empty($_POST['wp_id'][$i])) {
                    $building = get_post($_POST['wp_id'][$i]);
                    $link  = true;
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
                } else {
                    $link  = false;
                    $color = '#000000';
                }
                $buildingLabels[] = array(
                    'id'    => $_POST['label'][$i],
                    'wp_id' => $_POST['wp_id'][$i],
                    'top'   => $_POST['top'][$i],
                    'left'  => $_POST['left'][$i],
                    'link'  => $link,
                    'color' => $color,
                );
            }
            update_post_meta($post_id, 'building_labels', $buildingLabels);
        }
    }
    return $post_id;
}

add_action('save_post_map', 'mp_dd_map_save_meta');
#endregion

#region Post Content
function mp_dd_filter_map_content($content)
{
    global $post;
    if (preg_match_all("/\[map-([0-9]+)\]/", $content, $mapMatches) || $post->post_type == 'map') {
        if (preg_match_all("/\[map-([0-9]+)\]/", $content, $mapMatches)) {
            foreach ($mapMatches[1] as $mapID) {
                $map     = get_post($mapID);
                $mapHTML = $map->post_content;
                if (strpos($mapHTML, '[building-labels]') !== false) {
                    $buildingLabels = get_post_meta($mapID, 'building_labels', true);
                    ob_start();
                    foreach ($buildingLabels as $buildingLabel) {
                        ?>
                        <div style="position:absolute; top:<?= $buildingLabel['top'] ?>px; left:<?= $buildingLabel['left'] ?>px;">
                            <?php if ($buildingLabel['link']): ?>
                                <?php $url = isset($buildingLabel['wp_id']) ? '[building-url-' . $buildingLabel['wp_id'] . ']' : '#modal' . $buildingLabel['id']; ?>
                                <a href="<?= $url ?>"
                                   style="color: #FFFFFF; background: rgba(0,0,0,0.6); height: 30px; width: 30px; text-align: center; display: block; border: 3px solid <?= $buildingLabel['color'] ?>; border-radius: 20%;font-size: 9px;line-height: 25px;">
                                    <?= $buildingLabel['id'] ?>
                                </a>
                            <?php else: ?>
                                <p style="color: #000000; background: #FFFFFF; height: 30px; width: 30px; text-align: center; display: block; border: 3px solid black; border-radius: 20%; margin: 0;font-size: 9px;line-height: 25px;">
                                    <?= $buildingLabel['id'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    $mapHTML = str_replace('[building-labels]', ob_get_clean(), $mapHTML);
                }
                $content = str_replace("[map-$mapID]", $mapHTML, $content);
            }
        }
        if ($post->post_type == 'map') {
            if (strpos($content, '[building-labels]') !== false) {
                $buildingLabels = get_post_meta($post->ID, 'building_labels', true);
                ob_start();
                foreach ($buildingLabels as $buildingLabel) {
                    ?>
                    <div style="position:absolute; top:<?= $buildingLabel['top'] ?>px; left:<?= $buildingLabel['left'] ?>px; z-index: 100">
                        <?php if ($buildingLabel['link']): ?>
                            <?php $url = isset($buildingLabel['wp_id']) ? '[building-url-' . $buildingLabel['wp_id'] . ']' : '#modal' . $buildingLabel['id']; ?>
                            <a href="<?= $url ?>"
                               style="color: #FFFFFF; background: rgba(0,0,0,0.6); height: 30px; width: 30px; text-align: center; display: block; border: 3px solid <?= $buildingLabel['color'] ?>; border-radius: 20%;font-size: 9px;line-height: 25px;">
                                <?= $buildingLabel['id'] ?>
                            </a>
                        <?php else: ?>
                            <p style="color: #000000; background: #FFFFFF; height: 30px; width: 30px; text-align: center; display: block; border: 3px solid black; border-radius: 20%; margin: 0;font-size: 9px;line-height: 25px;">
                                <?= $buildingLabel['id'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                $content = str_replace('[building-labels]', ob_get_clean(), $content);
            }
        }
    }

    return $content;
}

add_filter('the_content', 'mp_dd_filter_map_content');
#endregion
