<?php
/**
 * Plugin Name: MP D&D
 * Plugin URI: http://moridrin.com/mp-dd
 * Description: With MP D&D you have lots of functionality to keep track of your D&D world.
 * Version: 1.0.2
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

require_once 'mp-general/general.php';
require_once 'models/TimelineEvent.php';
require_once 'custom-types/timeline-event-post-type.php';
require_once 'custom-types/timeline-event-content.php';
require_once 'custom-types/dd-object-post-type.php';
require_once 'custom-types/dd-object-content.php';

function mp_dd_register_mp_dd()
{
    /* Database */
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $table_name      = $wpdb->prefix . "mp_dd_object_aliases";
    $sql
                     = "
		CREATE TABLE $table_name (
			alias VARCHAR(50) NOT NULL,
			post_id BIGINT(20),
			UNIQUE KEY id (alias)
		) $charset_collate;";
    $wpdb->query($sql);

    /* Options */
//    update_option('mp_dd_event_guest_registration', 'false');
}

register_activation_hook(__FILE__, 'mp_dd_register_mp_dd');

function mp_dd_unregister_mp_dd()
{
    //Nothing to do here.
}

register_deactivation_hook(__FILE__, 'mp_dd_unregister_mp_dd');

function mp_dd_uninstall_mp_dd()
{
//    global $wpdb;
//    $table_name = $wpdb->prefix . "mp_dd_timeline";
//    $sql = "DROP TABLE IF_EXISTS $table_name;";
//    $wpdb->query($sql);
//    $table_name = $wpdb->prefix . "mp_dd_timeline_tags";
//    $sql = "DROP TABLE IF_EXISTS $table_name;";
//    $wpdb->query($sql);
}

register_uninstall_hook(__FILE__, 'mp_dd_uninstall_mp_dd');

function mp_dd_timeline_event_template($archive_template)
{
    if (is_post_type_archive('event')) {
        $archive_template = dirname(__FILE__) . '/archive-event.php';
    }
    return $archive_template;
}

add_filter('archive_template', 'mp_dd_timeline_event_template');

function mp_dd_save_timeline_event(
    $post_ID,
    $post_after,
    /** @noinspection PhpUnusedParameterInspection */
    $post_before
) {
    if (get_post_type() != 'event') {
        return $post_ID;
    }
    $event = new TimelineEvent($post_after);
    if (!$event->isValid() && $event->isPublished()) {
        $updateArguments                = array();
        $updateArguments['ID']          = $post_ID;
        $updateArguments['post_status'] = 'draft';
        wp_update_post($updateArguments);
        update_option('mp_dd_is_publish_error', true);
    }
    return $post_ID;
}

add_action('save_post', 'mp_dd_save_timeline_event', 10, 3);

function mp_dd_timeline_events_admin_notice()
{
    $screen = get_current_screen();
    if ('events' != $screen->post_type || 'post' != $screen->base) {
        return;
    }
    $publish_error = get_option('mp_dd_is_publish_error', true);
    $save_notice   = get_option('mp_dd_is_save_warning', true);
    if ($publish_error || $save_notice) {
        ?>
        <div class="notice notice-error">
            <p><?php _e('You cannot publish a timeline event without a start date!', 'mp_dd'); ?></p>
        </div>
        <?php
    }
    update_option('mp_dd_is_publish_error', false);
    update_option('mp_dd_is_save_warning', false);
}

add_action('admin_notices', 'mp_dd_timeline_events_admin_notice');

function mp_dd_timeline_events_updated_messages($messages)
{
    global $post, $post_ID;
    $publish_error = get_option('mp_dd_is_publish_error', true);
    if ($publish_error) {
        /** @noinspection HtmlUnknownTarget */
        $messages['events'] = array(
            0  => '',
            1  => sprintf(__('Timeline Event updated. <a href="%s">View Event</a>'), esc_url(get_permalink($post_ID))),
            2  => __('Custom field updated.'),
            3  => __('Custom field deleted.'),
            4  => __('Timeline Event updated.'),
            /* translators: %s: date and time of the revision */
            5  => isset($_GET['revision']) ? sprintf(
                __('Timeline Event restored to revision from %s'),
                wp_post_revision_title((int)$_GET['revision'], false)
            ) : false,
            6  => '', //Send a blank string to prevent it from posting that it has been published correctly.
            7  => __('Timeline Event saved.'),
            8  => sprintf(
                __('Timeline Event submitted. <a target="_blank" href="%s">Preview event</a>'),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
            9  => sprintf(
                __('Timeline Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)),
                esc_url(get_permalink($post_ID))
            ),
            10 => sprintf(
                __('Timeline Event draft updated. <a target="_blank" href="%s">Preview event</a>'),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
        );
    } else {
        /** @noinspection HtmlUnknownTarget */
        $messages['events'] = array(
            0  => '',
            1  => sprintf(__('Timeline Event updated. <a href="%s">View Event</a>'), esc_url(get_permalink($post_ID))),
            2  => __('Custom field updated.'),
            3  => __('Custom field deleted.'),
            4  => __('Event updated.'),
            /* translators: %s: date and time of the revision */
            5  => isset($_GET['revision']) ? sprintf(
                __('Event restored to revision from %s'),
                wp_post_revision_title((int)$_GET['revision'], false)
            ) : false,
            6  => sprintf(__('Timeline Event published. <a href="%s">View event</a>'), esc_url(get_permalink($post_ID))),
            7  => __('Event saved.'),
            8  => sprintf(
                __('Timeline Event submitted. <a target="_blank" href="%s">Preview event</a>'),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
            9  => sprintf(
                __('Timeline Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)),
                esc_url(get_permalink($post_ID))
            ),
            10 => sprintf(
                __('Timeline Event draft updated. <a target="_blank" href="%s">Preview event</a>'),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
        );
    }

    return $messages;
}

add_filter('post_updated_messages', 'mp_dd_timeline_events_updated_messages');

function mp_dd_custom_column_values($column, $post_id)
{
    switch ($column) {
        case 'timeline_event_start_date':
            echo get_post_meta($post_id, 'start_date', true);
            break;

        case 'timeline_event_end_date':
            echo get_post_meta($post_id, 'end_date', true);
            break;

        case 'timeline_event_links':
            foreach (get_post_meta($post_id, 'links')[0] as $id => $link) {
                echo '<a href="' . get_post_permalink($id) . '">' . $link . '</a>,<br/>';
            }
            break;
    }
}

add_action('manage_timeline_event_posts_custom_column', 'mp_dd_custom_column_values', 10, 2);
