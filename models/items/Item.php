<?php

/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 27-2-17
 * Time: 16:06
 */
class Item extends EmbeddedObject implements EmbeddedObjectInterface
{
    /** @var  string[] $properties */
    public $properties = array();

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
        <script>
            var index = {};
            function mp_dd_add_new_list_item(event) {
                event.preventDefault();
                var caller = event.target || event.srcElement;
                var group = caller.dataset.group;
                mp_dd_add_list_item(group, '');
            }
            function mp_dd_add_list_item(group, value) {
                if (!(group in index)) {
                    index[group] = 0;
                }
                var container = document.getElementById(group + "-placeholder");
                var tr = document.createElement("tr");
                tr.setAttribute("id", index[group] + "_tr");
                tr.appendChild(getTextListItemTD(group, index[group], value));
                container.appendChild(tr);
                index[group]++;
            }
        </script>
        <table class="wp-list-table widefat fixed striped vertical-center" style="width: auto">
            <tbody>
            <?php foreach (get_object_vars($this) as $var => $value): ?>
                <tr>
                    <th>
                        <label for="<?= $var ?>">
                            <?= mp_dd_to_camel_case($var, true) ?>
                        </label>
                    </th>
                    <td>
                        <?php if (is_array($value)): ?>
                            <table><tbody id="<?= $var ?>-placeholder"></tbody></table>
                            <button onclick="mp_dd_add_new_list_item(event)" data-group="<?= $var ?>">Add <?= mp_dd_to_camel_case($var, true) ?></button>
                            <script>
                                <?php foreach($value as $index => $item): ?>
                                mp_dd_add_list_item('<?= $var ?>', '<?= $item ?>');
                                <?php endforeach; ?>
                            </script>
                        <?php else: ?>
                        <input id="<?= $var ?>" name="<?= $var ?>" value="<?= $value ?>"/>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}

require_once 'Weapon.php';
require_once 'Armor.php';
