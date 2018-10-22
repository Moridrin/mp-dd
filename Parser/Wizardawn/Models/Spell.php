<?php

namespace mp_dd\Wizardawn\Models;


class Spell extends JsonObject
{
    public $spell;
    public $cost;

    public function __construct(string $spell, string $cost)
    {
        parent::__construct();
        $this->spell = $spell;
        $this->cost = $cost;
    }

    public static function getFromArray($array)
    {
        $spells = [];
        for ($i = 0; $i < count($array['spell']); ++$i) {
            $spell = new Spell($array['spell'][$i], $array['cost'][$i]);
            $spells[$spell->id] = $spell;
        }
        return $spells;
    }

    public function toWordPress(): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $title = $this->spell;
        $sql   = "SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'object' AND p.post_title = '$title'";
        /** @var \WP_Post $foundNPC */
        $foundSpell = $wpdb->get_row($sql);
        if ($foundSpell) {
            // The Spell has been found (not saving another instance but returning the found ID).
            return $foundSpell->ID;
        }

        $thisTypeTerm = term_exists('Spell', 'object_type', 0);
        if (!$thisTypeTerm) {
            wp_insert_term('Spell', 'object_type', ['parent' => 0]);
        }

        $custom_tax = [
            'object_type' => [
                'Spell',
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
