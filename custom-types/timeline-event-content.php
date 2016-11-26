<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-11-16
 * Time: 9:32
 */
function mp_dd_add_timeline_event_content($content)
{
    global $post;
    if ($post->post_type != 'timeline_event') {
        return $content;
    }
    $links = get_post_meta($post->ID, 'links', true);
    if (empty($links)) {
        return $content;
    }

    if (strpos($content, '<h1>') === false) {
        $content = '<h1>About</h1>' . $content;
    }

    $content .= '<h1>Links</h1>';
    $content .= '<ul>';
    foreach ($links as $id => $link) {
        $post = get_post($id);
        ob_start();
        ?>
        <li>
            <a href="<?= get_post_permalink($post->ID) ?>"><?= $post->post_title ?></a>
        </li>
        <?php
        $content .= ob_get_clean();
    }
    $content .= '</ul>';

    return $content;
}

add_filter('the_content', 'mp_dd_add_timeline_event_content');