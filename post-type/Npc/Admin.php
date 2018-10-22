<?php

namespace mp_dd\PostType\NPC;

use mp_general\base\PostType;
use mp_general\base\Taxonomy;

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
    $postType = new PostType('Npc');
    $postType->addTaxonomy(new Taxonomy('NPC Type'));
    $postType->addTaxonomy(new Taxonomy('NPC Profession'));
    $postType->addTaxonomy(new Taxonomy('NPC Race'));
    $postType->addTaxonomy(new Taxonomy('NPC Class'));
    $postType->addTaxonomy(new Taxonomy('NPC Level'));
    $postType->create();
});
