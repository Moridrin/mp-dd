<?php

namespace mp_dd\shortcodes;

use mp_dd\Options;
use mp_general\base\BaseFunctions;

abstract class MoridrinMaps
{
    public static function mmap($attributes, $innerHtml)
    {
        if (!is_array($attributes)) {
            $attributes = [];
        }
        if (isset($attributes['x']) && isset($attributes['y'])) {
            $attributes['url'] = 'http://maps.moridrin.com?x=' . $attributes['x'] . '&y=' . $attributes['y'];
            if (isset($attributes['z'])) {
                $attributes['url'] .= '&z=' . $attributes['z'];
            }
        }
        if (!isset($attributes['url'])) {
            return $innerHtml;
        }
        ob_start();
        ?><a href="<?= BaseFunctions::escape($attributes['url'], 'attr') ?>" target="_blank"><?= $innerHtml ?></a><?php
        return ob_get_clean();
    }

    public static function filterAllTags($content)
    {
        do_shortcode($content);
    }

    public static function duplicatePrefixes($content)
    {
        $pattern = get_shortcode_regex(['mmap']);

        if (preg_match_all('/' . $pattern . '/s', $content, $matches)
            && array_key_exists(2, $matches)
            && in_array('mmap', $matches[2])) {
            for ($i = 0; $i < count($matches[5]); ++$i) {
                $tmpString = '[#####' . uniqid() . '#####]';
                $content   = str_replace($matches[0][$i], $tmpString, $content);
                $content   = str_replace($matches[5][$i], $matches[0][$i], $content);
                $content   = str_replace($tmpString, $matches[0][$i], $content);
            }
        }
        return $content;
    }
}

add_shortcode('mmap', [MoridrinMaps::class, 'mmap']);
// if (get_option(Options::OPTIONS['general']['match_similar']['id'])) {
    add_filter('the_content', [MoridrinMaps::class, 'duplicatePrefixes']);
// }
