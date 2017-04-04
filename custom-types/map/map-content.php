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
    return $content;
}

add_filter('the_content', 'mp_dd_custom_map_content');
