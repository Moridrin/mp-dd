<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 22:32
 */
class Weapon extends Item
{
    const TYPE = 'weapon';

    public $martial;
    public $damage;
    public $damageType;
    public $properties;

    protected function __construct($title, $martial, $damage, $damageType, $properties)
    {
        parent::__construct($title, self::TYPE);
        $this->martial    = $martial;
        $this->damage     = $damage;
        $this->damageType = $damageType;
        $this->properties = $properties;
    }

    public static function fromPOST($index)
    {
        $title      = $_POST['item_' . $index . '_title'];
        $martial    = isset($_POST['item_' . $index . '_martial']);
        $damage     = $_POST['item_' . $index . '_damage'];
        $damageType = $_POST['item_' . $index . '_damage_type'];
        $properties = $_POST['item_' . $index . '_properties'];
        return new Weapon($title, $martial, $damage, $damageType, $properties);
    }

    public static function fromObject($object)
    {
        $title      = $object->title;
        $martial    = $object->martial;
        $damage     = $object->damage;
        $damageType = $object->damageType;
        $properties = $object->properties;
        return new Weapon($title, $martial, $damage, $damageType, $properties);
    }
}
