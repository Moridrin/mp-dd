<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 22:35
 */
class Armor extends Item
{
    const TYPE = 'armor';

    public $armorClass;
    public $properties;

    protected function __construct($title, $armorClass, $properties)
    {
        parent::__construct($title, self::TYPE);
        $this->armorClass = $armorClass;
        $this->properties = $properties;
    }

    public static function fromPOST($index)
    {
        $title      = $_POST['item_' . $index . '_title'];
        $armorClass = $_POST['item_' . $index . '_armor_class'];
        $properties = $_POST['item_' . $index . '_properties'];
        return new Armor($title, $armorClass, $properties);
    }

    public static function fromObject($object)
    {
        $title      = $object->title;
        $armorClass = $object->armorClass;
        $properties = $object->properties;
        return new Armor($title, $armorClass, $properties);
    }
}
