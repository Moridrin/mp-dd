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
    if (!mp_dd_starts_with($admin_referer, 'mp_dd__')) {
        return;
    }
    MP_DD::resetOptions();
}

add_filter(MP_DD::HOOK_RESET_OPTIONS, 'mp_dd_reset_options');
#endregion

#region Functions that should be in PHP
/**
 * @param string $string
 * @param bool   $capitalizeFirstCharacter
 *
 * @return string
 */
function mp_dd_to_camel_case($string, $capitalizeFirstCharacter = false)
{
    $string    = str_replace(' ', '', mp_dd_to_title($string));

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
function mp_dd_to_title($string)
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
function mp_dd_to_snake_case($string)
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
function mp_dd_to_value($string)
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
function mp_dd_replace_at_pos($haystack, $needle, $replacement, $position)
{
    return substr_replace($haystack, $replacement, $position, strlen($needle));
}

/**
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function mp_dd_starts_with($haystack, $needle)
{
    return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

/**
 * @param $haystack
 * @param $needle
 *
 * @return bool
 */
function mp_dd_ends_with($haystack, $needle)
{
    return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}
#endregion