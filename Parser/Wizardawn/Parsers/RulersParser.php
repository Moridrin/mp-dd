<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-6-17
 * Time: 20:58
 */

namespace mp_dd\Wizardawn\Parser;

use DOMDocument;
use mp_dd\Parser;

class RulersParser extends Parser
{
    private function __construct()
    {
    }

    /**
     * This function parses the Rulers of the city and adds them to the ruler building.
     *
     * @param string|null $basePart is the HTML as a string or null if the base doesn't have to be parsed.
     *
     * @return array of all the NPCs as arrays of data.
     */
    public static function parseRulersBuilding($basePart = null)
    {
        $rulersBuilding = array('id' => 0, 'npcs' => array());
        $part           = self::cleanCode($basePart);
        $file           = new DOMDocument();
        libxml_use_internal_errors(true);
        $file->loadHTML($part);

        $parts  = $file->getElementsByTagName('font');
        $parser = NPCParser::getParser();
        for ($i = 1; $i < $parts->length; $i++) {
            $html = $parts->item($i);
            if ($html->childNodes->item(1)->firstChild->textContent == 'ROYAL VAULT:') {
                $rulersBuilding['vault'] = explode('---', trim($html->childNodes->item(2)->textContent));
            } else {
                $rulersBuilding['npcs'][] = $parser->parseBuildingNPC($html, 0, 'royalty');
            }
        }

        return $rulersBuilding;
    }

    /**
     * @param array  $rulersBuilding
     * @param string $city
     *
     * @return array
     */
    public static function toWordPress(&$rulersBuilding, $city)
    {
        $rulersBuilding['city'] = $city;
        $buildingType           = 'Rulers';
        $rulersBuilding['type'] = $buildingType;
        $buildingTitle          = $city . ' Rulers';

        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = '$buildingTitle'";
        /** @var \WP_Post $foundBuilding */
        $foundBuilding = $wpdb->get_row($sql);
        if ($foundBuilding) {
            $building['wp_id'] = $foundBuilding->ID;
            return $building;
        }

        $buildingTypeTerm = term_exists($buildingType, 'building_category', 0);
        if (!$buildingTypeTerm) {
            $buildingTypeTerm = wp_insert_term($buildingType, 'building_category', array('parent' => 0));
        }

        $custom_tax = array(
            'building_category' => array(
                $buildingTypeTerm['term_taxonomy_id'],
            ),
        );

        $postID = wp_insert_post(
            array(
                'post_title'   => $buildingTitle,
                'post_content' => self::toHTML($rulersBuilding),
                'post_type'    => 'building',
                'post_status'  => 'publish',
                'tax_input'    => $custom_tax,
            )
        );
        foreach ($rulersBuilding as $key => $value) {
            if ($key == 'title' || $key == 'products' || $key == 'html') {
                continue;
            }
            update_post_meta($postID, $key, $value);
        }
        $rulersBuilding['wp_id'] = $postID;
        return $rulersBuilding;
    }

    public static function toHTML($building)
    {
        $html = '';
        if (isset($building['npcs'])) {
            foreach ($building['npcs'] as $npc) {
                NPCParser::toWordPress($npc);
                $npcID = $npc['wp_id'];
                $html  .= "[npc-$npcID]";
            }
        }
        if (isset($building['vault'])) {
            $html .= '<hr/>';
            $html .= '<h2>Vault</h2>';
            $html .= '<ul class="browser-default">';
            foreach ($building['vault'] as $item) {
                $html .= "<li>$item</li>";
            }
            $html .= '</ul>';
        }
        return self::cleanCode($html);
    }
}
