<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

require_once 'property-group-content.php';

function mp_dd_register_property_group_post_types()
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
        'slug'            => 'race',
    );

    register_post_type('race', $args);

    $labels = array(
        'name'                  => 'Classes',
        'singular_name'         => 'Class',
        'add_new'               => 'Add new',
        'add_new_item'          => 'Add New Class',
        'edit_item'             => 'Edit Class',
        'new_item'              => 'New Class',
        'view_item'             => 'View Class',
        'search_items'          => 'Search Classes',
        'not_found'             => 'No Class found',
        'not_found_in_trash'    => 'No Class found in Trash',
        'menu_name'             => 'D&D Class',
        'all_items'             => 'All Classes',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Class',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        "show_in_menu"    => 'edit.php?post_type=creature',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'class',
    );

    register_post_type('class', $args);

    $labels = array(
        'name'                  => 'Backgrounds',
        'singular_name'         => 'Background',
        'add_new'               => 'Add new',
        'add_new_item'          => 'Add New Background',
        'edit_item'             => 'Edit Background',
        'new_item'              => 'New Background',
        'view_item'             => 'View Background',
        'search_items'          => 'Search Backgrounds',
        'not_found'             => 'No Background found',
        'not_found_in_trash'    => 'No Background found in Trash',
        'menu_name'             => 'D&D Background',
        'all_items'             => 'All Backgrounds',
    );

    $args = array(
        'labels'          => $labels,
        'hierarchical'    => false,
        'description'     => 'Background',
        'supports'        => array('title', 'editor', 'thumbnail', 'revisions'),
        'public'          => true,
        'show_ui'         => true,
        "show_in_menu"    => 'edit.php?post_type=creature',
        'has_archive'     => true,
        'capability_type' => 'post',
        'slug'            => 'background',
    );

    register_post_type('background', $args);
}

add_action('init', 'mp_dd_register_property_group_post_types');

function mp_dd_add_property_group_meta_boxes()
{
    add_meta_box('mp_dd_race_properties', 'Properties', 'mp_dd_property_group_properties', 'race', 'advanced', 'default');
    add_meta_box('mp_dd_class_properties', 'Properties', 'mp_dd_property_group_properties', 'class', 'advanced', 'default');
    add_meta_box('mp_dd_background_properties', 'Properties', 'mp_dd_property_group_properties', 'background', 'advanced', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_property_group_meta_boxes');

function mp_dd_property_group_properties()
{
    global $post;
    $propertyGroup = PropertyGroup::load($post->ID);
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
        <?php foreach($propertyGroup->properties as $title => $description): ?>
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
function mp_dd_save_property_group_meta($post_id, $post)
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
    $propertyGroup = PropertyGroup::fromPOST($post_id);
    $propertyGroup->save();
    return $post_id;
}

add_action('save_post_race', 'mp_dd_save_property_group_meta', 1, 2);
add_action('save_post_class', 'mp_dd_save_property_group_meta', 1, 2);
add_action('save_post_background', 'mp_dd_save_property_group_meta', 1, 2);
