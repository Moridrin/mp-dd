<?php

add_filter('the_content', 'mp_dd_filter_content');
function mp_dd_filter_content($content)
{
    global $post;
    return mp_dd_filter_object_content($post->ID, $content, false);
}

function mp_dd_filter_object_content(int $postID, string $content = null, bool $inModal = true)
{
    $post = get_post($postID);
    if ($content == null) {
        $content = $post->post_content;
    }
    if ($post->post_type == 'area') {
        if (strpos($content, '[map]') === false && !empty(get_post_meta($post->ID, 'map_image_id', true))) {
            $content = '[map]'.$content;
        }
        if (strpos($content, '[map]') !== false) {
            $content = str_replace('[map]', $inModal ? '' : mp_dd_get_map($post), $content);
        }
    }
    if ($post->post_type == 'npc') {
        $content = mp_dd_get_npc_content($postID);
    }
    $content = mp_dd_filter_object_tags($content);
    $content = mp_dd_filter_product_tags($content);
    $content = mp_dd_filter_spell_tags($content);
    return $content;
}

#region Map
function mp_dd_get_map(WP_Post $post): string
{
    $image_id = get_post_meta($post->ID, 'map_image_id', true);
    if (empty($image_id)) {
        return '';
    }
    $image_src = wp_get_attachment_url($image_id);
    $visibleObjects = get_post_meta($post->ID, 'visible_objects', true);
    $visibleObjects = is_array($visibleObjects) ? $visibleObjects : [];
    $labelTranslations = get_post_meta($post->ID, 'label_translations', true);
    $labelTranslations = is_array($labelTranslations) ? $labelTranslations : [];
    ob_start();
    ?>
    <div style="overflow-x: auto; overflow-y: hidden;">
        <div id="map" style="width: <?= getimagesize($image_src)[0] ?>px;margin: auto; position: relative">
            <img id="map_image" src="<?= $image_src ?>" class="materialboxed" />
            <?php $number = 1; ?>
            <?php $terms = []; ?>
            <?php foreach ($visibleObjects as $visibleObject): ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'area_type')); ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'npc_type')); ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'object_type')); ?>
                <?php $labelColor = mp_dd_get_area_label_color(wp_get_post_terms($visibleObject, 'area_type')); ?>
                <?php list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
                <a href="[object-<?= $visibleObject ?>-url]" class="area-label"
                   style="left: <?= $left + 3 ?>px; top: <?= $top + 3 ?>px; border: 3px solid <?= $labelColor ?>;"><?= $number ?></a>
                <?php ++$number; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="legend" class="row">
        <?php foreach (array_unique($terms, SORT_REGULAR) as $term): ?>
            <?php $color = ctype_xdigit($term->description) ? '#A0A0A0' : $term->description; ?>
            <div class="col s12 m3 area-label-legend" style="border: 3px solid <?= $color ?>"><?= $term->name ?></div>
        <?php endforeach; ?>
    </div>
    <script>
        window.onload = function() {
            var legend = document.getElementById('legend');
            var legendItems = legend.getElementsByTagName('div');
            var legendItem;
            var showing = 'all';
            for (var i = 0; i < legendItems.length; ++i) {
                legendItem = legendItems[i];
                legendItem.onclick = function() {
                    if (this.innerHTML === showing) {
                        toggleLabels(this.style.borderColor, true);
                        showing = 'all';
                    } else {
                        toggleLabels(this.style.borderColor, false);
                        showing = this.innerHTML;
                    }
                };
            }
        };
        function toggleLabels(rgb, showAll) {
            var map = document.getElementById('map');
            var areaLabels = map.getElementsByTagName('a');
            var areaLabel;
            for (var i = 0; i < areaLabels.length; ++i) {
                areaLabel = areaLabels[i];
                if (areaLabel.style.borderColor === rgb) {
                    areaLabel.style.display = 'block';
                } else {
                    areaLabel.style.display = showAll ? 'block' : 'none';
                }
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

function mp_dd_get_area_label_color(array $terms): string
{
    if (empty($terms)) {
        return '#A0A0A0';
    }
    $term = end($terms);
    $color = $term->description;
    return empty($color) ? '#A0A0A0' : $color;
}

#endregion

#region Objects
function mp_dd_filter_object_tags($content)
{
    if (preg_match_all('/\[object-(\d+)(.*?)\]/', $content, $matches)) {
        foreach ($matches[1] as $key => $objectID) {
            $type = $matches[2][$key];
            switch ($type) {
                case '-url':
                    $makeModal = true;
                    $target = $makeModal ? "#modal_$objectID" : "#$objectID";
                    $content = str_replace("[object-$objectID-url]", $target, $content);
                    break;
                case '-link':
                    $makeModal = true;
                    $title = get_post($objectID)->post_title;
                    $target = $makeModal ? "<a href=\"#modal_$objectID\">$title</a>" : "#$objectID";
                    $content = str_replace("[object-$objectID-link]", $target, $content);
                    break;
                case '-li':
                    $makeModal = false;
                    $li = '<li><div class="collapsible-header"><h3 style="margin: 1.14rem 0 0.912rem 0;">'.mp_dd_get_object_header($objectID, false).'</h3></div>';
                    $li .= '<div class="collapsible-body">'.mp_dd_filter_object_content($objectID).'</div></li>';
                    $content = str_replace("[object-$objectID-li]", $li, $content);
                    break;
                default:
                    $makeModal = false;
                    $target = mp_dd_get_object_header($objectID);
                    $target .= mp_dd_filter_object_content($objectID);
                    $content = str_replace("[object-$objectID$type]", $target, $content);
                    break;
            }
            if (strpos($content, "id=\"modal_$objectID\"") === false && $makeModal) {
                $objectTitle = mp_dd_get_object_header($objectID);
                $objectContent = mp_dd_filter_object_content($objectID);
                $objectModal = mp_dd_make_modal($objectTitle.$objectContent, $objectID);
                $content .= $objectModal;
            }
        }
    }
    return $content;
}
#endregion

#region Products
function mp_dd_filter_product_tags($content)
{
    if (preg_match_all('/\[product-(\d+)-(.*?)-(.*?)\]/', $content, $matches)) {
        foreach ($matches[1] as $key => $productID) {
            $title = get_post($productID)->post_title;
            $cost = $matches[2][$key];
            $inStock = $matches[3][$key];
            $target = "<tr><td>$title</td><td>$cost</td><td>$inStock</td></tr>";
            $content = str_replace("[product-$productID-$cost-$inStock]", $target, $content);
        }
    }
    return $content;
}
#endregion

#region Spells
function mp_dd_filter_spell_tags($content)
{
    if (preg_match_all('/\[spell-(\d+)-(.*?)\]/', $content, $matches)) {
        foreach ($matches[1] as $key => $productID) {
            $title = get_post($productID)->post_title;
            $cost = $matches[2][$key];
            $target = "<tr><td>$title</td><td>$cost</td></tr>";
            $content = str_replace("[spell-$productID-$cost]", $target, $content);
        }
    }
    return $content;
}
#endregion

#region Header
function mp_dd_get_object_header(int $objectID, bool $format = true, $header = '1'): string
{
    $object = get_post($objectID);
    $title = $object->post_title;
    $addedTag = get_post_meta($objectID, 'profession', true);
    if (strpos($title, '(') !== false && strpos($title, ')') !== false) {
        $addedTag = explode(')', explode('(', $title)[1])[0];
        $title = trim(str_replace('('.$addedTag.')', '', $title));
    }
    $addedTag = $addedTag ? '<span style="margin-left: 5px;"> ('.$addedTag.')</span>' : '';
    $postLink = get_permalink($objectID);
    if ($format) {
        return '<div><a href="'.$postLink.'" style="color:inherit; margin-top: 0.4em;"><h'.$header.' style="display: inline-block; margin-bottom: 0;">'.$title.'</h'.$header.'></a>'.$addedTag.'</div>';
    } else {
        return $title.$addedTag;
    }
}
#endregion

#region NPC
function mp_dd_get_npc_content(int $npcID)
{
    $npc = get_post($npcID);
    $npcHTML = '';
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '<div class="row">';
        $npcHTML .= '<div class="col s10" style="padding-left: 0;">';
    }
    $npcHTML .= '<p>';
    $class = get_post_meta($npcID, 'class', true);
    $level = get_post_meta($npcID, 'level', true);
    if ($class && $level) {
        $npcHTML .= '<b>Class:</b> '.$class.' <b>Level:</b> '.$level.'<br/>';
    }
    $npcHTML .= '<b>Height:</b> '.get_post_meta($npcID, 'height', true).' <b>Weight:</b> '.get_post_meta($npcID, 'weight', true).'<br/>';
    $npcHTML .= $npc->post_content.'<br/>';
    $npcHTML .= '<b>Wearing:</b> '.get_post_meta($npcID, 'clothing', true).'<br/>';
    $npcHTML .= '<b>Possessions:</b> '.get_post_meta($npcID, 'possessions', true).'<br/>';
    $npcHTML .= '</p>';
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '</div>';
        $npcHTML .= '<div class="col s2 valign-wrapper center-align">';
        $npcHTML .= get_the_post_thumbnail($npcID, 'thumbnail', ['class' => 'materialboxed']);
        $npcHTML .= '</div></div>';
    }

    return $npcHTML;
}
#endregion

#region Modal
function mp_dd_make_modal(string $content, int $objectID): string
{
    return "<div id=\"modal_$objectID\" class=\"modal\"><div id='modal_top'></div><div class=\"modal-content\">$content</div></div>";
}
#endregion