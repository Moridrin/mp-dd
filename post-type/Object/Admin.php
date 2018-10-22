<?php

namespace mp_dd\PostType\Product;

use mp_general\base\PostType;
use mp_general\base\Taxonomy;

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
    $postType = new PostType('Object');
    $postType->addTaxonomy(new Taxonomy('Object Type'));
    $postType->create();
});
