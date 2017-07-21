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
    wp_enqueue_script('interact', MP_DD::URL . '/include/interact.js', ['jquery']);
    wp_enqueue_script('chosen', MP_DD::URL . '/include/chosen/chosen.jquery.js', array('jquery'));
    wp_enqueue_script('chosen_init', MP_DD::URL . '/include/chosen/init-chosen.js', array('jquery'));
    wp_enqueue_style('chosen', MP_DD::URL . '/include/chosen/chosen.css');

    wp_enqueue_script('mp_dd_map_image_selector', MP_DD::URL . '/js/mp-dd-map-image-selector.js', ['jquery']);
    wp_enqueue_script('mp_dd_draggable', MP_DD::URL . '/js/mp-dd-draggable.js', ['jquery']);
    wp_enqueue_script('mp_dd_buildings', MP_DD::URL . '/js/mp-dd-buildings.js', ['jquery']);
    wp_enqueue_script('mp_dd_npcs', MP_DD::URL . '/js/mp-dd-npcs.js', ['jquery']);
    wp_enqueue_style('mp_dd_style', MP_DD::URL . '/css/mp-dd.css');
}

add_action('admin_enqueue_scripts', 'mp_dd_enquire_admin_scripts', 12);

function mp_dd_enquire_scripts()
{
    wp_enqueue_style('mp_dd_style', MP_DD::URL . '/css/mp-dd.css');
}

add_action('wp_enqueue_scripts', 'mp_dd_enquire_scripts');

if (!function_exists('mp_edit_form_after_title')) {
    function mp_edit_form_after_title()
    {
        global $post;
        do_meta_boxes(get_current_screen(), 'after_title', $post);
    }

    add_action('edit_form_after_title', 'mp_edit_form_after_title');
}

#region Update Settings Message.
function mp_ssv_events_update_settings_notification()
{
    $lastCityRemoved = get_option(MP_DD::OPTION_LAST_CITY_REMOVED);
    if ($lastCityRemoved !== false) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>Do you also want to remove all buildings, maps and NPCs linked to this city?</p>
            <p><a href="/wp-admin/admin.php?page=mp_dd_settings&tab=general&city=<?= $lastCityRemoved ?>">Set Now</a></p>
        </div>
        <?php
        delete_option(MP_DD::OPTION_LAST_CITY_REMOVED);
    }
}

add_action('admin_notices', 'mp_ssv_events_update_settings_notification');
#endregion

function mp_dd_post_deleted($post_id)
{
    update_option(MP_DD::OPTION_LAST_CITY_REMOVED, $post_id);
}

add_action('delete_post', 'mp_dd_post_deleted', 10);

#region Functions that should be in PHP
if (!function_exists('mp_var_export')) {
    /**
     * This function is for development purposes only and lets the developer print a variable in the PHP formatting to inspect what the variable is set to.
     *
     * @param mixed $variable any variable that you want to be printed.
     * @param bool $die set true if you want to call die() after the print. $die is ignored if $return is true.
     * @param bool $highlight set false if you don't want it to highlight.
     * @param mixed $unique when set it only runs the function if there hasn't been a mp_var_export call with this same $unique value.
     *
     * @return mixed|null|string returns the print in string if $return is true, returns null if $return is false, and doesn't return if $die is true.
     */
    function mp_var_export($variable, $die = false, $highlight = true, $unique = false)
    {
        if (isset($GLOBALS['mp_var_export_ignore']) && $GLOBALS['mp_var_export_ignore']) {
            return;
        }
        if ($unique) {
            if (!isset($GLOBALS['mp_var_export_unique'])) {
                $GLOBALS['mp_var_export_unique'] = [];
            }
            if (in_array($unique, $GLOBALS['mp_var_export_unique'])) {
                return;
            }
            $GLOBALS['mp_var_export_unique'][] = $unique;
        }
        if ($variable instanceof DOMElement || $variable instanceof DOMText) {
            $variable = $variable->ownerDocument->saveHTML($variable);
        }
        if ($variable instanceof simple_html_dom || $variable instanceof simple_html_dom_node) {
            $variable = (string) $variable;
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