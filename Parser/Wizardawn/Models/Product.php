<?php

namespace mp_dd\Wizardawn\Models;

class Product extends JsonObject
{
    public $name;
    public $cost;
    public $inStock;

    public function __construct(string $name, string $cost, int $inStock, string $description = '')
    {
        parent::__construct();
        $this->name = $name;
        $this->cost = $cost;
        $this->inStock = $inStock;
    }

    public static function getFromArray(array $array)
    {
        $products = [];
        for ($i = 0; $i < count($array['name']); ++$i) {
            $product = new Product($array['name'][$i], $array['cost'][$i], $array['in_stock'][$i], $array['description'][$i]);
            $products[$product->id] = $product;
        }
        return $products;
    }

    public function toWordPress(): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $title = $this->name;
        $sql   = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'object' AND p.post_title = '$title'";
        /** @var \WP_Post $foundNPC */
        $foundProduct = $wpdb->get_row($sql);
        if ($foundProduct) {
            // The NPC has been found (not saving another instance but returning the found ID).
            return $foundProduct->ID;
        }

        $thisTypeTerm = term_exists('Product', 'object_type', 0);
        if (!$thisTypeTerm) {
            wp_insert_term('Product', 'object_type', ['parent' => 0]);
        }

        $custom_tax = [
            'object_type' => [
                'Product',
            ],
        ];

        return wp_insert_post(
            [
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'object',
                'post_status'  => 'publish',
                'tax_input'    => $custom_tax,
            ]
        );
    }
}
