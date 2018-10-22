<?php

namespace mp_dd\shortcodes;

use mp_dd\Options;
use mp_general\base\BaseFunctions;

abstract class DndbCharacters
{
    public static function dndbCharacter($attributes, $innerHtml)
    {
        if (!is_array($attributes)) {
            return $innerHtml;
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

add_shortcode('dndb-character', [DndbCharacters::class, 'dndbCharacter']);
add_shortcode('dndb-character', [DndbCharacters::class, 'dndbCharacter']);
// if (get_option(Options::OPTIONS['general']['match_similar']['id'])) {
    add_filter('the_content', [DndbCharacters::class, 'duplicatePrefixes']);
// }
