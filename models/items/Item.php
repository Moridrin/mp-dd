<?php

/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 27-2-17
 * Time: 16:06
 */
class Item extends EmbeddedObject
{
    /** @var string[] $properties */
    public $properties = array();

    const FIELD_OPTIONS
        = array(
            'damageType' => array(
                'Bludgeoning',
                'Piercing',
                'Slashing',
            ),
        );

    protected function __construct()
    {
    }

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
                <?php if ($var == 'postID'): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <tr>
                    <th>
                        <label for="<?= $var ?>">
                            <?= mp_dd_to_camel_case($var, true) ?>
                        </label>
                    </th>
                    <td>
                        <?php if (is_array($value)): ?>
                            <table>
                                <tbody id="<?= $var ?>-placeholder"></tbody>
                            </table>
                            <button onclick="mp_dd_add_new_list_item(event)" data-group="<?= $var ?>">Add <?= mp_dd_to_camel_case($var, true) ?></button>
                            <script>
                                <?php foreach($value as $index => $item): ?>
                                mp_dd_add_list_item('<?= $var ?>', '<?= $item ?>');
                                <?php endforeach; ?>
                            </script>
                        <?php elseif (is_bool($value)): ?>
                        <input type="hidden" name="<?= $var ?>" value="false"/>
                        <input type="checkbox" id="<?= $var ?>" name="<?= $var ?>" value="true" <?= $value ? 'checked' : '' ?>/>
                        <?php elseif (array_key_exists($var, self::FIELD_OPTIONS)): ?>
                            <select id="<?= $var ?>" name="<?= $var ?>">
                                <?php foreach (self::FIELD_OPTIONS[$var] as $option): ?>
                                    <option value="<?= $option ?>" <?= $option == $value ? 'selected' : '' ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
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

    public function __toString()
    {
        $post = get_post($this->postID);
        ob_start()
        ?><a href="<?= get_permalink($this->postID) ?>"><?= $post->post_title ?></a><?php
        return ob_get_clean();
    }
}

require_once 'Weapon.php';
require_once 'Armor.php';
