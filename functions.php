<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 8:27
 */

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
 * This function is for development purposes only and lets the developer print a variable in the PHP formatting to inspect what the variable is set to.
 *
 * @param mixed $variable any variable that you want to be printed.
 * @param bool  $die      set true if you want to call die() after the print. $die is ignored if $return is true.
 * @param bool  $return   set true if you want to return the print as string.
 * @param bool  $newline  set false if you don't want to print a newline at the end of the print.
 *
 * @return mixed|null|string returns the print in string if $return is true, returns null if $return is false, and doesn't return if $die is true.
 */
function mp_dd_var_export($variable, $die = false, $return = false, $newline = true)
{
    if (mp_dd_has_circular_reference($variable)) {
        ob_start();
        var_dump($variable);
        $var_dump = ob_get_clean();
        $print    = highlight_string("<?php " . $var_dump, true);
    } else {
        $print = highlight_string("<?php " . var_export($variable, true), true);
    }
    $print = trim($print);
    $print = preg_replace("|^\\<code\\>\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>|", "", $print, 1);  // remove prefix
    $print = preg_replace("|\\</code\\>\$|", "", $print, 1);
    $print = trim($print);
    $print = preg_replace("|\\</span\\>\$|", "", $print, 1);
    $print = trim($print);
    $print = preg_replace("|^(\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>)(&lt;\\?php&nbsp;)(.*?)(\\</span\\>)|", "\$1\$3\$4", $print);
    $print .= ';';
    if ($return) {
        return $print;
    } else {
        echo $print;
        if ($newline) {
            echo '<br/>';
        }
    }

    if ($die) {
        die();
    }
    return null;
}

/**
 * This function checks if the given $variable is recursive.
 *
 * @param mixed $variable is the variable to be checked.
 *
 * @return bool true if the $variable contains circular reference.
 */
function mp_dd_has_circular_reference($variable)
{
    $dump = print_r($variable, true);
    if (strpos($dump, '*RECURSION*') !== false) {
        return true;
    } else {
        return false;
    }
}

function mp_dd_sanitize($value)
{
    if (is_array($value)) {
        return $value;
    }
    $value = stripslashes($value);
    $value = esc_attr($value);
    $value = sanitize_text_field($value);
    return $value;
}

function mp_dd_get_available_tags($include_aliases = false)
{
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $used_tags     = array();
    $existing_tags = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'creature' AND post_status = 'publish'");
    foreach ($existing_tags as $alias) {
        $used_tags[$alias->ID] = strtolower($alias->post_title);
        if ($include_aliases) {
            foreach (mp_dd_get_aliases_for_post($alias->ID) as $item) {
                $used_tags[$alias->ID . '_alias_' . $item] = $item;
            }
        }
    }
    return $used_tags;
}

function mp_dd_get_aliases_for_post($post_id)
{
    global $wpdb;
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $used_tags     = array();
    $table_name    = $wpdb->prefix . "mp_creature_aliases";
    $existing_tags = $wpdb->get_results("SELECT alias FROM {$table_name} WHERE post_id = {$post_id}");
    foreach ($existing_tags as $alias) {
        $used_tags[] = strtolower($alias->alias);
    }
    return $used_tags;
}
