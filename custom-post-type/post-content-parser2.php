<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 2-7-17
 * Time: 9:48
 */

#region Post Content
/**
 * @param string $content
 *
 * @return string
 */
function mp_dd_filter_content($content)
{
    global $post;

    if ($post->post_type == 'area') {
        $content = mp_dd_filter_area_content($content, $post);
    }
    $content = mp_dd_filter_modal_tags($content);
    return $content;
}

function mp_dd_filter_area_content($content, $post)
{
        if (strpos($content, '[map]') === false) {
            $content = '[map]' . $content;
        }
        $content = str_replace('[map]', mp_dd_get_map($post), $content);
    return $content;
}

function mp_dd_filter_modal_tags($content, $makeModal = true, $top = null)
{
    if (preg_match_all('/\[object-(\d+)-url\]/', $content, $matches)) {
        foreach ($matches[1] as $objectID) {
            $target = $makeModal ? "#modal_$objectID" : "#$objectID";
            $content = str_replace("[object-$objectID-url]", $target, $content);
            if (strpos($content, "id=\"modal_$objectID\"") === false) {
                $objectContent = mp_dd_get_object_content($objectID, $top);
                $objectContent = $makeModal ? mp_dd_make_modal($objectContent, $objectID) : $objectContent;
                $content .= $objectContent;
            }
        }
    }
    return $content;
}

add_filter('the_content', 'mp_dd_filter_content');
#endregion

#region Map
function mp_dd_get_map(WP_Post $post): string
{
    $image_id          = get_post_meta($post->ID, 'map_image_id', true);
    if (empty($image_id)) {
        return '';
    }
    $image_src         = wp_get_attachment_url($image_id);
    $visibleObjects    = get_post_meta($post->ID, 'visible_objects', true);
    $visibleObjects    = is_array($visibleObjects) ? $visibleObjects : [];
    $labelTranslations = get_post_meta($post->ID, 'label_translations', true);
    $labelTranslations = is_array($labelTranslations) ? $labelTranslations : [];
    ob_start();
    ?>
    <div style="overflow-x: auto; overflow-y: hidden;">
        <div id="map" style="width: <?= getimagesize($image_src)[0] ?>px;margin: auto; position: relative">
            <img id="map_image" src="<?= $image_src ?>"/>
            <?php $number = 1; ?>
            <?php foreach ($visibleObjects as $visibleObject): ?>
                <?php $labelColor = mp_dd_get_area_label_color(wp_get_post_terms($visibleObject, 'area_type')); ?>
                <?php list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
                <a href="[object-<?= $visibleObject ?>-url]" class="area-label" style="left: <?= $left + 3 ?>px; top: <?= $top + 3 ?>px; border: 3px solid <?= $labelColor ?>;"><?= $number ?></a>
                <?php ++$number; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row">
        <?php foreach (get_terms('area_type') as $term): ?>
            <?php $color = empty($term->description) ? '#A0A0A0' : $term->description; ?>
            <div class="col s12 m3 area-label-legend" style="border: 3px solid <?= $color ?>"><?= $term->name ?></div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

function mp_dd_get_area_label_color(array $terms): string
{
    if (empty($terms)) {
        return '#A0A0A0';
    }
    /** @var WP_Term $term */
    $term = end($terms);
    $color = $term->description;
    return empty($color) ? '#A0A0A0' : $color;
}

function mp_dd_make_modal(string $content, int $objectID): string
{
    $modalStart = "<div id=\"modal_$objectID\" class=\"modal modal-fixed-footer\"><div id='modal_top'></div><div class=\"modal-content\">";
    $modalEnd   = "</div></div>";
    return $modalStart . $content . $modalEnd;
}

function mp_dd_get_object_content(int $objectID, $top = null): string
{
    $object = get_post($objectID);
    switch ($object->post_type) {
        case 'area':
            return mp_dd_get_area_content($objectID, $top);
        case 'npc':
            return mp_dd_get_npc_content($objectID, $top);
        default:
            return '';
    }
}

function mp_dd_get_area_content(int $areaID, $top = null): string
{
    $area = get_post($areaID);
    ob_start();
    $postLink = get_permalink($areaID);
    $topLink = $top !== null ? "<a href='#$top'>&uarr;</a>" : '';
    ?>
    <div><a href="<?= $postLink ?>" style="color:inherit;"><h1 id="<?= $areaID ?>" style="display: inline-block;"><?= $area->post_title ?></h1></a> <?= $topLink ?></div>
    <?= mp_dd_filter_modal_tags(mp_dd_filter_area_content($area->post_content, $area), false, $areaID); ?>
    <?php
    return ob_get_clean();
}

function mp_dd_get_npc_content(int $npcID, $top = null)
{
    $npc        = get_post($npcID);
    $profession = get_post_meta($npcID, 'profession', true);
    $profession = $profession ? '<span style="margin-left: 5px;"> (' . $profession . ')</span>' : '';
    $postLink = get_permalink($npcID);
    $topLink = $top !== null ? "<a href='#$top'>&uarr;</a>" : '';
    $npcHTML    = "<div><a href='$postLink' style='color:inherit;'><h2 id='$npcID' style=\"display: inline-block;\">$npc->post_title</h2></a> $profession $topLink</div>";
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '<div class="row">';
        $npcHTML .= '<div class="col s10" style="padding-left: 0;">';
    }
    $npcHTML .= '<p>';
    $class   = get_post_meta($npcID, 'class', true);
    $level   = get_post_meta($npcID, 'level', true);
    if ($class && $level) {
        $npcHTML .= '<b>Class:</b> ' . $class . ' <b>Level:</b> ' . $level . '<br/>';
    }
    $npcHTML .= '<b>Height:</b> ' . get_post_meta($npcID, 'height', true) . ' <b>Weight:</b> ' . get_post_meta($npcID, 'weight', true) . '<br/>';
    $npcHTML .= $npc->post_content . '<br/>';
    $npcHTML .= '<b>Wearing:</b> ' . get_post_meta($npcID, 'clothing', true) . '<br/>';
    $npcHTML .= '<b>Possessions:</b> ' . get_post_meta($npcID, 'possessions', true) . '<br/>';
    $npcHTML .= '</p>';
    if (has_post_thumbnail($npcID)) {
        $npcHTML .= '</div>';
        $npcHTML .= '<div class="col s2 valign-wrapper center-align">';
        $npcHTML .= get_the_post_thumbnail($npcID, 'thumbnail');
        $npcHTML .= '</div></div>';
    }

    return $npcHTML;
}


#endregion