<?php
/**
 * Plugin Name: MP D&D
 * Plugin URI: http://moridrin.com/mp-dd
 * Description: With MP D&D you have lots of functionality to keep track of your D&D world.
 * Version: 1.0.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

namespace mp_dd;

use mp_dd\models\CombatAction;

if (!defined('ABSPATH')) {
    exit;
}

define('MP_DD_PATH', plugin_dir_path(__FILE__));
define('MP_DD_URL', plugins_url() . '/' . plugin_basename(__DIR__));

require_once 'general/general.php';
require_once 'Ajax.php';
require_once 'MP_DD.php';
require_once 'Options.php';
require_once 'Revisions/R1.php';
require_once 'post-type/Npc/Admin.php';
require_once 'post-type/Area/Admin.php';
require_once 'post-type/Object/Admin.php';
require_once 'post-type/Encounter/Admin.php';
require_once 'post-type/Encounter/Frontend.php';
require_once 'post-type/Location.php';
require_once 'models/Creature.php';
require_once 'models/CombatAction.php';
require_once 'models/Player.php';
require_once 'models/Monster.php';
require_once 'models/CombatMonster.php';
require_once 'shortcodes/DNDBeyond.php';
require_once 'shortcodes/Npc.php';
require_once 'shortcodes/Product.php';
require_once 'shortcodes/Calendar.php';
require_once 'shortcodes/MoridrinMaps.php';
require_once 'shortcodes/DndbCharacters.php';
require_once 'shortcodes/TimeLine.php';

// TODO Move
function mp_filter_atra_from_log(CombatAction $action, array $creatures)
{
    $actorIsAtra = $creatures[$action->getActor()]->getName() === 'Atra';
    $currentUserIsAtra = current_user_can('atra') || current_user_can('administrator');
    if ($actorIsAtra && !$currentUserIsAtra) {
        return false;
    }
    return true;
}

add_filter('dd_encounters_player_is_allowed_to_view_log', 'mp_filter_atra_from_log', 10, 2);

function mp_filter_publish_date($the_date, $d, \WP_Post $post)
{
    $tmp = new \DateTime($the_date);
    $tmp->add(new \DateInterval('P1100Y'));
    if ('' == $d) {
        $d = get_option('date_format');
    }
    return $tmp->format($d);
}

add_filter('get_the_date', 'mp_filter_publish_date', 10, 3);
