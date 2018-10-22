<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 5-7-17
 * Time: 20:49
 */

namespace mp_dd\Wizardawn\Models;

use Exception;
use mp_dd\Converter;

class NPC extends JsonObject
{
    public $race;
    public $type = 'citizen';
    public $profession = '';
    public $level = 1;
    public $class = '';
    public $name = '';
    public $height;
    public $weight;
    public $description = '';
    public $clothing = '';
    public $possessions = '';
    public $arms_armor = '';

    public function getHTML()
    {
        ob_start();
        ?>
        <table style="position: relative; display: inline-block; border: 1px solid black; margin-right: 4px;">
            <tbody>
            <tr>
                <td><label>Save</label></td>
                <td>
                    <input type="checkbox" name="npc___save[]" value="<?= $this->id ?>" title="Save" checked>
                    <button name="save_single" value="<?= $this->id ?>" title="Save Single" style="float: right;">
                        Save <?= $this->name ?></button>
                </td>
            </tr>
            <tr>
                <td><label>race</label></td>
                <td>
                    <input name="npc___race[<?= $this->id ?>]" value="<?= $this->race ?>" title="race">
                </td>
            </tr>
            <tr>
                <td><label>type</label></td>
                <td>
                    <input name="npc___type[<?= $this->id ?>]" value="<?= $this->type ?>" title="type">
                </td>
            </tr>
            <tr>
                <td><label>profession</label></td>
                <td>
                    <input name="npc___profession[<?= $this->id ?>]" value="<?= $this->profession ?>"
                           title="profession">
                </td>
            </tr>
            <tr>
                <td><label>level</label></td>
                <td>
                    <input name="npc___level[<?= $this->id ?>]" value="<?= $this->level ?>" title="level">
                </td>
            </tr>
            <tr>
                <td><label>class</label></td>
                <td>
                    <input name="npc___class[<?= $this->id ?>]" value="<?= $this->class ?>" title="class">
                </td>
            </tr>
            <tr>
                <td><label>name</label></td>
                <td>
                    <input name="npc___name[<?= $this->id ?>]" value="<?= $this->name ?>" title="name">
                </td>
            </tr>
            <tr>
                <td><label>height</label></td>
                <td>
                    <input name="npc___height[<?= $this->id ?>]" value="<?= $this->height ?>" title="height">
                </td>
            </tr>
            <tr>
                <td><label>weight</label></td>
                <td>
                    <input name="npc___weight[<?= $this->id ?>]" value="<?= $this->weight ?>" title="weight">
                </td>
            </tr>
            <tr>
                <td><label>description</label></td>
                <td>
                    <textarea style="width: 100%;" name="npc___description[<?= $this->id ?>]"
                              title="description"><?= $this->description ?></textarea>
                </td>
            </tr>
            <tr>
                <td><label>clothing</label></td>
                <td>
                    <textarea style="width: 100%;" name="npc___clothing[<?= $this->id ?>]"
                              title="clothing"><?= $this->clothing ?></textarea>
                </td>
            </tr>
            <tr>
                <td><label>possessions</label></td>
                <td>
                    <textarea style="width: 100%;" name="npc___possessions[<?= $this->id ?>]"
                              title="possessions"><?= $this->possessions ?></textarea>
                </td>
            </tr>
            <tr>
                <td><label>arms_armor</label></td>
                <td>
                    <textarea style="width: 100%;" name="npc___arms_armor[<?= $this->id ?>]"
                              title="arms_armor"><?= $this->arms_armor ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public static function getFromPOST($id, $unset = false)
    {
        $npc = new self();
        $npc->setID($id);
        $fields = [
            'race',
            'type',
            'profession',
            'level',
            'class',
            'name',
            'height',
            'weight',
            'description',
            'clothing',
            'possessions',
            'arms_armor',
        ];
        foreach ($fields as $field) {
            if (!isset($_POST['npc___' . $field][$id])) {
                throw new \Exception('The max_input_vars is set to low (not all fields are available in $_POST).');
            }
            $value = $_POST['npc___' . $field][$id];
            if (!empty($_POST['npc___' . $field][$id])) {
                $npc->$field = $value;
            }
            if ($unset) {
                unset($_POST['npc___' . $field][$id]);
            }
        }
        return $npc;
    }

    /**
     * @return int|\WP_Error
     */
    public function toWordPress()
    {
        $title = $this->name;
        if ($this->type == 'spouse' || $this->type == 'child') {
            $title .= ' (' . $this->type . ')';
        }
        $content = $this->description;

        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'npc' AND p.post_title = '$title' AND p.post_content = '$content'";
        /** @var \WP_Post $foundNPC */
        $foundNPC = $wpdb->get_row($sql);
        if ($foundNPC) {
            // The NPC has been found (not saving another instance but returning the found ID).
            Converter::updateID($this->id, $foundNPC->ID);
            $savedBuildings = $_SESSION['saved_npcs'];
            $savedBuildings[$foundNPC->ID] = $this;
            $_SESSION['saved_npcs'] = $savedBuildings;
            return $foundNPC->ID;
        }

        $taxonomies = [
            'type',
            'profession',
            'race',
            'class',
            'level',
        ];
        $custom_tax = [];
        foreach ($taxonomies as $taxonomy) {
            if (!empty($this->$taxonomy)) {
                $custom_tax['npc_' . $taxonomy] = [
                    $this->$taxonomy,
                ];
            }
        }

        $wp_id = wp_insert_post(
            [
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'npc',
                'post_status' => 'publish',
                'tax_input' => $custom_tax,
            ]
        );
        foreach ($this as $key => $value) {
            if ($key == 'name' || $key == 'description' || $key == 'html') {
                continue;
            }
            update_post_meta($wp_id, $key, $value);
        }
        Converter::updateID($this->id, $wp_id);
        $savedBuildings = $_SESSION['saved_npcs'];
        $savedBuildings[$wp_id] = $this;
        $_SESSION['saved_npcs'] = $savedBuildings;
        return $wp_id;
    }
}
