<?php

namespace mp_dd\Wizardawn\Models;

class VaultItem extends JsonObject
{
    const GEM = 'gem';
    const JEWEL = 'jewel';
    const OTHER = 'other';

    public $type;
    public $title;
    public $cost;
    public $count;
    public $description;

    public function __construct(string $type, string $title, string $description, string $cost, int $count)
    {
        parent::__construct();
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->cost = $cost;
        $this->count = $count;
    }

    public static function getFromArray(array $array)
    {
        $products = [];
        for ($i = 0; $i < count($array['title']); ++$i) {
            $product = new VaultItem($array['type'][$i], $array['title'][$i], $array['description'][$i], $array['cost'][$i], intval($array['count'][$i]));
            $products[$product->id] = $product;
        }
        return $products;
    }

    public function toWordPress(): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $title = $this->title;
        $sql   = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'object' AND p.post_title = '$title'";
        /** @var \WP_Post $foundNPC */
        $foundProduct = $wpdb->get_row($sql);
        if ($foundProduct) {
            // The NPC has been found (not saving another instance but returning the found ID).
            return $foundProduct->ID;
        }

        $thisTypeTerm = term_exists($this->type, 'object_type', 0);
        if (!$thisTypeTerm) {
            wp_insert_term($this->type, 'object_type', ['parent' => 0]);
        }

        $custom_tax = [
            'object_type' => [
                $this->type,
            ],
        ];

        return wp_insert_post(
            [
                'post_title'   => $title,
                'post_content' => $this->description,
                'post_type'    => 'object',
                'post_status'  => 'publish',
                'tax_input'    => $custom_tax,
            ]
        );
    }
}
