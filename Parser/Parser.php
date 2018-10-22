<?php

namespace mp_dd;

class Parser
{
    const REMOVE_HTML = [
        '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
        '<html>',
        '</html>',
        '<body>',
        '</body>',
        '&Atilde;',
        '&#130;',
        '&Acirc;',
        '&nbsp;',
        '&#13;',
    ];

    /**
     * This function converts to a UTF-8 string, removes all redundant spaces, tabs, etc. and returns all usable code after the closing head tag.
     *
     * @param string $html
     *
     * @return string
     */
    protected static function cleanCode($html)
    {
        $html = str_replace(Parser::REMOVE_HTML, '', $html);
        $html = preg_replace('!\s+!', ' ', $html);
        $html = iconv("UTF-8", "UTF-8//IGNORE", utf8_decode($html));
        $html = str_replace('> <', '><', $html);
        $html = trim(preg_replace('/.*<\/head>/', '', $html));
        return $html;
    }

    /**
     * This function fixes some last issues such as image URLs, '<font>' blocks are replaced with '<span>' blocks, etc.
     *
     * @param string $part
     *
     * @return string
     */
    protected static function finalizePart($part)
    {
        $part = self::cleanCode($part);
        $file = new \DOMDocument();
        libxml_use_internal_errors(true);
        $file->loadHTML($part);

        $images = $file->getElementsByTagName('img');
        foreach ($images as $image) {
            $imageStart = self::cleanCode($file->saveHTML($image));
            if (strpos($imageStart, 'wizardawn.and-mag.com') === false) {
                $imageNew = self::cleanCode(preg_replace('/.\/[\s\S]+?\//', 'http://wizardawn.and-mag.com/maps/', $imageStart));
                $part     = str_replace($imageStart, $imageNew, $part);
            }
        }
        $part = preg_replace("/<font.*?>(.*?)<\/font>/", "<span>$1</span>", $part);
        return self::cleanCode($part);
    }
}
