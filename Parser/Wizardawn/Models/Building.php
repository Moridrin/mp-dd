<?php

namespace mp_dd\Wizardawn\Models;

use Exception;
use mp_dd\MP_DD;
use mp_dd\Converter;

class Building extends JsonObject
{
    public $label;
    protected $type;
    protected $title = null;
    /** @var NPC[] */
    protected $npcs = [];
    /** @var Product[] */
    protected $products = [];
    /** @var Spell[] */
    protected $spells = [];
    /** @var array */
    protected $vaultItems = [];

    public function __construct(int $buildingID, string $type)
    {
        parent::__construct();
        $this->label = $buildingID;
        $this->type  = $type;
        $this->title = 'Building ' . $buildingID;
    }

    public function getID()
    {
        return $this->label;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function addNPC(NPC $npc, bool $overrideOwner = false)
    {
        if ($overrideOwner && !empty($this->npcs)) {
            $this->npcs[array_keys($this->npcs)[0]] = $npc;
        } else {
            $this->npcs[$npc->id] = $npc;
        }
    }

    /**
     * @return NPC[]|int[]
     */
    public function getNPCs(): array
    {
        return $this->npcs;
    }

    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    public function addProduct(Product $product)
    {
        $this->products[$product->id] = $product;
    }

    public function addSpell(Spell $spell)
    {
        $this->spells[$spell->id] = $spell;
    }

    public function getSpells()
    {
        return $this->spells;
    }

    public function setVaultItems($vaultItems)
    {
        $this->vaultItems = $vaultItems;
    }

    public function getVaultItems(): array
    {
        return $this->vaultItems;
    }

    public function updateWith(Building $building)
    {
        if ($this->label != $building->label) {
            throw new \Exception("The Buildings have different Labels (indicating that they are different buildings)");
        }
        $this->type  = $building->type;
        $this->title = $building->title;
        foreach ($building->npcs as $npc) {
            if (!in_array($npc->name, array_column($this->npcs, 'name'))) {
                $this->npcs[$npc->id] = $npc;
            }
        }
        foreach ($building->products as $product) {
            if (!in_array($product, $this->products)) {
                $this->products[$product->id] = $product;
            }
        }
        foreach ($building->spells as $spells) {
            if (!in_array($spells, $this->spells)) {
                $this->spells[$spells->id] = $spells;
            }
        }
        foreach ($building->vaultItems as $type => $items) {
            foreach ($items as $item) {
                if (!in_array($item, $this->vaultItems[$type])) {
                    $this->vaultItems[$type][] = $item;
                }
            }
        }

        return $this;
    }

    public function getHTML()
    {
        ob_start();
        ?>
        <table style="position: relative; display: inline-block; border: 1px solid black; margin-right: 4px; width: 600px;">
            <tbody style="width: 100%; display: table;">
            <tr>
                <td><label>Save</label></td>
                <td>
                    <input type="checkbox" name="building___save[]" value="<?= $this->id ?>" title="Save" checked>
                    <button name="save_single" value="<?= $this->id ?>" title="Save Single" style="float: right;">Save Building <?= $this->label ?></button>
                </td>
            </tr>
            <tr>
                <td><label>ID</label></td>
                <td>
                    <?= $this->label ?>
                    <input type="hidden" name="building___label[<?= $this->id ?>]" value="<?= $this->label ?>" title="Label" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <td><label>Title</label></td>
                <td>
                    <input name="building___title[<?= $this->id ?>]" value="<?= $this->title ?>" title="Title" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <td><label>NPCs</label></td>
                <td>
                    <select name="building___npcs[<?= $this->id ?>][]" title="NPCs" multiple style="width: 100%;">
                        <?php
                        global $wpdb;
                        $sql     = "SELECT ID, post_title FROM $wpdb->posts WHERE (post_type = 'area' OR post_type = 'npc' OR post_type = 'item') AND post_status = 'publish'";
                        $objects = $wpdb->get_results($sql);
                        foreach ($objects as $object) {
                            ?>
                            <option value="<?= $object->ID ?>" <?= in_array($object->ID, $this->npcs) ? 'selected' : '' ?>><?= $object->post_title ?></option><?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label>Products</label></td>
                <td>
                    <table style="width: 100%;">
                        <tbody id="<?= $this->id ?>_products_table" style="display: block; height: 150px; overflow-y: auto; overflow-x: hidden;">
                        <?php $productID = 0; ?>
                        <?php foreach ($this->products as $product): ?>
                            <tr id="<?= $this->id ?>_product_row_<?= $productID ?>">
                                <td><input name="building___products[<?= $this->id ?>][name][]" value="<?= $product->name ?>" title="NPCs" style="width: 120px;"></td>
                                <td><input name="building___products[<?= $this->id ?>][cost][]" value="<?= $product->cost ?>" title="NPCs" style="width: 40px;"></td>
                                <td><input name="building___products[<?= $this->id ?>][in_stock][]" value="<?= $product->inStock ?>" title="NPCs" style="width: 30px;"></td>
                                <td><button type="button" onclick="removeProduct('<?= $this->id ?>', '<?= $productID ?>')">X</button></td>
                            </tr>
                            <?php ++$productID; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" onclick="addProduct('<?= $this->id ?>', this)" data-rows="<?= $productID ?>">Add</button>
                </td>
            </tr>
            <tr>
                <td><label>Spells</label></td>
                <td>
                    <table style="width: 100%;">
                        <tbody id="<?= $this->id ?>_spells_table" style="display: block; height: 150px; overflow-y: auto; overflow-x: hidden;">
                        <?php $spellID = 0; ?>
                        <?php foreach ($this->spells as $spell): ?>
                            <tr id="<?= $this->id ?>_spell_row_<?= $spellID ?>">
                                <td><input name="building___spells[<?= $this->id ?>][spell][]" value="<?= $spell->spell ?>" title="Spell name" style="width: 150px"></td>
                                <td><input name="building___spells[<?= $this->id ?>][cost][]" value="<?= $spell->cost ?>" title="Spell cost" style="width: 50px"></td>
                                <td><button type="button" onclick="removeSpell('<?= $this->id ?>', '<?= $spellID ?>')">X</button></td>
                            </tr>
                            <?php ++$spellID; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" onclick="addSpell('<?= $this->id ?>', this)" data-rows="<?= $spellID ?>">Add</button>
                </td>
            </tr>
            <tr>
                <?php $vaultItemId = 0; ?>
                <td><label>Vault</label></td>
                <td>
                    <table style="width: 100%;">
                        <tbody id="<?= $this->id ?>_vault_items_table" style="display: block; height: 150px; overflow-y: auto; overflow-x: hidden;">
                        <?php /** @var VaultItem $vaultItem */ ?>
                        <?php foreach ($this->vaultItems as $vaultItem): ?>
                            <tr id="<?= $this->id ?>_vault_item_row_<?= $vaultItemId ?>">
                                <td>
                                    <select name="building___vault[<?= $this->id ?>][type][]" title="Vault Item Type" style="width: 70px">
                                        <option value="<?= VaultItem::GEM ?>" <?= $vaultItem->type === VaultItem::GEM ? 'selected' : '' ?>><?= VaultItem::GEM ?></option>
                                        <option value="<?= VaultItem::JEWEL ?>" <?= $vaultItem->type === VaultItem::JEWEL ? 'selected' : '' ?>><?= VaultItem::JEWEL ?></option>
                                        <option value="<?= VaultItem::OTHER ?>" <?= $vaultItem->type === VaultItem::OTHER ? 'selected' : '' ?>><?= VaultItem::OTHER ?></option>
                                    </select>
                                </td>
                                <td><input name="building___vault[<?= $this->id ?>][title][]" value="<?= $vaultItem->title ?>" title="Vault Item Name" style="width: 130px"></td>
                                <td><input name="building___vault[<?= $this->id ?>][description][]" value="<?= $vaultItem->description ?>" title="Vault Item Description" style="width: 150px"></td>
                                <td><input name="building___vault[<?= $this->id ?>][cost][]" value="<?= $vaultItem->cost ?>" title="Vault Item Cost" style="width: 70px"></td>
                                <td><input name="building___vault[<?= $this->id ?>][count][]" value="<?= $vaultItem->count ?>" title="Vault Item Count" style="width: 30px"></td>
                                <td><button type="button" onclick="removeVaultItem('<?= $this->id ?>', '<?= $vaultItemId ?>')">X</button></td>
                            </tr>
                            <?php ++$vaultItemId; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" onclick="addVaultItem('<?= $this->id ?>', this)" data-rows="<?= $spellID ?>">Add</button>
                </td>
            </tr>
            <tr>
                <td><label>Type</label></td>
                <td>
                    <input name="building___type[<?= $this->id ?>]" value="<?= $this->type ?>" title="Type" style="width: 100%;">
                </td>
            </tr>
            </tbody>
        </table>
        <script>
            if (typeof removeProduct !== 'function') {

                function addProduct(buildingID, sender) {
                    var productID = sender.getAttribute('data-rows');
                    var row = document.createElement('tr');
                    row.setAttribute('id', buildingID + '_product_row_' + productID);
                    var tdName = document.createElement('td');
                    var inputName = document.createElement('input');
                    inputName.setAttribute('name', 'building___products[' + buildingID + '][name][]');
                    inputName.setAttribute('style', 'width: 120px;');
                    tdName.appendChild(inputName);
                    row.appendChild(tdName);
                    var tdCost = document.createElement('td');
                    var inputCost = document.createElement('input');
                    inputCost.setAttribute('name', 'building___products[' + buildingID + '][cost][]');
                    inputCost.setAttribute('style', 'width: 40px;');
                    tdCost.appendChild(inputCost);
                    row.appendChild(tdCost);
                    var tdInStock = document.createElement('td');
                    var inputInStoc = document.createElement('input');
                    inputInStoc.setAttribute('name', 'building___products[' + buildingID + '][in_stock][]');
                    inputInStoc.setAttribute('style', 'width: 30px;');
                    tdInStock.appendChild(inputInStoc);
                    row.appendChild(tdInStock);
                    var tdRemove = document.createElement('td');
                    var buttonRemove = document.createElement('button');
                    buttonRemove.setAttribute('type', 'button');
                    buttonRemove.setAttribute('onclick', 'removeProduct(\'' + buildingID + '\', \'' + productID + '\')');
                    buttonRemove.innerHTML = 'X';
                    tdRemove.appendChild(buttonRemove);
                    row.appendChild(tdRemove);
                    document.getElementById(buildingID + '_products_table').appendChild(row);
                    productID++;
                    sender.setAttribute('data-rows', productID);
                }

                function removeProduct(buildingID, productID) {
                    var row = document.getElementById(buildingID + '_product_row_' + productID);
                    row.parentElement.removeChild(row);
                }

                function addSpell(buildingID, sender) {
                    var spellID = sender.getAttribute('data-rows');
                    var row = document.createElement('tr');
                    row.setAttribute('id', buildingID + '_spell_row_' + spellID);
                    var tdName = document.createElement('td');
                    var inputName = document.createElement('input');
                    inputName.setAttribute('name', 'building___spells[' + buildingID + '][spell][]');
                    inputName.setAttribute('style', 'width: 150px;');
                    tdName.appendChild(inputName);
                    row.appendChild(tdName);
                    var tdCost = document.createElement('td');
                    var inputCost = document.createElement('input');
                    inputCost.setAttribute('name', 'building___spells[' + buildingID + '][cost][]');
                    inputCost.setAttribute('style', 'width: 50px;');
                    tdCost.appendChild(inputCost);
                    row.appendChild(tdCost);
                    var tdRemove = document.createElement('td');
                    var buttonRemove = document.createElement('button');
                    buttonRemove.setAttribute('type', 'button');
                    buttonRemove.setAttribute('onclick', 'removeSpell(\'' + buildingID + '\', \'' + spellID + '\')');
                    buttonRemove.innerHTML = 'X';
                    tdRemove.appendChild(buttonRemove);
                    row.appendChild(tdRemove);
                    document.getElementById(buildingID + '_spells_table').appendChild(row);
                    spellID++;
                    sender.setAttribute('data-rows', spellID);
                }

                function removeSpell(buildingID, spellID) {
                    var row = document.getElementById(buildingID + '_spell_row_' + spellID);
                    row.parentElement.removeChild(row);
                }

                function addVaultItem(buildingID, sender) {
                    var gemId = sender.getAttribute('data-rows');
                    var row = document.createElement('tr');
                    row.setAttribute('id', buildingID + '_vault_item_row_' + gemId);
                    var tdType = document.createElement('td');
                    var inputType = document.createElement('select');
                    inputType.setAttribute('name', 'building___vault[' + buildingID + '][type][]');
                    inputType.setAttribute('style', 'width: 70px;');
                    let gemOption = document.createElement('option');
                    gemOption.setAttribute('value', '<?= VaultItem::GEM ?>');
                    gemOption.innerText = '<?= VaultItem::GEM ?>';
                    inputType.appendChild(gemOption);
                    let jewelOption = document.createElement('option');
                    jewelOption.setAttribute('value', '<?= VaultItem::JEWEL ?>');
                    jewelOption.innerText = '<?= VaultItem::JEWEL ?>';
                    inputType.appendChild(jewelOption);
                    let otherOption = document.createElement('option');
                    otherOption.setAttribute('value', '<?= VaultItem::OTHER ?>');
                    otherOption.innerText = '<?= VaultItem::OTHER ?>';
                    inputType.appendChild(otherOption);
                    tdType.appendChild(inputType);
                    row.appendChild(tdType);
                    var tdTitle = document.createElement('td');
                    var inputTitle = document.createElement('input');
                    inputTitle.setAttribute('name', 'building___vault[' + buildingID + '][title][]');
                    inputTitle.setAttribute('style', 'width: 130px;');
                    tdTitle.appendChild(inputTitle);
                    row.appendChild(tdTitle);
                    var tdDescription = document.createElement('td');
                    var inputDescription = document.createElement('input');
                    inputDescription.setAttribute('name', 'building___vault[' + buildingID + '][description][]');
                    inputDescription.setAttribute('style', 'width: 150px;');
                    tdDescription.appendChild(inputDescription);
                    row.appendChild(tdDescription);
                    var tdCost = document.createElement('td');
                    var inputCost = document.createElement('input');
                    inputCost.setAttribute('name', 'building___vault[' + buildingID + '][cost][]');
                    inputCost.setAttribute('style', 'width: 70px;');
                    tdCost.appendChild(inputCost);
                    row.appendChild(tdCost);
                    var tdCount = document.createElement('td');
                    var inputCount = document.createElement('input');
                    inputCount.setAttribute('name', 'building___vault[' + buildingID + '][count][]');
                    inputCount.setAttribute('style', 'width: 30px;');
                    tdCount.appendChild(inputCount);
                    row.appendChild(tdCount);
                    var tdRemove = document.createElement('td');
                    var buttonRemove = document.createElement('button');
                    buttonRemove.setAttribute('type', 'button');
                    buttonRemove.setAttribute('onclick', 'removeVaultItem(\'' + buildingID + '\', \'' + gemId + '\')');
                    buttonRemove.innerHTML = 'X';
                    tdRemove.appendChild(buttonRemove);
                    row.appendChild(tdRemove);
                    document.getElementById(buildingID + '_vault_items_table').appendChild(row);
                    gemId++;
                    sender.setAttribute('data-rows', gemId);
                }

                function removeVaultItem(buildingID, vaultItemId) {
                    var row = document.getElementById(buildingID + '_vault_item_row_' + vaultItemId);
                    row.parentElement.removeChild(row);
                }
            }
        </script>
        <?php
        return ob_get_clean();
    }

    public static function getFromPOST($id, $unset = false)
    {
        $buildingID = $_POST['building___label'][$id];
        $building = new self($buildingID, $_POST['building___type'][$id]);
        $building->setID($id);
        $fields   = [
            'title',
            'npcs',
            'products',
            'spells',
            'vault',
        ];
        foreach ($fields as $field) {
            if (!isset($_POST['building___' . $field]) || !isset($_POST['building___' . $field][$id])) {
                continue;
            }
            $value = $_POST['building___' . $field][$id];
            if (!empty($value)) {
                if ($field == 'products') {
                    $building->products = Product::getFromArray($value);
                } elseif ($field == 'spells') {
                    $building->spells = Spell::getFromArray($value);
                } elseif ($field == 'vault') {
                    $building->vaultItems = VaultItem::getFromArray($value);
                } else {
                    $building->$field = $value;
                }
            }
            if ($unset) {
                unset($_POST['building___' . $field][$id]);
            }
        }
        return $building;
    }

    /**
     * @return int|\WP_Error
     */
    public function toWordPress(): int
    {
        $title   = $this->title;
        $content = $this->getWordPressContent();
        $content = empty($content) ? 'This building is empty.' : $content;

        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql         = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'area' AND p.post_title = '$title' AND p.post_content = '$content'";
        /** @var \WP_Post $foundBuilding */
        $foundBuilding = $wpdb->get_row($sql);
        if ($foundBuilding) {
            // The Building has been found (not saving another instance but returning the found ID).
            Converter::updateID($this->id, $foundBuilding->ID);
            Map::updateLabel($this->label, $foundBuilding->ID);
            $savedBuildings = $_SESSION['saved_buildings'];
            $savedBuildings[$foundBuilding->ID] = $this;
            $_SESSION['saved_buildings'] = $savedBuildings;
            return $foundBuilding->ID;
        }

        $buildingTerm = term_exists('Building', 'area_type', 0);
        if (!$buildingTerm) {
            $buildingTerm = wp_insert_term('Building', 'area_type', ['parent' => 0]);
        }
        $thisTypeTerm = term_exists(ucfirst($this->type), 'area_type', $buildingTerm['term_taxonomy_id']);
        if (!$thisTypeTerm) {
            switch ($this->type) {
                case 'Merchant':
                    $color = '#aa00ff';
                    break;
                case 'Guardhouse':
                    $color = '#00b0ff';
                    break;
                case 'Church':
                    $color = '#ff6d00';
                    break;
                case 'Guild':
                    $color = '#00c853';
                    break;
                case 'Inn':
                    $color = '#eeff41';
                    break;
                case 'Royal':
                    $color = '#d50000';
                    break;
                case 'House':
                default:
                    $color = '#a0a0a0';
                    break;
            }
            wp_insert_term(ucfirst($this->type), 'area_type', ['description' => $color, 'parent' => $buildingTerm['term_taxonomy_id']]);
        }

        $custom_tax = [
            'area_type' => [
                'Building',
                $this->type,
            ],
        ];

        $wp_id = wp_insert_post(
            [
                'post_title'   => $title,
                'post_content' => $content,
                'post_type'    => 'area',
                'post_status'  => 'publish',
                'tax_input'    => $custom_tax,
            ]
        );
        update_post_meta($wp_id, 'visible_objects', $this->npcs);
        Converter::updateID($this->id, $wp_id);
        Map::updateLabel($this->label, $wp_id);
        $savedBuildings = $_SESSION['saved_buildings'];
        $savedBuildings[$wp_id] = $this;
        $_SESSION['saved_buildings'] = $savedBuildings;
        return $wp_id;
    }

    private function getWordPressContent(): string {
        ob_start();
        /** @var NPC[] $savedNPCs */
        if (isset($_SESSION['saved_npcs'])) {
            $savedNPCs = $_SESSION['saved_npcs'];
        }
        $alwaysShowNPCs = [];
        $occupants = [];
        foreach ($savedNPCs as $npcID => $npc) {
            if (in_array($npcID, $this->npcs)) {
                if ($npc->type === 'owner' || empty($npc->profession)) {
                    $occupants[] = $npcID;
                } else {
                    $alwaysShowNPCs[] = $npcID;
                }
            }
        }
        if ($this->type == 'House') {
            $alwaysShowNPCs = array_merge($alwaysShowNPCs, $occupants);
            $occupants = [];
            echo '<h1>OccupantsA</h1>';
        }
        if (count($occupants) === 1) {
            echo '<h1>OccupantsB</h1>';
            array_unshift($alwaysShowNPCs, array_pop($occupants));
        }
        if (!empty($occupants)) {
            echo '<h1>OccupantsC</h1>';
            echo '<ul class="collapsible" data-collapsible="expandable">';
            foreach ($occupants as $npcID) {
                echo '<li class="collection-item">[npc id="'.$npcID.'" display="li"]</li>';
            }
            echo '</ul>';
        }
        foreach ($alwaysShowNPCs as $npcID) {
            echo '[npc id="'.$npcID.'"]'.PHP_EOL;
        }
        if (!empty($this->products)) {
            echo '<h1>Products</h1>';
            echo '<table class="striped">';
            echo '<tr><th>Product</th><th>Cost</th><th>In Stock</th></tr>';
            foreach ($this->products as $product) {
                $productID = $product->toWordPress();
                echo '<tr><td>[product id="'.$productID.'"]</td><td>'.$product->cost.'</td><td>'.$product->inStock.'</td></tr>';
            }
            echo '</table>';
        }
        if (!empty($this->spells)) {
            echo '<h1>Spells</h1>';
            echo '<table class="striped">';
            echo '<tr><th>Spell</th><th>Cost</th></tr>';
            foreach ($this->spells as $spell) {
                $spellID = $spell->toWordPress();
                echo '<tr><td>[spell id="' . $spellID . '"]</td><td>' . $spell->cost . '</td></tr>';
            }
            echo '</table>';
        }

        if (!empty($this->vaultItems)) {
            echo '<h1>Royal Vault</h1>';
            echo '<ul class="collection collapsible with-header">';
            $gems = [];
            $jewels = [];
            $vaultItems = [];
            /** @var VaultItem $item */
            foreach ($this->vaultItems as $item) {
                switch ($item->type) {
                    case VaultItem::GEM:
                        $gems[] = $item;
                        break;
                    case VaultItem::JEWEL:
                        $jewels[] = $item;
                        break;
                    case VaultItem::OTHER:
                        $vaultItems[] = $item;
                        break;
                }
            }
            foreach (['Gems' => $gems, 'Jewels' => $jewels, 'Other' => $vaultItems] as $header => $vaultItems) {
                if (!empty($vaultItems)) {
                    echo '<li class="collection-header"><h3>'.$header.'</h3></li>';
                    foreach ($vaultItems as $vaultItem) {
                        $title = $vaultItem->title;
                        if ($vaultItem->count > 1) {
                            $title = $vaultItem->count . 'x ' . $title;
                        }
                        if ($vaultItem->cost !== '') {
                            $title .= ' (' . $vaultItem->cost . ')';
                        }
                        if ($vaultItem->description !== '') {
                            ?>
                            <li>
                                <div class="collapsible-header collection-item"><?= $title ?></div>
                                <div class="collapsible-body"><?= $vaultItem->description ?></div>
                            </li>
                            <?php
                        } else {
                            ?><li class="collection-item"><?= $title ?></li><?php
                        }
                    }
                }
            }
            echo '</ul>';
        }
        return ob_get_clean();
    }
}
