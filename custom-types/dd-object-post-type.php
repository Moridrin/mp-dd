<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

function mp_dd_register_dd_object_post_category()
{

    $labels = array(
        'name'               => _x('D&D Objects', 'dd_object'),
        'singular_name'      => _x('D&D Object', 'dd_object'),
        'add_new'            => _x('Add New', 'dd_object'),
        'add_new_item'       => _x('Add New D&D Object', 'dd_object'),
        'edit_item'          => _x('Edit D&D Object', 'dd_object'),
        'new_item'           => _x('New D&D Object', 'dd_object'),
        'view_item'          => _x('View D&D Object', 'dd_object'),
        'search_items'       => _x('Search D&D Objects', 'dd_object'),
        'not_found'          => _x('No D&D Objects found', 'dd_object'),
        'not_found_in_trash' => _x('No D&D Objects found in Trash', 'dd_object'),
        'parent_item_colon'  => _x('Parent D&D Object:', 'dd_object'),
        'menu_name'          => _x('D&D Objects', 'dd_object'),
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'D&D Objects filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        //        'taxonomies'          => array('dd_object_category', 'dd_tag'),
        'taxonomies'          => array('dd_object_category'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-archive',
        'show_in_nav_menus'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post',
    );

    register_post_type('dd_object', $args);
}

add_action('init', 'mp_dd_register_dd_object_post_category');

function mp_dd_register_dd_object_taxonomy()
{
    register_taxonomy(
        'dd_object_category',
        'dd_object',
        array(
            'hierarchical' => true,
            'label'        => 'Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'dd_object_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_register_dd_object_taxonomy');

function mp_dd_add_dd_object_metaboxes()
{
    add_meta_box('mp_dd_parent', 'Parent', 'mp_dd_parent', 'dd_object', 'side', 'default');
    add_meta_box('mp_dd_children', 'Children', 'mp_dd_chidren', 'dd_object', 'side', 'default');
    add_meta_box('mp_dd_object_date', 'Alias', 'mp_dd_object_date', 'dd_object', 'side', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_dd_object_metaboxes');

function mp_dd_parent()
{
    global $post;
    ?>
    <label class="selectit"><input type="checkbox" name="display_parent" value="yes" <?= get_post_meta($post->ID, 'display_parent', true) == 'yes' ? 'checked' : '' ?>> Display Parent</label><br/><br/>
    <input type="text" name="display_parent_header" placeholder="Header" class="form-input-tip" size="16" autocomplete="off" value="<?= get_post_meta($post->ID, 'display_parent_header', true) ?>">
    <?php
}

function mp_dd_chidren()
{
    global $post;
    ?>
    <label class="selectit"><input type="checkbox" name="display_children" value="yes" <?= get_post_meta($post->ID, 'display_children', true) == 'yes' ? 'checked' : '' ?>> Display Children</label><br/><br/>
    <input type="text" name="display_children_header" placeholder="Header" class="form-input-tip" size="16" autocomplete="off" value="<?= get_post_meta($post->ID, 'display_children_header', true) ?>">
    <?php
}

function mp_dd_object_date()
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
function mp_dd_save_dd_object_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    if (isset($_POST['display_parent'])) {
        update_post_meta($post->ID, 'display_parent', 'yes');
    } else {
        update_post_meta($post->ID, 'display_parent', 'no');
    }
    update_post_meta($post->ID, 'display_parent_header', $_POST['display_parent_header']);
    if (isset($_POST['display_children'])) {
        update_post_meta($post->ID, 'display_children', 'yes');
    } else {
        update_post_meta($post->ID, 'display_children', 'no');
    }
    update_post_meta($post->ID, 'display_children_header', $_POST['display_children_header']);
    if (isset($_POST['aliases'])) {

        $aliases = !empty($_POST['aliases']) ? explode(',', $_POST['aliases']) : array();
        global $wpdb;
        /** @noinspection PhpIncludeInspection */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix . "mp_dd_object_aliases";
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
    return $post_id;
}

add_action('save_post', 'mp_dd_save_dd_object_meta', 1, 2);

function mp_dd_get_available_tags($include_aliases = false)
{
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $used_tags = array();
    $existing_tags = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'dd_object'");
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
    $table_name    = $wpdb->prefix . "mp_dd_object_aliases";
    $existing_tags = $wpdb->get_results("SELECT alias FROM {$table_name} WHERE post_id = {$post_id}");
    foreach ($existing_tags as $alias) {
        $used_tags[] = strtolower($alias->alias);
    }
    return $used_tags;
}

/** @noinspection PhpUnusedParameterInspection */
add_filter(
    'get_pages',
    function ($pages, $args) {
        if (!is_admin()) {
            return $pages;
        }

        global $pagenow;
        if ('options-reading.php' !== $pagenow) {
            return $pages;
        }

        remove_filter(current_filter(), __FUNCTION__);
        $args      = [
            'post_type'      => 'dd_object',
            'posts_per_page' => -1,
        ];
        $new_pages = get_posts($args);
        $pages += $new_pages;

        return $pages;
    }

    ,
    10,
    2
);

function mp_dd_include_custom_dd_object_filter_fields()
{
    if (strpos($_SERVER['REQUEST_URI'], 'edit.php?post_type=dd_object') === false || get_option('ssv_frontend_members_custom_users_filters', 'hide') == 'hide') {
        return;
    }
    $fields = FrontendMembersField::getAll(array('field_type' => 'input'));
    $fields = array('category');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($fields as $field) {
            if (isset($_POST['clear_filters']) || !isset($_POST['filter_' . $field]) || empty($_POST['filter_' . $field])) {
                unset($_SESSION['filter_' . $field]);
            } else {
                $_SESSION['filter_' . $field] = $_POST['filter_' . $field];
            }
        }
        if (isset($_GET['paged']) && $_GET['paged'] > 1) {
            $uri = $_SERVER['REQUEST_URI'];
            $uri = str_replace('paged=' . $_GET['paged'], 'paged=1', $uri);
            ssv_redirect($uri);
        }
    }
    $filters     = '';
    $selected    = json_decode(get_option('ssv_frontend_members_user_filters'));
    $selected    = $selected ?: array();
    $addedFields = array();
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        if (in_array($field->name, $selected) && !in_array($field->name, $addedFields)) {
            $filters .= '<div style="display: inline-block; margin-right: 6px;">';
            $filters .= $field->getFilter();
            $filters .= '</div>';
            $addedFields[] = $field->name;
        }
    }
    $filters .= '<br/><button type="submit" value="submit" class="button" style="margin-right: 6px;">Filter</button>';
    $filters .= '<button type="submit" name="clear_filters" value="clear_filters" class="button">Clear Filters</button>';
    ?>
    <script>
        window.onload = function () {
            jQuery(document).ready(function ($) {
                var old_filter_area = $('.subsubsub');
                old_filter_area.before('<h2 style="margin-bottom: 0;">Filters</h2>');
                old_filter_area.after('<form name="filter_form" method="post"><div id="filter_area"></div></form>');
                <?php if (get_option('ssv_frontend_members_custom_users_filters', 'under') == 'replace'): ?>
                old_filter_area.remove();
                <?php endif; ?>
                var filter_area = $('#filter_area');
                filter_area.html('<?php echo $filters; ?>');
            });
        };
    </script>
    <?php
}

add_action('admin_init', 'mp_dd_include_custom_dd_object_filter_fields');

function mp_dd_custom_dd_object_filters($query)
{
    if (strpos($_SERVER['REQUEST_URI'], 'users.php') === false) {
        return $query;
    }
    global $wpdb;
    $filtered = array();
    $fields   = FrontendMembersField::getAll(array('field_type' => 'input'));
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        if (isset($_SESSION['filter_' . $field->name]) && !in_array($field->name, $filtered)) {
            $value = $_SESSION['filter_' . $field->name];
            switch (get_class($field)) {
                case FrontendMembersFieldInputCustom::class:
                case FrontendMembersFieldInputText::class:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    if (strpos($value, '<') !== false) {
                        $value = str_replace('<', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value < '{$value}'";
                    } elseif (strpos($value, '>') !== false) {
                        $value = str_replace('>', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value > '{$value}'";
                    } elseif (strpos($value, '!') !== false && (strpos($value, "'") !== false || strpos($value, '"') !== false)) {
                        $value = str_replace('!', '', $value);
                        $value = str_replace("'", '', $value);
                        $value = str_replace('"', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value != '{$value}'";
                    } elseif (strpos($value, '!') !== false) {
                        $value = str_replace('!', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value NOT LIKE '%{$value}%'";
                    } elseif (strpos($value, "\\'") !== false || strpos($value, '\\"') !== false) {
                        $value = str_replace("\\'", '', $value);
                        $value = str_replace('\\"', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value = '{$value}'";
                    } else {
                        $query->query_where .= " AND {$table_alias}.meta_value LIKE '%{$value}%'";
                    }
                    break;
                case FrontendMembersFieldInputImage::class:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " LEFT OUTER JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    if ($value == 'no') {
                        $query->query_where .= " AND profile_picturemeta.meta_key IS NULL";
                    } else {
                        $query->query_where .= " AND profile_picturemeta.meta_key = '" . $field->name . "'";
                    }
                    break;
                case FrontendMembersFieldInputSelect::class:
                case FrontendMembersFieldInputRoleCheckbox::class:
                case FrontendMembersFieldInputTextCheckbox::class:
                default:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    $query->query_where .= " AND {$table_alias}.meta_value LIKE '{$value}'";
                    break;
            }
            $filtered[] = $field->name;
        }
    }
    return $query;
}

add_filter('pre_user_query', 'mp_dd_custom_dd_object_filters');