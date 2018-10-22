<?php

namespace mp_dd\PostType\Area;

use mp_general\base\PostType;
use mp_general\base\Taxonomy;

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
    $postType = new PostType('Area');
    $postType->addTaxonomy(new Taxonomy('Area Type'));
    $postType->create();
});
