<?php

namespace mp_dd\Wizardawn\Models;

class MapLabel extends JsonObject
{
    public $buildingID;
    public $visible;
    public $left;
    public $top;

    public function __construct(int $buildingID, int $left, int $top)
    {
        parent::__construct();
        $this->buildingID = $buildingID;
        $this->left = $left;
        $this->top = $top;
    }
}
