<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-11-16
 * Time: 9:32
 */
function mp_dd_add_dd_object_content($content)
{
    global $post;
    if ($post->post_type != 'dd_object') {
        return $content;
    }

    if (strpos($content, '<h1>') === false) {
        $content = '<h1>About</h1>' . $content;
    }
    $content .= '<h1>Links</h1>';

    #region Parent
    if (get_post_meta($post->ID, 'display_parent', true) == 'yes' && get_post(wp_get_post_parent_id($post->ID)) !== false) {
        $parent      = get_post(wp_get_post_parent_id($post->ID));
        $url         = get_post_permalink($parent->ID);
        $header_text = get_post_meta($post->ID, 'display_parent_header', true);
        if (empty($header_text)) {
            $content .= '<h2><a href="' . $url . '">' . $parent->post_title . '</a></h2>';
        } else {
            $content .= '<h2>' . $header_text . ': <a href="' . $url . '">' . $parent->post_title . '</a></h2>';
        }
    }
    #endregion

    #region Children
    if (get_post_meta($post->ID, 'display_children', true) == 'yes') {
        $args     = array(
            'post_parent' => $post->ID,
            'post_type'   => 'any',
            'numberposts' => -1,
            'post_status' => 'any',
        );
        $children = get_children($args);
        if (!empty($children)) {
            $content .= '<h2>' . get_post_meta($post->ID, 'display_children_header', true) . '</h2>';
            $content .= '<ul>';
            foreach ($children as $child) {
                /** @var WP_Post $child */
                ob_start();
                ?>
                <li>
                    <a href="<?= get_post_permalink($child->ID) ?>"><?= $child->post_title ?></a>
                </li>
                <?php
                $content .= ob_get_clean();
            }
        }
    }
    #endregion

    #region History
    $events = TimelineEvent::get_all_for_post($post);

    $content .= '<h2>History</h2>';
    $content .= '<ul>';
    foreach ($events as $event) {
        /** @var TimelineEvent $event */
        ob_start();
        ?>
        <li>
            <?= $event->getStartDate()->format('Y-m-d') ?> - <a href="<?= get_post_permalink($event->getPostId()) ?>"><?= $event->post->post_title ?></a>
        </li>
        <?php
        $content .= ob_get_clean();
    }
    $content .= '</ul>';
    #endregion

    return $content;
}

add_filter('the_content', 'mp_dd_add_dd_object_content', 9);
