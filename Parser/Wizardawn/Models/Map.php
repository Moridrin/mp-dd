<?php

namespace mp_dd\Wizardawn\Models;

use Exception;

class Map extends JsonObject
{
    /** @var MapLabel[] */
    protected $labels = [];
    protected $width = 500;
    protected $image = null;

    public function addLabel(MapLabel $label)
    {
        $this->labels[$label->getID()] = $label;
    }

    public static function updateLabel($label, $wp_id)
    {
        /** @var City $city */
        $city = $_SESSION['city'];
        if (!$city->hasMap()) {
            return;
        }
        $map = $city->getMap();
        foreach ($map->labels as &$mapLabel) {
            if ($mapLabel->buildingID == $label) {
                $mapLabel->buildingID = $wp_id;
                $mapLabel->visible = true;
            }
        }
        $city->setMap($map);
        $_SESSION['city'] = $city;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    public function setImage(string $image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function updateFromPOST()
    {
        foreach ($this->labels as &$mapLabel) {
            if ($mapLabel->visible) {
                $translate = $_POST['label_translations'][$mapLabel->buildingID];
                preg_match("/\((.*?)p?x?, (.*?)p?x?\)/", $translate, $matches);
                list($original, $left, $top) = $matches;
                $mapLabel->left += $left;
                $mapLabel->top += $top;
            }
        }
    }

    /**
     * @param array[] $buildings
     * @param string  $title
     *
     * @return int postID
     */
    public function toWordPress($buildings, $title)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        /** @var \WP_Post $foundMap */
        $foundMap = $wpdb->get_row("SELECT p.ID FROM $wpdb->posts AS p WHERE p.post_type = 'map' AND p.post_title = '$title'");
        if ($foundMap) {
            $this->id = $foundMap->ID;
            return $this->id;
        }

        $widthImageCount = ((($this->width - 100) - 300) / 300) + 2;
        $xModifier       = 0;
        $yModifier       = 0;
        $xImage          = 1;
        $yImage          = 1;
        $buildingLabels  = array();
        foreach ($this->labels as &$panel) {
            foreach ($panel['building_labels'] as &$buildingLabel) { //TODO BuildingLabels to Visible Objects
                if (isset($buildings[$buildingLabel['id']])) {
                    $building                 = $buildings[$buildingLabel['id']];
                    $buildingLabel['left']    += $xModifier;
                    $buildingLabel['top']     += $yModifier;
                    $buildingLabel['showing'] = true;
                    if (isset($building['wp_id'])) {
                        $buildingLabel['wp_id'] = $building['wp_id'];
                    }
                    switch ($building['type']) {
                        case 'merchants':
                            $buildingLabel['color'] = '#6a1b9a';
                            break;
                        case 'guardhouses':
                            $buildingLabel['color'] = '#1976d2';
                            break;
                        case 'churches':
                            $buildingLabel['color'] = '#d50000';
                            break;
                        case 'guilds':
                            $buildingLabel['color'] = '#1b5e20';
                            break;
                        default:
                            $buildingLabel['color'] = '#000000';
                            break;
                    }
                    $buildingLabels[] = array(
                        'id'      => $buildingLabel['wp_id'],
                        'color'   => $buildingLabel['color'],
                        'showing' => $buildingLabel['showing'],
                        'label'   => $buildingLabel['id'],
                        'top'     => $buildingLabel['top'],
                        'left'    => $buildingLabel['left'],
                    );
                }
            }

            if ($xImage == 1) {
                $xModifier += 150;
            } else {
                $xModifier += 300;
            }

            $xImage++;
            if ($xImage > $widthImageCount) {
                $xModifier = 0;
                $xImage    = 1;
                if ($yImage == 1) {
                    $yModifier += 150;
                } else {
                    $yModifier += 300;
                }
                $yImage++;
            }
        }
        $postID = wp_insert_post(
            array(
                'post_title'   => $title,
                'post_content' => $this->toHTML(),
                'post_type'    => 'map',
                'post_status'  => 'publish',
            )
        );
        update_post_meta($postID, 'building_labels', $buildingLabels);
        $map['wp_id'] = $postID;
        return $postID;
    }

    private function toHTML() //TODO Fix it so that the images are joined into one and uploaded. @see https://diceattack.wordpress.com/2011/01/03/combining-multiple-images-using-php-and-gd/
    {
        $zIndex = count($this->labels);
        ob_start();
        ?>
        <div style="overflow-x: auto; overflow-y: hidden;">
            <div id="map" style="width: <?= $this->width ?>px; margin: auto; position: relative">
                <?php foreach ($this->labels as $panel): ?>
                    <div style="display: inline-block; position:relative; padding: 0; z-index: <?= $zIndex ?>;">
                        <img src="http://wizardawn.and-mag.com/maps/<?= $panel['image'] ?>">
                    </div>
                    <?php --$zIndex; ?>
                <?php endforeach; ?>
                [building-labels]
            </div>
        </div>
        <div class="row">
            <div class="col s12"><h2>Legend</h2></div>
            <div class="col s6 m2" style="background-color: #000000; color: #FFFFFF;border: 3px solid black;">House</div>
            <div class="col s6 m2" style="background-color: #6a1b9a; color: #FFFFFF;border: 3px solid black;">Merchant</div>
            <div class="col s6 m2" style="background-color: #1976d2; color: #FFFFFF;border: 3px solid black;">Guardhouse</div>
            <div class="col s6 m2" style="background-color: #d50000; color: #FFFFFF;border: 3px solid black;">Church</div>
            <div class="col s6 m2" style="background-color: #1b5e20; color: #FFFFFF;border: 3px solid black;">Guild</div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getHTML()
    {
        ob_start();
        ?>
        <div style="overflow-x: auto; overflow-y: hidden;">
            <div id="map" style="margin: auto; position: relative">
                <img id="map_image" src="<?= $this->image ?>"/>
                <?php $number = 1; ?>
                <?php foreach ($this->labels as $mapLabel): ?>
                    <?php if ($mapLabel->visible): ?>
                        <aside draggable="true" class="mp-draggable area-label" style="left: <?= $mapLabel->left-13 ?>px; top: <?= $mapLabel->top-10 ?>px">
                            <?= $number ?>
                            <input type="hidden" name="label_translations[<?= $mapLabel->buildingID ?>]" value="translate(0px, 0px)">
                        </aside>
                        <?php ++$number; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getVisibleBuildings()
    {
        $visibleLabels = array_filter($this->labels, function ($mapLabel) {
            return $mapLabel->visible;
        });
        return array_column($visibleLabels, 'buildingID');
    }

    public function getLabelTranslations()
    {
        $labelTranslations = [];
        foreach ($this->labels as $mapLabel) {
            $labelTranslations[$mapLabel->buildingID] = [$mapLabel->left - 13, $mapLabel->top - 10];
        }
        return $labelTranslations;
    }
}
