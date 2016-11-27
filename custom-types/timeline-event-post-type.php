<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:21
 */

function mp_dd_register_timeline_event_post_category()
{

    $labels = array(
        'name'               => _x('Timeline Events', 'timeline_event'),
        'singular_name'      => _x('Timeline Event', 'timeline_event'),
        'add_new'            => _x('Add New', 'timeline_event'),
        'add_new_item'       => _x('Add New Timeline Event', 'timeline_event'),
        'edit_item'          => _x('Edit Timeline Event', 'timeline_event'),
        'new_item'           => _x('New Timeline Event', 'timeline_event'),
        'view_item'          => _x('View Timeline Event', 'timeline_event'),
        'search_items'       => _x('Search Timeline Events', 'timeline_event'),
        'not_found'          => _x('No Timeline Events found', 'timeline_event'),
        'not_found_in_trash' => _x('No Timeline Events found in Trash', 'timeline_event'),
        'parent_item_colon'  => _x('Parent Timeline Event:', 'timeline_event'),
        'menu_name'          => _x('Timeline Events', 'timeline_event'),
    );

    $args = array(
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'Timeline Events filterable by category',
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes'),
        'taxonomies'          => array('event_category'),
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
    );

    register_post_type('timeline_event', $args);
}

add_action('init', 'mp_dd_register_timeline_event_post_category');

function mp_dd_register_event_taxonomy()
{
    register_taxonomy(
        'event_category',
        'timeline_event',
        array(
            'hierarchical' => true,
            'label'        => 'Categories',
            'query_var'    => true,
            'rewrite'      => array(
                'slug'       => 'event_category',
                'with_front' => false,
            ),
        )
    );
}

add_action('init', 'mp_dd_register_event_taxonomy');

function mp_dd_add_timeline_event_metaboxes()
{
//    remove_meta_box('pageparentdiv', 'timeline_event', 'side');
    add_meta_box('mp_dd_timeline_event_date', 'Date', 'mp_dd_timeline_event_date', 'timeline_event', 'side', 'default');
    add_meta_box('mp_dd_timeline_event_links', 'Links', 'mp_dd_timeline_event_links', 'timeline_event', 'side', 'default');
}

add_action('add_meta_boxes', 'mp_dd_add_timeline_event_metaboxes');

function mp_dd_timeline_event_date()
{
    global $post;
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Start Date</th>
            <td><input type="date" name="start_date" value="<?php echo get_post_meta($post->ID, 'start_date', true); ?>" title="Start Date" required></td>
        </tr>
        <tr valign="top">
            <th scope="row">End Date</th>
            <td><input type="date" name="end_date" value="<?php echo get_post_meta($post->ID, 'end_date', true); ?>" title="End Date"></td>
        </tr>
    </table>
    <?php
}

function mp_dd_timeline_event_links()
{
    global $post;
    $used_links      = get_post_meta($post->ID, 'links', true);
    $used_links      = is_array($used_links) ? $used_links : array();
    $available_links = array_diff(mp_dd_get_available_tags(false), $used_links);
    $links_string    = '';
    foreach ($used_links as $id => $link) {
        $links_string .= $id . ';' . $link;
    }
    rtrim($links_string, ",")
    ?>
    <div>
        <div>
            <div>
                <select id="new-link" name="new_link" class="form-input-tip" title="New Link">
                    <option value="-1">[select]</option>
                    <?php foreach ($available_links as $id => $link): ?>
                        <option value="<?= $id . ';' . $link ?>"><?= $link ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="button" id="new-link-submit" onclick="document.getElementById('post').submit();" class="button" value="Add">
            </div>
            <div id="aliases_list" class="tagchecklist">
                <?php
                foreach ($used_links as $id => $link) {
                    ?><span id="alias_id_<?= $id ?>"><a id="dd_tag-check-num-<?= $id ?>" class="ntdelbutton" tabindex="<?= $id ?>" onclick="remove_alias(<?= $id ?>)">X</a>&nbsp;<?= $link ?></span><?php
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        function remove_alias(id) {
            var form = document.getElementById("post");
            var remove_field = document.createElement('input');
            remove_field.setAttribute("type", "hidden");
            remove_field.setAttribute("name", "remove_link");
            remove_field.setAttribute("value", id);
            form.appendChild(remove_field);
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
function mp_dd_save_timeline_event_meta($post_id, $post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return $post_id;
    }
    if (isset($_POST['start_date'])) {
        update_post_meta($post->ID, 'start_date', $_POST['start_date']);
    }
    if (isset($_POST['end_date'])) {
        update_post_meta($post->ID, 'end_date', $_POST['end_date']);
    }
    if (isset($_POST['new_link']) && substr($_POST['new_link'], 0, 2) != -1) {
        $links            = get_post_meta($post->ID, 'links', true);
        $tags             = mp_dd_get_available_tags(false);
        $parts            = explode(';', $_POST['new_link']);
        $links[$parts[0]] = stripslashes($parts[1]);
        $links            = array_intersect($links, $tags);
        update_post_meta($post->ID, 'links', $links);
    }
    if (isset($_POST['remove_link'])) {
        $links = get_post_meta($post->ID, 'links', true);
        unset($links[$_POST['remove_link']]);
        update_post_meta($post->ID, 'links', $links);
    }
    return $post_id;
}

add_action('save_post', 'mp_dd_save_timeline_event_meta', 1, 2);

function smp_dd_custom_timeline_event_columns($column_headers)
{
    unset($column_headers['author']);
    unset($column_headers['comments']);
    unset($column_headers['date']);
    $column_headers['timeline_event_start_date'] = __('Start Date');
    $column_headers['timeline_event_end_date']   = __('End Date');
    $column_headers['timeline_event_links']      = __('Links');
    return $column_headers;
}

add_action('manage_timeline_event_posts_columns', 'smp_dd_custom_timeline_event_columns');

function smp_dd_custom_timeline_event_sortable_columns($columns)
{
    $columns['timeline_event_start_date'] = 'timeline_event_start_date';
    $columns['timeline_event_end_date']   = 'timeline_event_end_date';
    return $columns;
}

add_action('manage_edit-timeline_event_sortable_columns', 'smp_dd_custom_timeline_event_sortable_columns');

function mp_dd_sort_timeline_events_on_date($vars)
{
    if (!isset($vars['post_type']) || $vars['post_type'] != 'timeline_event') {
        return $vars;
    }
    if (strpos($_SERVER['REQUEST_URI'], 'orderby=') === false) {
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => 'start_date',
                'orderby'  => 'meta_value_num',
                'order'  => 'DESC',
            )
        );
    }
    if ($vars['orderby'] == 'timeline_event_start_date') {
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => 'start_date',
                'orderby'  => 'meta_value_num',
            )
        );
    }
    if ($vars['orderby'] == 'timeline_event_end_date') {
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => 'end_date',
                'orderby'  => 'meta_value_num',
            )
        );
    }
    return $vars;
}

add_filter('request', 'mp_dd_sort_timeline_events_on_date');