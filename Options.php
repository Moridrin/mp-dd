<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 14-6-18
 * Time: 7:23
 */

namespace mp_dd;

use mp_dd\models\Monster;
use mp_dd\models\Player;
use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;

abstract class Options
{
    const OPTION_GROUP = 'mp_dd';

    const SECTIONS = [
        'general',
    ];

    const OPTIONS = [
        'general' => [
            'match_similar' => [
                'id'          => 'mp_dd__match_similar',
                'title'       => 'Match Similar',
                'description' => 'If you have a tag <code>[dndb]unicorn[/dndb]</code> it will match <b>all</b> places where it says unicorn in the current post.',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
        ],
    ];

    public static function registerSettings()
    {
        foreach (self::SECTIONS as $section) {
            add_settings_section('mp_dd_' . $section . '_section', ucfirst($section), null, 'mp_dd_' . $section);

            foreach (self::OPTIONS[$section] as $option) {
                $option += [
                    'description' => '',
                    'type'        => 'string',
                    'callback'    => 'showTextField',
                ];
                add_settings_field($option['id'], $option['title'], [BaseFunctions::class, $option['callback']], 'mp_dd_' . $section, 'mp_dd_' . $section . '_section', $option);
                register_setting(self::OPTION_GROUP . '_mp_dd_' . $section, $option['id'], ['type' => $option['type']]);
            }
        }
    }
    public static function setupNetworkMenu()
    {
        add_menu_page('Players', 'Players', 'edit_players', 'dd_encounters', [self::class, 'showPlayersList'], 'dashicons-feedback');
    }

    public static function setupSiteSpecificMenu()
    {
        add_menu_page('MP D&D', 'MP D&D', 'edit_posts', 'mp_dd_settings', '', 'dashicons-feedback');
        add_submenu_page('mp_dd_settings', 'MP D&D', 'MP D&D', 'edit_posts', 'mp_dd_settings', [self::class, 'showOptionsPage']);
        add_submenu_page('edit.php?post_type=encounter', 'Monsters', 'Monsters', 'edit_monsters', 'dd_monsters', [self::class, 'showMonstersList']);
        add_submenu_page('edit.php?post_type=encounter', 'Players', 'Players', 'edit_players', 'dd_players', [self::class, 'showPlayersList']);
        add_submenu_page('mp_dd_settings', 'Parser', 'Parser', 'edit_posts', 'mp_dd_parser', [Options::class, 'showParserOptionsPage']);
    }

    public static function showOptionsPage()
    {
        $activeTab = $_REQUEST['tab'] ?? self::OPTION_GROUP . '_' . self::SECTIONS[0];
        ?>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach (self::SECTIONS as $section) {
                ?><a href="?<?= BaseFunctions::escape(BaseFunctions::getCurrentUrlWithArguments(['tab' => self::OPTION_GROUP . '_' . $section]), 'attr') ?>" class="nav-tab <?= $activeTab === self::OPTION_GROUP . '_' . $section ? 'nav-tab-active' : ''; ?>"><?= BaseFunctions::toTitle($section) ?></a><?php
            }
            ?>
            <a href="https://moridrin.com/plugins/mp-dd" class="nav-tab">Help</a>
        </h2>
        <div class="wrap">
            <h1>Your Plugin Page Title</h1>
            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP . '_' . $activeTab);
                do_settings_sections($activeTab);
                submit_button();
                ?>
        </div>
        <?php
    }

    public static function showPlayersList()
    {
        ?>
        <div class="wrap">
            <?php
            if (BaseFunctions::isValidPOST(null)) {
                if ($_POST['action'] === 'delete-selected' && !isset($_POST['_inline_edit'])) {
                    Player::deleteByIds(BaseFunctions::sanitize($_POST['ids'], 'int'));
                } else {
                    $_SESSION['SSV']['errors'][] = 'Unknown action.';
                }
            }
            $orderBy = BaseFunctions::sanitize(isset($_GET['orderby']) ? $_GET['orderby'] : 'f_name', 'text');
            $order   = BaseFunctions::sanitize(isset($_GET['order']) ? $_GET['order'] : 'asc', 'text');
            $addNew  = '<a href="javascript:void(0)" class="page-title-action" onclick="playerManager.addNew(\'the-list\', \'\')">Add New</a>';
            ?>
            <h1 class="wp-heading-inline"><span>Players</span><?= current_user_can('edit_players') ? $addNew : '' ?></h1>
            <?php mp_ssv_show_table(Player::class, $orderBy, $order, current_user_can('edit_players')); ?>
        </div>
        <?php
    }

    public static function showMonstersList()
    {
        ?>
        <div class="wrap">
            <?php
            if (BaseFunctions::isValidPOST(null)) {
                if (!isset($_POST['action'])) {
                    SSV_Global::addError('No action provided.');
                } else {
                    $action      = BaseFunctions::sanitize($_POST['action'], 'text');
                    $postHandled = apply_filters('dd_encounters_monsters_list_post', $action);
                    if ($postHandled !== true) {
                        switch (BaseFunctions::sanitize($_POST['action'], 'text')) {
                            case 'delete-selected':
                                Monster::deleteByIds(BaseFunctions::sanitize($_POST['ids'], 'int'));
                                break;
                            case 'import':
                                $data            = array_map('str_getcsv', file($_FILES['import']['tmp_name']));
                                $keys            = array_shift($data);
                                $notAddedRecords = 0;
                                foreach ($data as $row) {
                                    $row = array_combine($keys, $row);
                                    if (empty($row['name']) || empty($row['hp'])) {
                                        ++$notAddedRecords;
                                        continue;
                                    }
                                    $name               = BaseFunctions::sanitize($row['name'], 'text');
                                    $hp                 = BaseFunctions::sanitize($row['hp'], 'text');
                                    $initiativeModifier = BaseFunctions::sanitize($row['init'] ?? $row['$initiativeModifier'] ?? 0, 'int');
                                    $url                = isset($row['url']) ? BaseFunctions::sanitize($row['url'], 'text') : '';
                                    if (Monster::findByName($name) !== null) {
                                        SSV_Global::addError('"' . $name . '" already exists.');
                                        continue;
                                    }
                                    $hp = str_replace([' + ', 'd'], ['+', 'D'], $hp);
                                    if (strpos($hp, '+') === false) {
                                        $hp .= '+0';
                                    }
                                    Monster::create($name, $hp, $initiativeModifier, $url);
                                }
                                if ($notAddedRecords > 0) {
                                    SSV_Global::addError($notAddedRecords . ' rows could not be added because they don\'t have a name/hp field.');
                                }
                                break;
                            default:
                                SSV_Global::addError('Unknown action.');
                                break;
                        }
                    }
                }
            }
            $orderBy = BaseFunctions::sanitize(isset($_GET['orderby']) ? $_GET['orderby'] : 'f_name', 'text');
            $order   = BaseFunctions::sanitize(isset($_GET['order']) ? $_GET['order'] : 'asc', 'text');
            $addNew  = '<a href="javascript:void(0)" class="page-title-action" onclick="monsterManager.addNew(\'the-list\', \'\')">Add New</a>';
            ?>
            <h1 class="wp-heading-inline"><span>Monsters</span><?= current_user_can('edit_monsters') ? $addNew : '' ?></h1>
            <?php mp_ssv_show_table(Monster::class, $orderBy, $order, current_user_can('edit_monsters')); ?>
            <h1>Import</h1>
            <h2>From CSV</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import">
                <input type="file" name="import">
                <button type="submit">Import</button>
            </form>
            <?php do_action('dd_encounters_monsters_list') ?>
        </div>
        <?php
    }

    public static function showParserOptionsPage(): void
    {
        $active_tab = "wizardawn";
        if (isset($_GET['tab'])) {
            $active_tab = $_GET['tab'];
        }
        ?>
        <div class="wrap">
            <h1>Users Options</h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=<?= $_GET['page'] ?>&tab=wizardawn" class="nav-tab <?= $active_tab == 'wizardawn' ? 'nav-tab-active' : '' ?>">Wizardawn</a>
                <a href="?page=<?= $_GET['page'] ?>&tab=donjon" class="nav-tab <?= $active_tab == 'donjon' ? 'nav-tab-active' : '' ?>">donjon</a>
            </h2>
            <?php
            switch ($active_tab) {
                case "wizardawn":
                    require_once "Parser/Wizardawn/Wizardawn.php";
                    break;
                case "donjon":
                    require_once "Parser/Donjon.php";
                    break;
            }
            ?>
        </div>
        <?php
    }
}

add_action('admin_init', [Options::class, 'registerSettings']);
add_action('admin_menu', [Options::class, 'setupSiteSpecificMenu'], 9);
add_action('network_admin_menu', [Options::class, 'setupNetworkMenu']);
