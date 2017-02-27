<?php

/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 27-2-17
 * Time: 16:06
 */
require_once 'items/Weapon.php';
require_once 'items/Armor.php';

class Item
{
    const TYPES
        = array(
            'weapon'       => 'Weapon',
            'armor'        => 'Armor',
            'gear'         => 'Gear',
            'tool'         => 'Tool',
            'ammunition'   => 'Ammunition',
            'mount'        => 'Mount',
            'magical_item' => 'Magical Item',
        );

    public $title;
    public $type;

    protected function __construct($title, $type)
    {
        $this->title = $title;
        $this->type  = $type;
    }

    public static function fromPOST($index)
    {
        $type = $_POST['item_' . $index . '_type'];
        switch ($type) {
            case Weapon::TYPE:
                return Weapon::fromPOST($index);
                break;
            case Armor::TYPE:
                return Armor::fromPOST($index);
                break;
        }
        return null;
    }

    private static function fromObject($object)
    {
        switch ($object->type) {
            case Weapon::TYPE:
                return Weapon::fromObject($object);
                break;
            case Armor::TYPE:
                return Armor::fromObject($object);
                break;
        }
    }

    public static function getItemsEditor($items)
    {
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed striped vertical-center" style="width: auto">
            <tbody id="items-placeholder"></tbody>
        </table>
        <select id="new_item_select" onchange="mp_ssv_add_new_custom_field()">
            <option>[Add Item]</option>
            <?php foreach (self::TYPES as $key => $name): ?>
                <option value="<?= $key ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>
        <script>
            var i = <?= count($items) ?>;
            function mp_ssv_add_new_custom_field() {
                var newType = document.getElementById('new_item_select');
                var type = newType.options[newType.selectedIndex].value;
                newType.getElementsByTagName('option')[0].selected = "selected";
                mp_dd_add_new_item(type, i);
                i++;
            }
            <?php foreach($items as $key => $item): ?>
            <?php $item = Item::fromObject($item); ?>
            mp_dd_add_new_item('<?= $item->type ?>', <?= $key ?>, <?= $item->getJSON() ?>);
            <?php endforeach; ?>
        </script>
        <?php
        return ob_get_clean();
    }

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
