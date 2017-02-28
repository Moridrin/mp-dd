<?php

/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 27-2-17
 * Time: 16:06
 */
class Item extends EmbeddedObject implements EmbeddedObjectInterface
{
    public $description;

    protected function __construct()
    {
    }

//    public static function fromPOST()
//    {
//        if (!isset($_POST['type'])) {
//            return new Item();
//        }
//        $type = $_POST['type'];
//        switch ($type) {
//            case Weapon::TYPE:
//                return Weapon::fromPOST();
//                break;
//            case Armor::TYPE:
//                return Armor::fromPOST();
//                break;
//            default:
//                return parent::fromPOST();
//                break;
//        }
//    }

    public function getEditor()
    {
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed striped vertical-center" style="width: auto">
            <?php foreach (get_object_vars($this) as $var => $value): ?>
                <tr>
                    <th>
                        <label for="<?= $var ?>">
                            <?= mp_dd_to_camel_case($var, true) ?>
                        </label>
                    </th>
                    <td>
                        <input id="<?= $var ?>" name="<?= $var ?>" value="<?= $value ?>"/>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        return ob_get_clean();
    }
}

require_once 'Weapon.php';
require_once 'Armor.php';
