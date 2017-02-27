<?php

/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 27-2-17
 * Time: 16:06
 */
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

    private $name;
    private $weight;
    private $description;

    public function __construct($name, $weight, $description)
    {
        $this->name        = $name;
        $this->weight      = $weight;
        $this->description = $description;
    }

    /**
     * @return string HTML with the editor for this item.
     */
    public function getEditor()
    {
        return 'test';
    }

    public static function getItemsEditor($items)
    {
        ob_start();
        ?>
        <table id="items-placeholder" class="wp-list-table widefat fixed striped vertical-center" style="width: auto"></table>
        <select id="new_item_select" onchange="mp_ssv_add_new_custom_field()">
            <?php foreach (self::TYPES as $key => $name): ?>
                <option value="<?= $key ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>
        <script>
            var i = <?= count($items) ?>;
            function mp_ssv_add_new_custom_field() {
                var newType = document.getElementById('new_item_select');
                var type = newType.options[newType.selectedIndex].value;
                mp_dd_add_new_item(type, i);
                i++;
            }
        </script>
        <?php
        return ob_get_clean();
    }
}