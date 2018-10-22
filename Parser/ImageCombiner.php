<?php

namespace mp_dd\Parser;

use mp_dd\MP_DD;
use mp_dd\Parser;
use mp_general\base\BaseFunctions;

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 18-7-17
 * Time: 6:36
 */
class ImageCombiner
{
    public static function convertToSingle(array $srcImagePaths, $mapWidth)
    {
        $rowWidth  = 0;
        $mapHeight = 0;
        $images    = [];
        foreach ($srcImagePaths as $index => $srcImagePath) {
            if (BaseFunctions::endsWith($srcImagePath, '.gif')) {
                $tileImg = imagecreatefromgif($srcImagePath);
            } elseif (BaseFunctions::endsWith($srcImagePath, '.jpg')) {
                $tileImg = imagecreatefromjpeg($srcImagePath);
            } else {
                $tileImg = imagecreatefrompng($srcImagePath);
            }
            list($width, $height) = getimagesize($srcImagePath);
            $images[] = [
                'image'  => $tileImg,
                'width'  => $width,
                'height' => $height,
                'x'      => $rowWidth,
                'y'      => $mapHeight,
            ];
            $rowWidth += $width;
            if ($rowWidth >= $mapWidth) {
                $rowWidth  = 0;
                $mapHeight += $height;
            }
        }

        $mapImage = imagecreatetruecolor($mapWidth, $mapHeight);
        $bgColor  = imagecolorallocate($mapImage, 0, 0, 0);
        imagefill($mapImage, 0, 0, $bgColor);

        foreach ($images as $image) {
            imagecopy($mapImage, $image['image'], $image['x'], $image['y'], 0, 0, $image['width'], $image['height']);
            imagedestroy($image['image']);
        }

        imagepng($mapImage, MP_DD::PATH . 'Parser/tmp.png');
        return MP_DD::URL . 'Parser/tmp.png';
    }
}
