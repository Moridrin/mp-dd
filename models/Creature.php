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
        $creature->items = $_POST['items'];
        $index = 0;
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

    public function getCurrentItemsList() {
        $list = '';
        foreach ($this->items as $key => $count) {
            if ($count <= 0) {
                continue;
            }
            $id = explode('_', $key)[0];
            $type = explode('_', $key)[1];
            $item = Item::getByID($id, $type);
            $list .= $item . ' ' . $count . 'x, ';
        }
        return $list;
    }
}
