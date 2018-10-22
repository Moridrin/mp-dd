<?php

namespace mp_dd\shortcodes;

use mp_dd\Options;
use mp_general\base\BaseFunctions;

abstract class Product
{
    /**
     * @param $attributes
     * @param $innerHtml
     * @return false|string
     * @throws \Exception
     */
    public static function product($attributes, $innerHtml)
    {
        if (!is_array($attributes) || !isset($attributes['id'])) {
            throw new \Exception('The ID attribute needs to be set.');
        }
        $post = get_post($attributes['id']);
        ob_start();
        $imageUrl = get_the_post_thumbnail_url($post, 'full-size');
        if (!empty($post->post_content)) {
            ?>
            <a href="#modal_<?= $post->ID ?>" class="modal-trigger"><?= $post->post_title ?></a>
            <div id="modal_<?= $post->ID ?>" class="modal">
                <div class="modal-content">
                    <?php
                    if ($imageUrl !== false) {
                        list($width, $height, $type, $attr) = getimagesize($imageUrl);
                        if ($width > $height) {
                            ?>
                            <div class="parallax-container">
                                <img id="img_<?= $post->ID ?>" src="<?= $imageUrl ?>" class="materialboxed" width="100%"/>
                            </div>
                            <?php
                            echo apply_filters('the_content', $post->post_content);
                        } else {
                            ?>
                            <div class="row">
                                <div class="col s12 m8">
                                    <?= apply_filters('the_content', $post->post_content) ?>
                                </div>
                                <div class="col s12 m4">
                                    <img id="img_<?= $post->ID ?>" src="<?= $imageUrl ?>" class="materialboxed" width="100%"/>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo apply_filters('the_content', $post->post_content);
                    }
                    ?>
                </div>
            </div>
            <?php
        } elseif ($imageUrl !== false) {
            ?>
            <a href="javascript:void()" data-img-id="img_<?= $post->ID ?>" class="materialboxed-trigger"><?= $post->post_title ?></a>
            <img id="img_<?= $post->ID ?>" src="<?= $imageUrl ?>" class="materialboxed" style="display: none; position: absolute;" width="1px;"/>
            <?php
        } else {
            ?><span><?= $post->post_title ?></span><?php
        }
        return ob_get_clean();
    }
}

add_shortcode('product', [Product::class, 'product']);
