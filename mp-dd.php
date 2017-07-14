<?php
/**
 * Plugin Name: MP D&D
 * Plugin URI: http://moridrin.com/mp-dd
 * Description: With MP D&D you have lots of functionality to keep track of your D&D world.
 * Version: 2.0.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

namespace mp_dd;

if (!defined('ABSPATH')) {
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('MP_DD_PATH', plugin_dir_path(__FILE__));
define('MP_DD_URL', plugins_url() . '/mp-dd/');

#region Require Once
require_once 'functions.php';

require_once 'include/JSLikeHTMLElement.php';

require_once 'models/City.php';

require_once 'custom-post-type/post-content-parser.php';
require_once 'custom-post-type/area.php';
require_once 'custom-post-type/map.php';
require_once 'custom-post-type/building.php';
require_once 'custom-post-type/npc.php';

require_once 'options/options.php';
#endregion

#region SSV_Users class
class MP_DD
{
    #region Constants
    const PATH = MP_DD_PATH;
    const URL = MP_DD_URL;

    const HOOK_RESET_OPTIONS = 'mp_dd__hook_reset_options';

    const OPTION_PUBLISH_ERROR = 'mp_dd__option_publish_error';
    const OPTION_LAST_CITY_REMOVED = 'mp_dd__option_last_city_removed';
    #endregion

    #region resetOptions()
    /**
     * This function sets all the options for this plugin back to their default value
     */
    public static function resetOptions()
    {
        // There currently aren't any options
    }

    #endregion

    public static function CLEAN_INSTALL()
    {
        mp_dd_unregister();
        mp_dd_register_plugin();
    }
}
#endregion
