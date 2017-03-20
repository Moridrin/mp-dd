<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 1-3-17
 * Time: 14:45
 */

function mp_dd_custom_race_content($content)
{
    global $post;
    if ($post->post_type != 'race' && $post->post_type != 'class' && $post->post_type != 'background') {
        return $content;
    }
    $race = PropertyGroup::load($post->ID);
    return $content . $race->getPropertiesHTML();
}

add_filter('the_content', 'mp_dd_custom_race_content');
