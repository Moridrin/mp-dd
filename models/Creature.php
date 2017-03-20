<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 7:56
 */
class Creature extends EmbeddedObject
{
    #region Constants
    const STATS
        = array(
            'strength'     => array(
                'strengthSavingThrow',
                'athletics',
            ),
            'dexterity'    => array(
                'dexteritySavingThrow',
                'acrobatics',
                'sleightOfHand',
                'stealth',
            ),
            'constitution' => array(
                'constitutionSavingThrow',
            ),
            'intelligence' => array(
                'intelligenceSavingThrow',
                'arcana',
                'history',
                'investigation',
                'nature',
                'religion',
            ),
            'wisdom'       => array(
                'wisdomSavingThrow',
                'animalHandling',
                'insight',
                'medicine',
                'perception',
                'survival',
            ),
            'charisma'     => array(
                'charismaSavingThrow',
                'deception',
                'intimidation',
                'performance',
                'persuasion',
            ),
        );
    #endregion

    #region Variables
    #region Stats
    protected $proficiency = 2;
    protected $armorClass;
    protected $hitPoints;
    protected $speed;

    protected $strength = 10;
    protected $strengthSavingThrow = false;
    protected $athletics = false;

    protected $dexterity = 10;
    protected $dexteritySavingThrow = false;
    protected $acrobatics = false;
    protected $sleightOfHand = false;
    protected $stealth = false;

    protected $constitution = 10;
    protected $constitutionSavingThrow = false;

    protected $intelligence = 10;
    protected $intelligenceSavingThrow = false;
    protected $arcana = false;
    protected $history = false;
    protected $investigation = false;
    protected $nature = false;
    protected $religion = false;

    protected $wisdom = 10;
    protected $wisdomSavingThrow = false;
    protected $animalHandling = false;
    protected $insight = false;
    protected $medicine = false;
    protected $perception = false;
    protected $survival = false;

    protected $charisma = 10;
    protected $charismaSavingThrow = false;
    protected $deception = false;
    protected $intimidation = false;
    protected $performance = false;
    protected $persuasion = false;
    #endregion

    /** @var int $race PostID for the Race */
    public $race;
    /** @var int $class PostID for the Class */
    public $class;
    /** @var int $background PostID for the Background */
    public $background;

    /** @var int[] $items */
    public $items = array();

    /** @var string[] $properties */
    public $properties = array();
    #endregion

    #region fromPOST()
    /**
     * This function builds the Embedded Object from the $_POST variable.
     *
     * @param int $postID is the id of the post where this object is embedded in.
     *
     * @return Creature|false
     */
    public static function fromPOST($postID)
    {
        $creature = new Creature();
        foreach (get_object_vars($creature) as $var => $value) {
            if (isset($_POST[$var])) {
                $creature->$var = $_POST[$var];
            }
        }
        $creature->items = isset($_POST['items']) ? $_POST['items'] : array();
        $index           = 0;
        while (isset($_POST['property_' . $index . '_title'])) {
            if (empty($_POST['property_' . $index . '_title'])) {
                $index++;
                continue;
            }
            if (isset($_POST['property_' . $index . '_description'])) {
                $creature->properties[$_POST['property_' . $index . '_title']] = str_replace(PHP_EOL, '<br/>', $_POST['property_' . $index . '_description']);
            }
            $index++;
        }
        $creature->items = array_diff($creature->items, array(0));
        ksort($creature->items);
        $creature->postID = $postID;
        return $creature;
    }
    #endregion

    #region getStatsEditor()
    /**
     * @return string HTML Table to edit the stats.
     */
    public function getStatsEditor()
    {
        ob_start();
        ?>
        <table id="stats_table" class="wp-list-table widefat fixed striped vertical-center" style="width: auto">
            <tr>
                <th><label for="proficiency">Proficiency</label></th>
                <td colspan="3"><input id="proficiency" type="number" name="proficiency" value="<?= $this->proficiency ?>"></td>
            </tr>
            <tr>
                <th><label for="armorClass">Armor Class</label></th>
                <td colspan="3"><input id="armorClass" type="text" name="armorClass" value="<?= $this->armorClass ?>"></td>
            </tr>
            <tr>
                <th><label for="hitPoints">Hit Points</label></th>
                <td colspan="3"><input id="hitPoints" type="text" name="hitPoints" value="<?= $this->hitPoints ?>"></td>
            </tr>
            <tr>
                <th><label for="speed">Speed</label></th>
                <td colspan="3"><input id="speed" type="text" name="speed" value="<?= $this->speed ?>"></td>
            </tr>
            <?php foreach (self::STATS as $stat => $skills): ?>
                <tr>
                    <th><label for="stat_<?= $stat ?>"><?= ucwords(str_replace('_', ' ', $stat)) ?></label></th>
                    <td><input id="stat_<?= $stat ?>" type="number" name="<?= $stat ?>" value="<?= $this->$stat ?>"/></td>
                    <td id="stat_<?= $stat ?>_modifier">+0</td>
                    <td>
                        <table>
                            <?php foreach ($skills as $skill): ?>
                                <tr>
                                    <th><label for="skill_<?= $skill ?>"><?= ucwords(str_replace('_', ' ', str_replace($stat . '_', '', $skill))) ?></label></th>
                                    <td><input id="skill_<?= $skill ?>" type="checkbox" name="<?= $skill ?>" data-stat="<?= $stat ?>" <?= $this->$skill ? 'checked' : '' ?>/></td>
                                    <td id="skill_<?= $skill ?>_modifier">+0</td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <script>
            jQuery(function ($) {
                var proficiencyField = $('#proficiency');
                var proficiency = parseInt(proficiencyField.val());
                var stats = <?= json_encode(self::STATS) ?>;
                var statsTable = $("#stats_table");
                statsTable.find(':input[type="number"]').on('input', function () {
                    var stat = $(this).attr('id');
                    stat = stat.replace('stat_', '');
                    mp_dd_update_stats(stat);
                });
                statsTable.find(':input[type="checkbox"]').change(function () {
                    var stat = $(this).data('stat');
                    mp_dd_update_stats(stat);
                });
                proficiencyField.on('input', function () {
                    proficiency = parseInt($(this).val());
                    $.each(stats, function (stat, skills) {
                        mp_dd_update_stats(stat);
                    })
                });
                $.each(stats, function (stat, skills) {
                    mp_dd_update_stats(stat);
                });

                function mp_dd_update_stats(stat) {
                    var statModifier = Math.floor(($('#stat_' + stat).val() - 10) / 2);
                    if (statModifier >= 0) {
                        $('#stat_' + stat + '_modifier').html('+' + statModifier);
                    } else {
                        $('#stat_' + stat + '_modifier').html(statModifier);
                    }

                    var skills = stats[stat];
                    $.each(skills, function (index, skill) {
                        var skillModifier = statModifier;
                        if ($('#skill_' + skill).is(':checked')) {
                            skillModifier += proficiency;
                        }
                        if (statModifier >= 0) {
                            $('#skill_' + skill + '_modifier').html('+' + skillModifier);
                        } else {
                            $('#skill_' + skill + '_modifier').html(skillModifier);
                        }
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    #endregion

    #region getPlayerEditor()
    /**
     * @return string HTML Table to edit the player type.
     */
    public function getTypeEditor()
    {
        ob_start();
        ?>
        <table id="player_table" class="wp-list-table widefat fixed striped vertical-center" style="width: auto">
            <tr>
                <th><label for="race">Race</label></th>
                <td colspan="3">
                    <?= PropertyGroup::getPropertyGroupSelect('race', $this->race) ?>
                </td>
            </tr>
            <tr>
                <th><label for="class">Class</label></th>
                <td colspan="3">
                    <select id="class" name="class">
                        <option value="-1">Monster</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="background">Background</label></th>
                <td colspan="3">
                    <select id="background" name="background">
                        <option value="-1">Monster</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

    #endregion

    public function getItemFromKey($key)
    {
        if (strpos($key, '_') === false) {
            return null;
        }
        $type = explode('_', $key)[0];
        $id   = explode('_', $key)[1];
        return Item::getByID($id, $type);
    }

    public function getCurrentItemsList()
    {
        $list = '';
        foreach ($this->items as $key => $count) {
            if ($count <= 0 || strpos($key, '_') === false) {
                continue;
            }
            $item = $this->getItemFromKey($key);
            $list .= $item . ' ' . $count . 'x, ';
        }
        return $list;
    }

    public function getHTML($description)
    {
        ob_start();
        ?>
        <ul class="collapsible" data-collapsible="expandable">
            <li>
                <div class="collapsible-header">Creature Type</div>
                <div class="collapsible-body row" style="margin-bottom: 0;">
                    <div class="col s4"><strong>Race</strong></div>
                    <div class="col s8"><?= $this->race > 0 ? PropertyGroup::load($this->race)->getLink() : 'Monster' ?></div>
                    <div class="col s4"><strong>Class</strong></div>
                    <div class="col s8"><?= $this->class > 0 ? PropertyGroup::load($this->class)->getLink() : 'Monster' ?></div>
                    <div class="col s4"><strong>Background</strong></div>
                    <div class="col s8"><?= $this->background > 0 ? PropertyGroup::load($this->background)->getLink() : 'Monster' ?></div>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Stats</div>
                <div class="collapsible-body">
                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s4"><strong>Proficiency</strong></div>
                        <div class="col s8"><?= $this->proficiency ?></div>
                        <div class="col s4"><strong>Armor Class</strong></div>
                        <div class="col s8"><?= $this->armorClass ?></div>
                        <div class="col s4"><strong>Hit Points</strong></div>
                        <div class="col s8"><?= $this->hitPoints ?></div>
                        <div class="col s4"><strong>Speed</strong></div>
                        <div class="col s8"><?= $this->speed ?></div>
                    </div>
                    <ul class="collapsible" data-collapsible="expandable">
                        <?php foreach (self::STATS as $stat => $skills): ?>
                            <?php $statModifier = floor(($this->$stat - 10) / 2); ?>
                            <li>
                                <div class="collapsible-header" style="padding: 0;">
                                    <div class="col s4"><?= mp_dd_to_title($stat) ?></div>
                                    <div class="col s4"><?= $this->$stat ?></div>
                                    <div class="col s4"><?= $statModifier >= 0 ? '+' . $statModifier : $statModifier ?></div>
                                </div>
                                <div class="collapsible-body">
                                    <div class="row" style="margin-bottom: 0;">
                                        <?php foreach ($skills as $skill): ?>
                                            <?php $skillModifier = $this->$skill ? $statModifier + $this->proficiency : $statModifier; ?>
                                            <div class="col s8">
                                                <input id="skill_<?= $skill ?>" type="checkbox" name="<?= $skill ?>" data-stat="<?= $stat ?>" <?= $this->$skill ? 'checked' : '' ?> class="filled-in" disabled/>
                                                <label for="skill_<?= $skill ?>"><?= ucwords(str_replace('_', ' ', str_replace($stat . '_', '', $skill))) ?></label>
                                            </div>
                                            <div class="col s4" style="height: 25px">
                                                <?= $skillModifier >= 0 ? '+' . $skillModifier : $skillModifier ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Properties</div>
                <div class="collapsible-body">
                    <table class="striped">
                        <?php
                        $properties = $this->properties;
                        if ($this->race > 0) {
                            foreach (PropertyGroup::load($this->race)->properties as $title => $property_description) {
                                if (isset($properties[$title]) && $properties[$title] != $property_description) {
                                    $property_description = $properties[$title] . '<br/>' . $property_description;
                                }
                                $properties[$title] = $property_description;
                            }
                        }
                        ?>
                        <?php foreach ($properties as $title => $property_description): ?>
                            <tr>
                                <th><?= $title ?></th>
                                <td colspan="3"><?= $property_description ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Items</div>
                <div class="collapsible-body">
                    <?php
                    if (count($this->items) > 0) {
                        foreach ($this->items as $key => $count) {
                            $item = $this->getItemFromKey($key);
                            echo $item->getHTML($item->getPost()->post_title . ' (' . $count . 'x)');
                        }
                    } else {
                        echo 'No Items';
                    }
                    ?>
                </div>
            </li>
        </ul>
        <?php
        return ob_get_clean() . $description;
    }
}
