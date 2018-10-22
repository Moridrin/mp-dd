<?php

namespace mp_dd;

use DOMDocument;
use Exception;
use mp_general\base\BaseFunctions;
use mp_dd\Wizardawn\Models\City;
use mp_dd\Wizardawn\Parser\BuildingParser;
use mp_dd\Wizardawn\Parser\MapParser;
use mp_dd\Wizardawn\Parser\NPCParser;

require_once "Parsers/MapParser.php";
require_once "Parsers/NPCParser.php";
require_once "Parsers/RulersParser.php";
require_once "Parsers/BuildingParser.php";

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 14-6-17
 * Time: 7:15
 */
abstract class Converter extends Parser
{
    /**
     * This function converts a HTML string as generated by the Wizardawn Fantasy Settlements Generator to arrays with all the data in the HTML.
     *
     * @param string $content
     *
     * @return City
     */
    public static function Convert(string $content)
    {
        $content = self::cleanCode($content);
        $content = self::bugFixes($content);
        $html = str_get_html($content);

        $city = new City();
        $city->setTitle($html->getElementByTagName('font')->text());
//        MapParser::parseMap($city, $html);
        BuildingParser::parseRoyalty($city, $html);
        BuildingParser::parseBuildings($city, $html);
        return $city;
    }

    /**
     * This function fixes all bugs in the original generated code from the generated Wizardawn HTML.
     *
     * @param string $content
     *
     * @return string
     */
    private static function bugFixes($content)
    {
        $file = new DOMDocument();
        libxml_use_internal_errors(true);
        $file->loadHTML($content);
        $body         = $file->getElementsByTagName('body')->item(0);
        $baseElements = $body->childNodes;
        for ($i = 0; $i < $baseElements->length; $i++) {
            $html = $file->saveHTML($baseElements->item($i));
            if (strpos($html, 'wtown_01.jpg') !== false) {
                $badCode = trim($file->saveHTML($baseElements->item($i + 2)->childNodes->item(0)));
            }
        }
        if (isset($badCode)) {
            $html = $file->saveHTML();
            $html = str_replace($badCode, $badCode . '</font>', $html);
            $file->loadHTML($html);
        }
        return self::cleanCode($file->saveHTML());
    }

    public static function updateID($id, $wp_id)
    {
        /** @var City $city */
        $city = $_SESSION['city'];
        if (!$city->replaceID($id, $wp_id)) {
            throw new Exception('WordPress ID not changed in City Object');
        }
        $_SESSION['city'] = $city;
    }
}
