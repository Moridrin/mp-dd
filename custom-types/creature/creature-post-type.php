<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

require_once 'creature-content.php';

function mp_dd_register_creatures_post_type()
{

    $labels = array(
        'name'               => 'Creatures',
        'singular_name'      => 'Creature',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Creature',
        'edit_item'          => 'Edit Creature',
        'new_item'           => 'New Creature',
        'view_item'          => 'View Creature',
        'search_items'       => 'Search Creatures',
        'not_found'          => 'No Creatures found',
        'not_found_in_trash' => 'No Creatures found in Trash',
        'parent_item_colon'  => 'Parent Creature:',
        'menu_name'          => 'D&D Creatures',
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Creatures filterable by category',
        'supports'            => array('title', 'editor', 'thumbnail', 'revisions'),
        'taxonomies'          => array('creature_category'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-universal-access',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('creature', $args);
}

add_action('init', 'mp_dd_register_creatures_post_type');

function mp_dd_register_creatures_taxonomy()
{
    register_taxonomy(
        'creature_category',
        'creature',
        array(
            'hierarchical' => true,
            'label'        => 'Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'creature_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_register_creatures_taxonomy');

function mp_dd_add_creature_meta_boxes()
{
    add_meta_box('mp_dd_creature_stats', 'Stats', 'mp_dd_creature_stats', 'creature', 'advanced', 'default');
    add_meta_box('mp_dd_creature_items', 'Items', 'mp_dd_creature_items', 'creature', 'advanced', 'default');
    add_meta_box('mp_dd_creature_properties', 'Properties', 'mp_dd_creature_properties', 'creature', 'advanced', 'default');
    add_meta_box('mp_dd_creature_aliases', 'Aliases', 'mp_dd_creature_aliases', 'creature', 'side', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_creature_meta_boxes');

function mp_dd_creature_stats()
{
    global $post;
    $creature = Creature::load($post->ID);
    echo $creature->getStatsEditor();
}

function mp_dd_creature_items()
{
    global $post;
    $creature = Creature::load($post->ID);
    $args     = array(
        'post_type'      => array('item', 'weapon', 'armor'),
        'posts_per_page' => -1,
        'numberposts'    => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );
    $items    = get_posts($args);
    ?>
    <p id="current_items"><?= $creature->getCurrentItemsList() ?></p>
    <input type="text" id="item-selector" onkeyup="filterItems()" placeholder="Search for items.." title="Find Item">
    <ul id="item-list">
        <?php foreach ($items as $itemPost): ?>
            <?php $item = Item::getByID($itemPost->ID, $itemPost->post_type); ?>
            <?php $key = $itemPost->post_type . '_' . $itemPost->ID; ?>
            <?php $count = array_key_exists($key, $creature->items) ? $creature->items[$key] : 0; ?>
            <li>
                <input
                        type="number"
                        name="items[<?= $itemPost->post_type . '_' . $itemPost->ID ?>]"
                        min="0"
                        value="<?= $count ?>"
                        onchange="mp_dd_item_count_changed(event)"
                        style="max-width: 50px;"
                />
                <?= $item ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        function filterItems() {
            var input, filter, ul, li, a, i;
            input = document.getElementById("item-selector");
            filter = input.value.toUpperCase();
            ul = document.getElementById("item-list");
            li = ul.getElementsByTagName("li");
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByTagName("a")[0];
                if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";

                }
            }
        }

        function mp_dd_item_count_changed(event) {
            event.preventDefault();
        }
    </script>
    <?php
}

function mp_dd_creature_properties()
{
    global $post;
    $creature = Creature::load($post->ID);
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
        <?php foreach($creature->properties as $title => $description): ?>
        mp_dd_add_property(index, '<?= $title ?>', '<?= str_replace('<br/>', '\n', $description) ?>');
        index++;
        <?php endforeach; ?>
    </script>
    <?php
}

function mp_dd_creature_aliases()
{
    global $post;
    ?>
    <div>
        <div>
            <div>
                <div class="hide-if-js">
                    <label for="aliases">Add or remove aliases</label>
                    <p><textarea name="aliases" rows="3" cols="20" id="aliases"><?php echo get_post_meta($post->ID, 'aliases', true); ?></textarea></p>
                </div>
                <div class="hide-if-no-js">
                    <label class="screen-reader-text" for="new-alias">Add New Alias</label>
                    <p>
                        <input type="text" id="new-alias" name="new_alias" class="form-input-tip" size="16" autocomplete="off" value="" onkeydown="if (event.keyCode == 13) document.getElementById('new-alias-submit').click()">
                        <input type="button" id="new-alias-submit" onclick="add_alias()" class="button" value="Add">
                    </p>
                </div>
                <p class="howto" id="new-alias-dd_tag-desc">Separate aliases with commas</p>
            </div>
            <div id="aliases_list" class="tagchecklist">
                <?php
                $aliases = get_post_meta($post->ID, 'aliases', true);
                $aliases = !empty($aliases) ? explode(',', $aliases) : array();
                $i       = 0;
                foreach ($aliases as $alias) {
                    ?><span id="alias_id_<?= $i ?>"><a id="dd_tag-check-num-<?= $i ?>" class="ntdelbutton" tabindex="<?= $i ?>" onclick="remove_alias(<?= $i ?>)">X</a>&nbsp;<?= $alias ?></span><?php
                    $i++;
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        index = <?= $i ?>;
        function add_alias() {
            var new_alias_field = document.getElementById('new-alias');
            var aliases_field = document.getElementById('aliases');
            var aliases_list = document.getElementById('aliases_list');
            var new_alias = new_alias_field.value;
            var current_aliases = aliases_field.innerHTML;
            if (current_aliases != '') {
                aliases_field.innerHTML = current_aliases + ',' + new_alias;
            } else {
                aliases_field.innerHTML = new_alias;
            }
            var new_element = document.createElement('span');
            new_element.id = 'alias_id_' + index;
            new_element.innerHTML = '<a id="dd_tag-check-num-' + index + '" class="ntdelbutton" tabindex="' + index + '" onclick="remove_alias(' + index + ')">X</a>&nbsp;' + new_alias;
            aliases_list.appendChild(new_element);
            new_alias_field.value = '';
            index++;
            document.getElementById("post").submit();
        }

        function remove_alias(alias_id) {
            var child = document.getElementById('alias_id_' + alias_id);
            var parent = child.parentNode;
            var alias_index = Array.prototype.indexOf.call(parent.children, child);
            var aliases_field = document.getElementById('aliases');
            var aliases_list = document.getElementById('aliases_list');
            var aliases = aliases_field.innerHTML.split(',');
            aliases.splice(alias_index, 1);
            aliases_field.innerHTML = aliases;
            aliases_list.children[alias_index].remove();
            document.getElementById("post").submit();
        }
    </script>
    <?php
}

/**
 * @param $post_id
 * @param $post
 *
 * @return int the post_id
 */
function mp_dd_save_creature_meta($post_id, $post)
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
        $table_name = $wpdb->prefix . "mp_creature_aliases";
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
    $creature = Creature::fromPOST($post_id);
    $creature->save();
    return $post_id;
}

add_action('save_post_creature', 'mp_dd_save_creature_meta', 1, 2);

function mp_dd_get_available_tags($include_aliases = false)
{
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $used_tags     = array();
    $existing_tags = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'creature' AND post_status = 'publish'");
    foreach ($existing_tags as $alias) {
        $used_tags[$alias->ID] = strtolower($alias->post_title);
        if ($include_aliases) {
            foreach (mp_dd_get_aliases_for_post($alias->ID) as $item) {
                $used_tags[$alias->ID . '_alias_' . $item] = $item;
            }
        }
    }
    return $used_tags;
}

function mp_dd_get_aliases_for_post($post_id)
{
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $used_tags     = array();
    $table_name    = $wpdb->prefix . "mp_creature_aliases";
    $existing_tags = $wpdb->get_results("SELECT alias FROM {$table_name} WHERE post_id = {$post_id}");
    foreach ($existing_tags as $alias) {
        $used_tags[] = strtolower($alias->alias);
    }
    return $used_tags;
}
