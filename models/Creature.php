<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 7:56
 */
class Creature
{
    private $proficiency = 2;

    #region Stats
    private $strength = 10;
    private $strengthSavingThrow = false;
    private $athletics = false;

    private $dexterity = 10;
    private $dexteritySavingThrow = false;
    private $acrobatics = false;
    private $sleightOfHand = false;
    private $stealth = false;

    private $constitution = 10;
    private $constitutionSavingThrow = false;

    private $intelligence = 10;
    private $intelligenceSavingThrow = false;
    private $arcana = false;
    private $history = false;
    private $investigation = false;
    private $nature = false;
    private $religion = false;

    private $wisdom = 10;
    private $wisdomSavingThrow = false;
    private $animalHandling = false;
    private $insight = false;
    private $medicine = false;
    private $perception = false;
    private $survival = false;

    private $charisma = 10;
    private $charismaSavingThrow = false;
    private $deception = false;
    private $intimidation = false;
    private $performance = false;
    private $persuasion = false;
    #endregion

    private static $stats
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

    #region getByID($id)
    /**
     * @param int $id is the post_id;
     *
     * @return Creature
     */
    public static function getByID($id)
    {
        $creature = new Creature();
        return $creature;
    }
    #endregion

    #region fromJSON($json)
    /**
     * @param string $json
     *
     * @return Creature
     */
    public static function fromJSON($json)
    {
        $objectVars = json_decode($json);
        $creature   = new Creature();
        foreach ($objectVars as $var => $value) {
            $creature->$var = $value;
        }
        return $creature;
    }
    #endregion

    #region fromPOST()
    /**
     * @return Creature|false
     */
    public static function fromPOST()
    {
        $creature = new Creature();
        foreach (get_object_vars($creature) as $var => $value) {
            if (isset($_POST[$var])) {
                $creature->$var = $_POST[$var];
            }
        }
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
            <?php foreach (self::$stats as $stat => $skills): ?>
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
                var stats = <?= json_encode(self::$stats) ?>;
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

    #region getJSON()
    /**
     * @return string json string with the encoded object vars.
     */
    public function getJSON()
    {
        return json_encode(get_object_vars($this));
    }
    #endregion
}