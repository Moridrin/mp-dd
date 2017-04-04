<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 1-3-17
 * Time: 14:45
 */

function mp_dd_custom_map_content($content)
{
    global $post;
    if ($post->post_type != 'map') {
        return $content;
    }
    ob_start();
    echo get_post_meta($post->ID, 'map', true);
    echo get_post_meta($post->ID, 'info', true);
    echo get_post_meta($post->ID, 'rooms', true);
    return ob_get_clean() . $content;
}

add_filter('the_content', 'mp_dd_custom_map_content');
