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

    protected function __construct()
    {
        parent::__construct();
    }
}
