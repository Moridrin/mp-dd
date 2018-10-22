<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-6-18
 * Time: 8:33
 */

namespace mp_dd\models;


interface Creature
{
    public function getId(): int;

    public function getInitiative(): ?int;

    public function getMaxHp(): ?int;

    public function getCurrentHp(): ?int;

    public function getName(): string;

    public function addDamage(int $damage): bool;

    public function save(): bool;

    public function getUrl(): ?string;
}
