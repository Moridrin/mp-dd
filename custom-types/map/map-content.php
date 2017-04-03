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
    $map = Map::load($post->ID);
    return $map->getHTML($content);
}

add_filter('the_content', 'mp_dd_custom_map_content');
