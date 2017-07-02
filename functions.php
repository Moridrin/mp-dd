<?php
use mp_dd\MP_DD;

if (!defined('ABSPATH')) {
    exit;
}

#region Register
function mp_dd_register_plugin()
{
    MP_DD::resetOptions();
}

register_activation_hook(MP_DD_PATH . 'mp-dd.php', 'mp_dd_register_plugin');
#endregion

#region Unregister
function mp_dd_unregister()
{
    //Nothing to do
}

register_deactivation_hook(MP_DD_PATH . 'mp-dd.php', 'mp_dd_unregister');
#endregion

#region Reset Options
/**
 * This function will reset the events options if the admin referer originates from the SSV Events plugin.
 *
 * @param $admin_referer
 */
function mp_dd_reset_options($admin_referer)
{
    if (!mp_starts_with($admin_referer, 'mp_dd__')) {
        return;
    }
    MP_DD::resetOptions();
}

add_filter(MP_DD::HOOK_RESET_OPTIONS, 'mp_dd_reset_options');
#endregion

function mp_dd_enquire_admin_scripts()
{
    wp_enqueue_script('mp_dd_buildings', MP_DD::URL . '/js/mp-dd-buildings.js');
    wp_enqueue_script('mp_dd_npcs', MP_DD::URL . '/js/mp-dd-npcs.js');
}

add_action('admin_enqueue_scripts', 'mp_dd_enquire_admin_scripts', 12);

if (!function_exists('mp_edit_form_after_title')) {
    function mp_edit_form_after_title()
    {
        global $post;
        do_meta_boxes(get_current_screen(), 'after_title', $post);
    }

    add_action('edit_form_after_title', 'mp_edit_form_after_title');
}

//function mp_dd_post_deleted($post_id)
//{
//    wp_delete_post($post_id, true);
//}
//
//add_action('delete_post', 'mp_dd_post_deleted', 10);

#region Functions that should be in PHP
if (!function_exists('mp_var_export')) {
    /**
     * This function is for development purposes only and lets the developer print a variable in the PHP formatting to inspect what the variable is set to.
     *
     * @param mixed $variable  any variable that you want to be printed.
     * @param bool  $die       set true if you want to call die() after the print. $die is ignored if $return is true.
     * @param bool  $highlight set false if you don't want it to highlight.
     *
     * @return mixed|null|string returns the print in string if $return is true, returns null if $return is false, and doesn't return if $die is true.
     */
    function mp_var_export($variable, $die = false, $highlight = true)
    {
        if ($variable instanceof DOMElement || $variable instanceof DOMText) {
            $variable = $variable->ownerDocument->saveHTML($variable);
        }
        if (mp_has_circular_reference($variable)) {
            ob_start();
            var_dump($variable);
            $var_dump = ob_get_clean();
            if ($highlight) {
                $print = highlight_string("<?php " . $var_dump, true);
            } else {
                $print = $var_dump;
            }
        } else {
            if ($highlight) {
                $print = highlight_string("<?php " . var_export($variable, true), true);
            } else {
                $print = var_export($variable, true);
            }
        }
        $print = trim($print);
        $print = preg_replace("|^\\<code\\>\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>|", "", $print, 1);  // remove prefix
        $print = preg_replace("|\\</code\\>\$|", "", $print, 1);
        $print = trim($print);
        $print = preg_replace("|\\</span\\>\$|", "", $print, 1);
        $print = trim($print);
        $print = preg_replace("|^(\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>)(&lt;\\?php&nbsp;)(.*?)(\\</span\\>)|", "\$1\$3\$4", $print);
        $print .= ';';
        echo $print . '<br/>';

        if ($die) {
            if (is_string($die)) {
                die($die);
            } else {
                die();
            }
        }
        return null;
    }

    function mp_echo($variable, $die = false)
    {
        echo $variable;
        if ($die) {
            if (is_string($die)) {
                die($die);
            } else {
                die();
            }
        }
    }

    /**
     * @param string $string
     * @param bool   $capitalizeFirstCharacter
     *
     * @return string
     */
    function mp_to_camel_case($string, $capitalizeFirstCharacter = false)
    {
        $string = str_replace(' ', '', mp_to_title($string));

        if (!$capitalizeFirstCharacter) {
            $string[0] = strtolower($string[0]);
        }

        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    function mp_to_title($string)
    {
        $string = preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
        $string = str_replace('-', ' ', $string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    function mp_to_snake_case($string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    function mp_to_value($string)
    {
        $string = str_replace(' ', '_', $string);
        $string = strtolower($string);
        return $string;
    }

    /**
     * @param $haystack
     * @param $needle
     * @param $replacement
     * @param $position
     *
     * @return mixed
     */
    function mp_replace_at_pos($haystack, $needle, $replacement, $position)
    {
        return substr_replace($haystack, $replacement, $position, strlen($needle));
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    function mp_starts_with($haystack, $needle)
    {
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    function mp_ends_with($haystack, $needle)
    {
        return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * This function checks if the given $variable is recursive.
     *
     * @param mixed $variable is the variable to be checked.
     *
     * @return bool true if the $variable contains circular reference.
     */
    function mp_has_circular_reference($variable)
    {
        $dump = print_r($variable, true);
        if (strpos($dump, '*RECURSION*') !== false) {
            return true;
        } else {
            return false;
        }
    }
}
#endregion