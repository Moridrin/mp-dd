<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 3-4-17
 * Time: 19:31
 */
abstract class DonjonConverter
{
    /** @var DOMDocument $file */
    private static $file;

    private static $map;
    private static $info;
    private static $rooms;

    public static function Convert($file)
    {
        self::$file = new DOMDocument();

        libxml_use_internal_errors(true);
        self::$file->loadHTMLFile($file);

        self::parseMap();
        self::parseInfo();
        self::parseRooms();

        return array(
            'map'   => self::$map,
            'info'  => self::$info,
            'rooms' => self::$rooms,
        );
    }

    private static function parseMap()
    {
        $file    = self::$file;
        $finder  = new DomXPath($file);
        $mapNode = $finder->query("//*[contains(@class, 'map')]")->item(0);
        $mapNode->setAttribute('class', $mapNode->getAttribute('class') . ' center-align');
        $map        = $file->saveHTML($mapNode);
        $legendNode = $finder->query("//*[contains(@class, 'legend')]")->item(0);
        if ($legendNode) {
            $legendNode->setAttribute('class', $legendNode->getAttribute('class') . ' center-align');
            $map .= $file->saveHTML($legendNode);
        }
        $linksNode    = $file->getElementById('dungeon');
        $newLinksNode = $file->createElement('map');
        $newLinksNode->setAttribute('id', 'dungeon');
        $newLinksNode->setAttribute('name', 'dungeon');
        /** @var DOMElement $childNode */
        for ($i = 0; $i < $linksNode->childNodes->length; $i++) {
            $childNode = $linksNode->childNodes->item($i);
            $href      = $childNode->getAttribute('href');
            if (!empty($href)) {
                if (!is_numeric(substr($href, 1))) {
                    $href = '#corridors';
                } else {
                    $href = '#room' . substr($href, 1);
                }
                $newChildNode = $childNode->cloneNode();
                $newChildNode->removeAttribute('href');
                $newLinkNode = $file->createElement('a');
                $newLinkNode->setAttribute('href', $href);
                $newLinkNode->appendChild($newChildNode);
                $newLinksNode->appendChild($newLinkNode);
            } else {
                $newLinksNode->appendChild($childNode);
            }
        }
        $map       .= $file->saveHTML($newLinksNode);
        self::$map = $map;
    }

    private static function parseInfo()
    {
        $file        = self::$file;
        $finder      = new DomXPath($file);
        $tableNode   = $finder->query("//*[contains(@class, 'stats standard')]")->item(0);
        $icon        = '';
        $name        = '';
        $content     = '';
        $collapsible = '<ul class="collapsible" id="test" data-collapsible="expandable">';
        /** @var DOMNode $trNode */
        foreach ($tableNode->childNodes as $trNode) {
            /** @var DOMNode $tdNode */
            foreach ($trNode->childNodes as $tdNode) {
                if ($tdNode->nodeType == XML_ELEMENT_NODE) {
                    if (empty($name)) {
                        if ($tdNode->firstChild->nodeType == 3) {
                            $name = trim($file->saveHTML($tdNode->firstChild));
                        } else {
                            /** @var DOMElement $firstChild */
                            $firstChild = $tdNode->firstChild;
                            $name       = 'room' . $firstChild->getAttribute('id');
                        }
                        switch ($name) {
                            case 'General':
                            default:
                                $icon = 'account_balance';
                                break;
                            case 'Wandering':
                                $icon = 'pets';
                                break;
                        }
                    } else {
                        $innerHTML = "";
                        foreach ($tdNode->childNodes as $child) {
                            $innerHTML .= trim($file->saveHTML($child));
                        }
                        $content = $innerHTML;
                    }
                }

                if (!empty($name) && !empty($content)) {
                    if ($name == 'General' || $name == 'Wandering') {
                        ob_start();
                        ?>
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons"><?= $icon ?></i><?= $name ?></div>
                            <div class="collapsible-body">
                                <?= $content ?>
                            </div>
                        </li>
                        <?php
                        $collapsible .= ob_get_clean();
                    }
                    $name    = '';
                    $content = '';
                }
            }
        }
        $collapsible .= '</ul>';
        self::$info  .= $collapsible;
    }

    private static function parseRooms()
    {
        $file      = self::$file;
        $finder    = new DomXPath($file);
        $tableNode = $finder->query("//*[contains(@class, 'stats standard')]")->item(0);
        $name      = '';
        $content   = '';
        /** @var DOMNode $trNode */
        foreach ($tableNode->childNodes as $trNode) {
            /** @var DOMNode $tdNode */
            foreach ($trNode->childNodes as $tdNode) {
                if ($tdNode->nodeType == XML_ELEMENT_NODE) {
                    if (empty($name)) {
                        if ($tdNode->firstChild->nodeType == 3) {
                            $name = trim($file->saveHTML($tdNode->firstChild));
                        } else {
                            $name = 'room' . $tdNode->firstChild->getAttribute('id');
                        }
                    } else {
                        $innerHTML = "";
                        foreach ($tdNode->childNodes as $child) {
                            $innerHTML .= trim($file->saveHTML($child));
                        }
                        $content = $innerHTML;
                    }
                }

                if (!empty($name) && !empty($content)) {
                    if ($name != 'General' && $name != 'Wandering') {
                        ob_start();
                        ?>
                        <div class="modal" id="<?= strtolower($name) ?>">
                            <div class="modal-content">
                                <?= $content ?>
                            </div>
                        </div>
                        <?php
                        self::$rooms .= ob_get_clean();
                    }
                    $name    = '';
                    $content = '';
                }
            }
        }
    }
}
