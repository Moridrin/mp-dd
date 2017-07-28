<?php

add_filter('the_content', 'mp_dd_filter_content');
function mp_dd_filter_content($content)
{
    global $post;
    return mp_dd_filter_object_content($post->ID, $content);
}

function mp_dd_filter_object_content(int $postID, string $content = null)
{
    $post = get_post($postID);
    if ($content == null) {
        $content = $post->post_content;
    }
    if ($post->post_type == 'area') {
        if (strpos($content, '[map]') === false && !empty(get_post_meta($post->ID, 'map_image_id', true))) {
            $content = '[map]' . $content;
        }
        if (strpos($content, '[map]') !== false) {
            $content = str_replace('[map]', mp_dd_get_map($post), $content);
        }
    }
    $content = mp_dd_filter_object_tags($content);
//    $content = mp_dd_filter_product_tags($content);
//    $content = mp_dd_filter_spell_tags($content);
    return $content;
}

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
            <img id="map_image" src="<?= $image_src ?>" class="materialboxed"/>
            <?php $number = 1; ?>
            <?php $terms = []; ?>
            <?php foreach ($visibleObjects as $visibleObject): ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'area_type')); ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'npc_type')); ?>
                <?php $terms = array_merge($terms, wp_get_post_terms($visibleObject, 'object_type')); ?>
                <?php $labelColor = mp_dd_get_area_label_color(wp_get_post_terms($visibleObject, 'area_type')); ?>
                <?php list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
                <a href="[object-<?= $visibleObject ?>-url]" class="area-label" style="left: <?= $left + 3 ?>px; top: <?= $top + 3 ?>px; border: 3px solid <?= $labelColor ?>;"><?= $number ?></a>
                <?php ++$number; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row">
        <?php foreach (array_unique($terms, SORT_REGULAR) as $term): ?>
            <?php $color = ctype_xdigit($term->description) ? '#A0A0A0' : $term->description; ?>
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
    $term = end($terms);
    $color = $term->description;
    return empty($color) ? '#A0A0A0' : $color;
}
#endregion

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
                    $li = '<li><div class="collapsible-header">'.mp_dd_get_object_header($objectID, false, 2).'</div>';
                    $li .= '<div class="collapsible-body">'.mp_dd_filter_object_content($objectID).'</div></li>';
                    $content = str_replace("[object-$objectID-li]", $li, $content);
                    break;
                default:
                    $makeModal = false;
                    $target = mp_dd_filter_object_content($objectID);
                    $content = str_replace("[object-$objectID$type]", $target, $content);
                    break;
            }
            if (strpos($content, "id=\"modal_$objectID\"") === false && $makeModal) {
                $objectTitle = mp_dd_get_object_header($objectID);
                $objectContent = mp_dd_filter_object_content($objectID);
                $objectModal = mp_dd_make_modal($objectTitle . $objectContent, $objectID);
                $content .= $objectModal;
            }
        }
    }
    return $content;
}

#region Content & Header
function mp_dd_get_object_header(int $objectID, bool $withLink = true, $header = '1'): string
{
    $object = get_post($objectID);
    $title = $object->post_title;
    $addedTag = get_post_meta($objectID, 'profession', true);
    if (strpos($title, '(') !== false && strpos($title, ')') !== false) {
        $addedTag = explode(')', explode('(', $title)[1])[0];
        $title = trim(str_replace('('.$addedTag.')', '', $title));
    }
    $addedTag = $addedTag ? '<span style="margin-left: 5px;"> (' . $addedTag . ')</span>' : '';
    $postLink = get_permalink($objectID);
    if ($withLink) {
        return '<div><a href="'.$postLink.'" style="color:inherit; margin-top: 0.4em;"><h'.$header.' style="display: inline-block;">'.$title.'</h'.$header.'></a>'.$addedTag.'</div>';
    } else {
        return '<div><h'.$header.' style="display: inline-block; margin-top: 0.4em;">'.$title.'</h'.$header.'>'.$addedTag.'</div>';
    }
}
#endregion

function mp_dd_make_modal(string $content, int $objectID): string
{
    return "<div id=\"modal_$objectID\" class=\"modal\"><div id='modal_top'></div><div class=\"modal-content\">$content</div></div>";
}