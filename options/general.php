<?php
namespace mp_dd;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cityID = $_POST['city'];
    update_option(MP_DD::OPTION_LAST_CITY_REMOVED, $cityID);
    $args = array(
        'post_type'      => 'map',
        'meta_query'     => array(
            array(
                'key'     => 'visible_cities',
                'value'   => 'a:1:{i:0;i:' . $cityID . ';}', //TODO Test if this works
                'compare' => '=',
            ),
        ),
        'posts_per_page' => -1,
    );
    /** @var WP_Post[] $maps */
    $maps = get_posts($args);
    foreach ($maps as $map) {
        wp_delete_post($map->ID, true);
    }

    $args = array(
        'post_type'      => 'building',
        'meta_query'     => array(
            array(
                'key'     => 'city',
                'value'   => $cityID,
                'compare' => '=',
            ),
        ),
        'posts_per_page' => -1,
    );
    /** @var WP_Post[] $cityBuildings */
    $cityBuildings = get_posts($args);
    foreach ($cityBuildings as $building) {
        $ownerID = get_post_meta($building->ID, 'owner', true);
        $spouse  = get_post_meta($ownerID, 'spouse', true);
        if ($spouse) {
            wp_delete_post($spouse, true);
        }
        $children = get_post_meta($ownerID, 'children', true);
        if ($children) {
            foreach ($children as $child) {
                wp_delete_post($child, true);
            }
        }
        wp_delete_post($ownerID, true);

        $args = array(
            'post_type'      => 'npc',
            'meta_query'     => array(
                array(
                    'key'     => 'building_id',
                    'value'   => $building->ID,
                    'compare' => '=',
                ),
            ),
            'posts_per_page' => -1,
        );
        /** @var WP_Post[] $cityBuildings */
        $workers = get_posts($args);
        foreach ($workers as $worker) {
            wp_delete_post($worker->ID, true);
        }

        wp_delete_post($building->ID, true);
    }
    $cty = get_post($cityID);
    if ($cty) {
        wp_delete_post($cityID, true);
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="city">City</label></th>
            <td>
                <?php /** @var WP_Post[] $cities */ ?>
                <?php $cities = get_posts(array('post_type' => 'city')); ?>
                <datalist id="cities">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city->ID ?>"><?= $city->post_title ?></option>
                    <?php endforeach; ?>
                </datalist>
                <input type="text" id="city" name="city" list="cities" value="<?= isset($_GET['city']) ? $_GET['city'] : '' ?>">
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
