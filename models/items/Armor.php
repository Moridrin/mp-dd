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

    protected function __construct()
    {
        parent::__construct();
    }
}
