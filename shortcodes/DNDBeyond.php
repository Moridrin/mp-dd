<?php

namespace mp_dd\shortcodes;

use mp_dd\Options;
use mp_general\base\BaseFunctions;

abstract class DNDBeyond
{
    public static function dndb($attributes, $innerHtml)
    {
        if (!is_array($attributes)) {
            $attributes = [];
        }
        $attributes += [
            'tag' => BaseFunctions::toDashCase($innerHtml),
            'url' => null,
        ];
        if ($attributes['url'] !== null) {
            $url = $attributes['url'];
        } else {
            $url = 'https://www.dndbeyond.com/monsters/' . BaseFunctions::escape($attributes['tag'], 'attr');
        }
        ob_start();
        ?><a href="<?= $url ?>" target="_blank"><?= $innerHtml ?></a><?php
        return ob_get_clean();
    }

    public static function filterAllTags($content)
    {
        do_shortcode($content);
    }

    public static function duplicatePrefixes($content)
    {
        $pattern = get_shortcode_regex(['dndb']);
        if (
            preg_match_all('/' . $pattern . '/s', $content, $matches)
            && array_key_exists(2, $matches)
            && in_array('dndb', $matches[2])
        ) {
            for ($i = 0; $i < count($matches[5]); ++$i) {
                preg_match_all('/ (.*?)=["|\'](.*?)["|\']/', $matches[3][$i], $tags);
                $tags            = array_combine($tags[1], $tags[2]);
                $tags            += ['tag' => $matches[5][$i]];
                $tmpString       = '[#####' . uniqid() . '#####]';
                $tmpStringPlural = '[#####' . uniqid() . '#####]';
                $content         = str_ireplace($matches[0][$i], $tmpString, $content);
                $content         = str_ireplace($matches[5][$i] . 's', $tmpStringPlural, $content);
                $content         = str_ireplace($matches[5][$i], $matches[0][$i], $content);
                $content         = str_ireplace($tmpString, $matches[0][$i], $content);
                $content         = str_ireplace($tmpStringPlural, '[' . $matches[2][$i] . $matches[3][$i] . ' plural="true"]' . $matches[5][$i] . 's[/' . $matches[2][$i] . ']', $content);
            }
        }
        return $content;
    }
}

add_shortcode('dndb', [DNDBeyond::class, 'dndb']);
if (get_option(Options::OPTIONS['general']['match_similar']['id'])) {
    add_filter('the_content', [DNDBeyond::class, 'duplicatePrefixes']);
}
