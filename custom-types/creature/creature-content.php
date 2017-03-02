<?php
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 1-3-17
 * Time: 14:45
 */

function mp_dd_custom_creature_content($content)
{
    global $post;
    if ($post->post_type != 'creature') {
        return $content;
    }
    $creature = Creature::load($post->ID);
    ob_start();
    ?>
    <?php
    $content = ob_get_clean();
    return $content;
}

add_filter('the_content', 'mp_dd_custom_creature_content');
