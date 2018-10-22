<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-6-17
 * Time: 20:58
 */

namespace mp_dd\Wizardawn\Parser;

use mp_dd\Parser\ImageCombiner;
use simple_html_dom;
use simple_html_dom_node;
use mp_dd\Parser;
use mp_dd\Wizardawn\Models\City;
use mp_dd\Wizardawn\Models\Map;
use mp_dd\Wizardawn\Models\MapLabel;

class MapParser extends Parser
{
    private function __construct()
    {
    }

    /**
     * This function parses the Map and adds links to the modals.
     *
     * @param City $city
     * @param simple_html_dom $body
     */
    public static function parseMap(City &$city, simple_html_dom $body)
    {
        $html = $body->getElementById('myMap');
        if ($html === null) {
            return;
        }
        $map   = new Map();
        $width = $html->getAttribute("style");
        preg_match('/width: (.*?)px/', $width, $width);
        $mapWidth = ($width[1] - 5) + 100;
        $map->setWidth($mapWidth);
        $srcImagePaths = [];
        foreach ($html->children() as $panelElement) {
            $image    = $panelElement->getElementByTagName('img');
            preg_match('/\/[\s\S]+?\/([\s\S]+?)"/', (string)$image, $image);
            $image = $image[1];
            $isKeep = BaseFunctions::endsWith($image, '.jpg');
            self::parsePanel($map, $panelElement, $isKeep);
            $srcImagePaths[] = 'http://wizardawn.and-mag.com/maps/'.$image;
        }
        $map->setImage(ImageCombiner::convertToSingle($srcImagePaths, $mapWidth - 100));
        $city->setMap($map);
    }

    private static function parsePanel(Map &$map, simple_html_dom_node $panelElement, $isKeep)
    {
        $style = $panelElement->getAttribute("style");
        preg_match("/top:([0-9]+)px/", $style, $topTranslation);
        $topTranslation = $topTranslation[1] - 10;
        preg_match("/left:([0-9]+)px/", $style, $leftTranslation);
        $leftTranslation = $leftTranslation[1] - 10;

        /** @var simple_html_dom_node[] $elements */
        $elements = $panelElement->getElementsByTagName('div');

        /** @var simple_html_dom_node $panelBuilding */
        foreach ($elements as $panelBuilding) {
            $panelBuildingNumber = $panelBuilding->text();
            if (is_numeric($panelBuildingNumber)) {
                $style = $panelBuilding->getAttribute("style");
                preg_match("/top:([0-9]+)px/", $style, $top);
                preg_match("/left:([0-9]+)px/", $style, $left);
                $map->addLabel(new MapLabel((int)$panelBuildingNumber, $left[1] + $leftTranslation, $top[1] + $topTranslation));
            }
        }
        if ($isKeep) {
            $map->addLabel(new MapLabel(-1, $leftTranslation + 150, $topTranslation + 150));
        }
    }
}
