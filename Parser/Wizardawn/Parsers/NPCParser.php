<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-6-17
 * Time: 20:58
 */

namespace mp_dd\Wizardawn\Parser;

use mp_general\base\BaseFunctions;
use simple_html_dom;
use simple_html_dom_node;
use mp_dd\Parser;
use mp_dd\Wizardawn\Models\Building;
use mp_dd\Wizardawn\Models\City;
use mp_dd\Wizardawn\Models\NPC;

class NPCParser extends Parser
{

    private static $typeMap
        = [
            '-'   => 'owner',
            '--'  => 'spouse',
            '---' => 'child',
        ];

    public static function parseNPC(simple_html_dom_node $node, $type = null): NPC
    {
        $npc  = new NPC();
        if ($type !== null) {
            $npc->profession = explode(':', ucfirst(strtolower($node->childNodes(0)->text())))[0];
            $info            = explode(' ', $node->childNodes(1)->text());
            $npc->level      = $info[1];
            $npc->class      = explode(']', $info[2])[0];
            $node            = $node->removeChild(0, 1);
            $npc->type       = $type;
        } else {
            $type = explode('<b>', $node->innertext())[0];
            $npc->type = self::$typeMap[$type];
            if ($npc->type === 'owner') {
                $npc->profession = 'Store Owner';
            }
        }
        $npc->name = str_replace(':', '', $node->firstChild()->text());
        list($npc->height, $npc->weight) = self::parsePhysique($node);
        $npc->description = self::parseDescription($node);
        $npc->race        = self::parseRaceFromDescription($npc->description);
        $npc->clothing    = self::parseClothing($node);
        $npc->possessions = self::parsePossessions($node);
        $npc->arms_armor  = self::parseArmsAndArmor($node);
        return $npc;
    }

    private static function parsePhysique(simple_html_dom_node $node): array
    {
        if (preg_match("/\[<b>HGT:<\/b>(.*?)<b>WGT:<\/b>(.*?)\]/", $node->innertext(), $physique)) {
            $height = 0;
            $weight = 0;
            if (preg_match("/(.*?)ft/", $physique[1], $feet)) {
                $height += intval($feet[1]) * 30.48;
            }
            if (preg_match("/, (.*?)in/", $physique[1], $inches)) {
                $height += intval($inches[1]) * 2.54;
            }
            if (preg_match("/(.*?)lbs/", $physique[2], $pounds)) {
                $weight = intval($pounds[1]) * 0.453592;
            }
            return [intval(round($height, 0)), intval(round($weight, 0))];
        }
        return [];
    }

    private static function parseDescription(simple_html_dom_node $node): string
    {
        $description = explode($node->childNodes(2)->outertext(), $node->innertext())[1];
        $description = explode(']', $description)[1];
        $description = explode($node->childNodes(3)->outertext(), $description)[0];
        return trim($description);
    }

    private static function parseClothing(simple_html_dom_node $node): string
    {
        $clothing = explode($node->childNodes(3)->outertext(), $node->innertext())[1];
        return trim(explode($node->childNodes(4)->outertext(), $clothing)[0]);
    }

    private static function parsePossessions(simple_html_dom_node $node): string
    {
        $possessions = explode($node->childNodes(4)->outertext(), $node->innertext())[1];
        if ($node->childNodes(5) !== null) {
            $possessions = trim(explode($node->childNodes(5)->outertext(), $possessions)[0]);
        }
        return $possessions;
    }

    private static function parseArmsAndArmor(simple_html_dom_node $node): string
    {
        if ($node->childNodes(5) !== null) {
            return trim(explode($node->childNodes(5)->outertext(), $node->innertext())[1]);
        }
        return '';
    }

    private static function parseRaceFromDescription(string $description): string
    {
        if (BaseFunctions::startsWith($description, 'He')) {
            return explode(' ', substr($description, strpos($description, 'male') + 5))[0];
        } else {
            return explode(' ', substr($description, strpos($description, 'female') + 7))[0];
        }
    }
}
