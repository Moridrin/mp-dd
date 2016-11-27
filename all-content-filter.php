<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-11-16
 * Time: 11:37
 */

function mp_dd_add_links($content)
{

    $tags = mp_dd_sort_on_containing_string(mp_dd_get_available_tags(true));
//    ssv_print($tags, true);
//    ssv_print($content, true);
    foreach ($tags as $id => $tag) {
        if (stripos($id, '_alias_' . $tag) !== false) {
            $id = str_replace('_alias_' . $tag, '', $id);
        }
        $tag          = esc_html($tag);
        $tag          = str_replace('&#039;', '&#8217;', $tag); //Different codex for '
        $tag_position = stripos($content, $tag);
        while ($tag_position !== false) {
            $next_a_open_position      = stripos($content, '<a', $tag_position);
            $next_a_close_position     = stripos($content, '</a', $tag_position);
            $next_quote_close_position = stripos($content, '"', $tag_position);
            $next_field_close_position = stripos($content, '>', $tag_position);
            $next_field_open_position  = stripos($content, '<', $tag_position);
            $not_in_a_tag              = $next_a_close_position === false
                                         || ($next_a_open_position !== false && $next_a_open_position < $next_a_close_position);
            $not_in_tag_field          = $next_quote_close_position === false
                                         || $next_quote_close_position > $next_field_close_position
                                         || $next_quote_close_position > $next_field_open_position;
//            ssv_print($tag);
//            ssv_print($tag_position);
//            ssv_print($next_quote_close_position);
//            ssv_print($next_field_close_position);
//            $tmpa = $not_in_a_tag ? 'true' : 'false';
//            $tmpb = $not_in_tag_field ? 'true' : 'false';
//            ssv_print($tmpa . ' - ' . $tmpb);
            if ($not_in_a_tag && $not_in_tag_field) {
                $url     = get_post_permalink($id);
                $content = mp_replace_at_pos($content, $tag, '<a href="' . $url . '">' . substr($content, $tag_position, strlen($tag)) . '</a>', $tag_position);
            }
            $tag_position = stripos($content, $tag, $tag_position + strlen($tag));
        }
    }
    return $content;
}

add_filter('the_content', 'mp_dd_add_links', 10);

/**
 * This function is to make sure that you don't break a tag that is later in the array.
 * For example: Your array looks like ['world', 'hello world']. When you run it on the content
 * it will replace 'hello world' by 'hello <a>world</a>' instead of '<a>hello world</a>'
 *
 * @param $array
 *
 * @return array
 */
function mp_dd_sort_on_containing_string($array)
{
    $keys        = $array;
    $array       = array_values($array);
    $new_array   = array();
    $item_moved  = true;
    $translation = 0;
    while ($item_moved) {
        $item_moved = false;
        foreach ($array as $key_a => $a) {
            foreach (array_slice($array, $key_a + 1) as $key_b => $b) {
                if ($a !== $b && strpos($b, $a) !== false) {
                    $new_array[count($array) + $translation] = $a;
                    $translation++;
                    $item_moved = true;
                }
            }
            $new_array[$key_a] = $a;
        }
        ksort($new_array);
        $array     = array_reverse(array_unique(array_reverse($new_array)));
        $new_array = array();
    }
    $new_array = array();
    foreach ($array as $item) {
        $new_array[array_search($item, $keys)] = $item;
    }
    return $new_array;
}