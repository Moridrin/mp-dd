<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

require_once 'race-content.php';

function mp_dd_register_races_post_type()
{

    $labels = array(
        'name'                  => 'Races',
        'singular_name'         => 'Race',
        'add_new'               => 'Add new',
        'add_new_item'          => 'Add New Race',
        'edit_item'             => 'Edit Race',
        'new_item'              => 'New Race',
        'view_item'             => 'View Race',
        'search_items'          => 'Search Races',
        'not_found'             => 'No Race found',
        'not_found_in_trash'    => 'No Race found in Trash',
        'menu_name'             => 'D&D Race',
        'all_items'             => 'All Races',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Race',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        "show_in_menu"    => 'edit.php?post_type=creature',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'dd_race',
    );

    register_post_type('race', $args);
}

add_action('init', 'mp_dd_register_races_post_type');

function mp_dd_add_race_meta_boxes()
{
    add_meta_box('mp_dd_race_properties', 'Properties', 'mp_dd_race_properties', 'race', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_race_meta_boxes');

function mp_dd_race_properties()
{
    global $post;
    $race = Race::load($post->ID);
    ?>
    <table class="wp-list-table widefat fixed striped vertical-center">
        <tbody id="properties-placeholder"></tbody>
    </table>
    <button onclick="mp_dd_add_new_property(event)">Add Property</button>
    <script>
        var index = 0;
        function mp_dd_add_new_property(event) {
            event.preventDefault();
            mp_dd_add_property(index, '', '');
            index++;
        }
        function mp_dd_add_property(id, title, description) {
            var container = document.getElementById("properties-placeholder");
            var tr = document.createElement("tr");
            tr.setAttribute("id", id + "_tr");
            tr.appendChild(getTextInputTD('property', 'title', id, title));
            tr.appendChild(getTextAreaTD('property', 'description', id, description));
            container.appendChild(tr);
        }
        <?php foreach($race->properties as $title => $description): ?>
        mp_dd_add_property(index, '<?= $title ?>', '<?= str_replace('<br/>', '\n', $description) ?>');
        index++;
        <?php endforeach; ?>
    </script>
    <?php
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_race_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return $post_id;
    }
    if (isset($_POST['aliases'])) {
        $aliases = !empty($_POST['aliases']) ? explode(',', $_POST['aliases']) : array();
        global $wpdb;
        /** @noinspection PhpIncludeInspection */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix . "mp_race_aliases";
        $wpdb->delete($table_name, array('post_id' => $post_id));
        $nogo_list = mp_dd_get_available_tags(true);

        $correct_aliases = array();
        foreach ($aliases as $alias) {
            if (!in_array(strtolower($alias), $nogo_list)) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'alias'   => strtolower($alias),
                        'post_id' => $post_id,
                    ),
                    array(
                        '%s',
                        '%d',
                    )
                );
                $correct_aliases[] = $alias;
            }
        }
        update_post_meta($post->ID, 'aliases', implode(',', $correct_aliases));
    }
    $race = Race::fromPOST($post_id);
    $race->save();
    return $post_id;
}

add_action('save_post_race', 'mp_dd_save_race_meta', 1, 2);