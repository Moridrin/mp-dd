<?php

namespace mp_dd\shortcodes;

use mp_dd\Options;
use mp_general\base\BaseFunctions;

abstract class Npc
{
    /**
     * @param $attributes
     * @param $innerHtml
     * @return false|string
     * @throws \Exception
     */
    public static function npc($attributes, $innerHtml)
    {
        if (!is_array($attributes) || !isset($attributes['id'])) {
            throw new \Exception('The ID attribute needs to be set.');
        }
        $post = get_post($attributes['id']);
        $attributes += [
            'display' => 'full',
        ];
        ob_start();
        switch ($attributes['display']) {
            case 'modal':
                ?>
                <a href="#modal_<?= $post->ID ?>" class="modal-trigger"><?= $post->post_title ?></a>
                <div id="modal_<?= $post->ID ?>" class="modal">
                    <div class="modal-content">
                        <?= apply_filters('the_content', $post->post_content) ?>
                    </div>
                </div>
                <?php
                break;
            case 'li':
                ?>
                <div class="collapsible-header"><?= $post->post_title ?></div>
                <div class="collapsible-body"><?= apply_filters('the_content', $post->post_content) ?></div>
                <?php
                break;
            default:
                echo '<h2>' . BaseFunctions::escape($post->post_title, 'html') . '</h2>';
                echo apply_filters('the_content', $post->post_content);
                break;
        }
        return ob_get_clean();
    }
}

add_shortcode('npc', [Npc::class, 'npc']);
