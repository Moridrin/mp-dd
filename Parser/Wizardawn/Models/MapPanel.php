<?php

namespace mp_dd\Wizardawn\Models;

class MapPanel extends JsonObject
{
    protected $imageURL;
    /** @var MapLabel[] */
    protected $labels = [];

    public function __construct($imageURL)
    {
        parent::__construct();
        $this->imageURL = $imageURL;
    }

    public function addLabel(MapLabel $label)
    {
        $this->labels[] = $label;
    }
}
