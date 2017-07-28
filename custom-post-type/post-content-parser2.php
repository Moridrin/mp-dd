<?php
///**
// * Created by PhpStorm.
// * User: moridrin
// * Date: 2-7-17
// * Time: 9:48
// */
//
//#region Filter
///**
// * @param string $content
// *
// * @return string
// */
//function mp_dd_filter_content($content)
//{
//    global $post;
//
//    if ($post->post_type == 'area') {
//        $content = mp_dd_filter_area_content($content, $post);
//    }
//    $content = mp_dd_filter_object_tags($content);
//    $content = mp_dd_filter_product_tags($content);
//    $content = mp_dd_filter_spell_tags($content);
//    return $content;
//}
//
//function mp_dd_filter_area_content($content, $post)
//{
//    if (strpos($content, '[map]') === false && !empty(get_post_meta($post->ID, 'map_image_id', true))) {
//        $content = '[map]' . $content;
//    }
//    if (strpos($content, '[map]') !== false) {
//        $content = str_replace('[map]', mp_dd_get_map($post), $content);
//    }
//    return $content;
//}
//
//function mp_dd_filter_object_tags($content, $makeModal = true)
//{
//    if (preg_match_all('/\[object-(\d+)\]/', $content, $matches)) {
//        foreach ($matches[1] as $objectID) {
//            $target = mp_dd_get_object_content($objectID);
//            $content = str_replace("[object-$objectID]", $target, $content);
//        }
//    }
//    if (preg_match_all('/\[object-(\d+)-(.*?)\]/', $content, $matches)) {
//        foreach ($matches[1] as $key => $objectID) {
//            $type = $matches[2][$key];
//            switch ($type) {
//                case 'url':
//                    $target = $makeModal ? "#modal_$objectID" : "#$objectID";
//                    $content = str_replace("[object-$objectID-url]", $target, $content);
//                    break;
//                case 'li':
//                    $li = '<li>';
//                    $li .= '<div class="collapsible-header">'.mp_dd_get_object_header($objectID, false).'</div>';
//                    $li .= '<div class="collapsible-body">'.mp_dd_get_object_content($objectID, false).'</div>';
//                    $li .= '</li>';
//                    $content = str_replace("[object-$objectID-li]", $li, $content);
//                    break;
//                case 'link':
//                default:
//                    $title = get_post($objectID)->post_title;
//                    $target = $makeModal ? "<a href=\"#modal_$objectID\">$title</a>" : "#$objectID";
//                    $content = str_replace("[object-$objectID-$type]", $target, $content);
//                    break;
//            }
//            if (strpos($content, "id=\"modal_$objectID\"") === false) {
//                $objectContent = mp_dd_get_object_content($objectID);
//                $objectContent = $makeModal ? mp_dd_make_modal($objectContent, $objectID) : $objectContent;
//                $content .= $objectContent;
//            }
//        }
//    }
//    return $content;
//}
//
//function mp_dd_filter_product_tags($content)
//{
//    if (preg_match_all('/\[product-(\d+)-(.*?)-(.*?)\]/', $content, $matches)) {
//        foreach ($matches[1] as $key => $productID) {
//            $title = get_post($productID)->post_title;
//            $cost = $matches[2][$key];
//            $inStock = $matches[3][$key];
//            $target = "<tr><td>$title</td><td>$cost</td><td>$inStock</td></tr>";
//            $content = str_replace("[product-$productID-$cost-$inStock]", $target, $content);
//        }
//    }
//    return $content;
//}
//
//function mp_dd_filter_spell_tags($content)
//{
//    if (preg_match_all('/\[spell-(\d+)-(.*?)\]/', $content, $matches)) {
//        foreach ($matches[1] as $key => $productID) {
//            $title = get_post($productID)->post_title;
//            $cost = $matches[2][$key];
//            $target = "<tr><td>$title</td><td>$cost</td></tr>";
//            $content = str_replace("[spell-$productID-$cost]", $target, $content);
//        }
//    }
//    return $content;
//}
//
//add_filter('the_content', 'mp_dd_filter_content');
//#endregion
//
//#region Map
//function mp_dd_get_map(WP_Post $post): string
//{
//    $image_id          = get_post_meta($post->ID, 'map_image_id', true);
//    if (empty($image_id)) {
//        return '';
//    }
//    $image_src         = wp_get_attachment_url($image_id);
//    $visibleObjects    = get_post_meta($post->ID, 'visible_objects', true);
//    $visibleObjects    = is_array($visibleObjects) ? $visibleObjects : [];
//    $labelTranslations = get_post_meta($post->ID, 'label_translations', true);
//    $labelTranslations = is_array($labelTranslations) ? $labelTranslations : [];
//    ob_start();
//    ?>
<!--    <div style="overflow-x: auto; overflow-y: hidden;">-->
<!--        <div id="map" style="width: --><?//= getimagesize($image_src)[0] ?>/*px;margin: auto; position: relative">*/
/*            <img id="map_image" src="*/<?//= $image_src ?><!--" class="materialboxed"/>-->
<!--            --><?php //$number = 1; ?>
<!--            --><?php //$terms = []; ?>
<!--            --><?php //foreach ($visibleObjects as $visibleObject): ?>
<!--                --><?php //$terms = array_merge($terms, wp_get_post_terms($visibleObject, 'area_type')); ?>
<!--                --><?php //$terms = array_merge($terms, wp_get_post_terms($visibleObject, 'npc_type')); ?>
<!--                --><?php //$terms = array_merge($terms, wp_get_post_terms($visibleObject, 'object_type')); ?>
<!--                --><?php //$labelColor = mp_dd_get_area_label_color(wp_get_post_terms($visibleObject, 'area_type')); ?>
<!--                --><?php //list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
<!--                <a href="[object---><?//= $visibleObject ?><!---url]" class="area-label" style="left: --><?//= $left + 3 ?>/*px; top: */<?//= $top + 3 ?>/*px; border: 3px solid */<?//= $labelColor ?>/*;">*/<?//= $number ?><!--</a>-->
<!--                --><?php //++$number; ?>
<!--            --><?php //endforeach; ?>
<!--        </div>-->
<!--    </div>-->
<!--    <div class="row">-->
<!--        --><?php //foreach (array_unique($terms, SORT_REGULAR) as $term): ?>
<!--            --><?php //$color = ctype_xdigit($term->description) ? '#A0A0A0' : $term->description; ?>
<!--            <div class="col s12 m3 area-label-legend" style="border: 3px solid --><?//= $color ?><!--">--><?//= $term->name ?><!--</div>-->
<!--        --><?php //endforeach; ?>
<!--    </div>-->
<!--    --><?php
    return ob_get_clean();
//}
//
//function mp_dd_get_area_label_color(array $terms): string
//{
//    if (empty($terms)) {
//        return '#A0A0A0';
//    }
//    /** @var WP_Term $term */
//    $term = end($terms);
//    $color = $term->description;
//    return empty($color) ? '#A0A0A0' : $color;
//}
//#endregion
//
//#region Object Content
//function mp_dd_get_object_content(int $objectID, bool $withTitle = true): string
//{
//    $object = get_post($objectID);
//    switch ($object->post_type) {
//        case 'area':
//            return mp_dd_get_area_content($objectID);
//        case 'npc':
//            return mp_dd_get_npc_content($objectID, $withTitle);
//        default:
//            return '';
//    }
//}
//function mp_dd_get_object_header(int $objectID, bool $withLink = true): string
//{
//    $object = get_post($objectID);
//    switch ($object->post_type) {
//        case 'area':
//            return mp_dd_get_area_content($objectID, $withLink);
//        case 'npc':
//            return mp_dd_get_npc_header($objectID, $withLink);
//        default:
//            return '';
//    }
//}
//
//function mp_dd_get_area_content(int $areaID): string
//{
//    $area = get_post($areaID);
//    ob_start();
//    $postLink = get_permalink($areaID);
//    ?>
<!--    <div><a href="--><?//= $postLink ?><!--" style="color:inherit;"><h1 id="--><?//= $areaID ?><!--" style="display: inline-block;">--><?//= $area->post_title ?><!--</h1></a></div>-->
<!--    --><?//= mp_dd_filter_object_tags($area->post_content, false); ?>
<!--    --><?php
//    return ob_get_clean();
//}
//
//function mp_dd_get_npc_content(int $npcID, bool $withTitle = true, $withLink = true)
//{
//    $npc        = get_post($npcID);
//    if ($withTitle) {
//        $npcHTML = mp_dd_get_npc_header($npcID, $withLink);
//    } else {
//        $npcHTML = '';
//    }
//    if (has_post_thumbnail($npcID)) {
//        $npcHTML .= '<div class="row">';
//        $npcHTML .= '<div class="col s10" style="padding-left: 0;">';
//    }
//    $npcHTML .= '<p>';
//    $class   = get_post_meta($npcID, 'class', true);
//    $level   = get_post_meta($npcID, 'level', true);
//    if ($class && $level) {
//        $npcHTML .= '<b>Class:</b> ' . $class . ' <b>Level:</b> ' . $level . '<br/>';
//    }
//    $npcHTML .= '<b>Height:</b> ' . get_post_meta($npcID, 'height', true) . ' <b>Weight:</b> ' . get_post_meta($npcID, 'weight', true) . '<br/>';
//    $npcHTML .= $npc->post_content . '<br/>';
//    $npcHTML .= '<b>Wearing:</b> ' . get_post_meta($npcID, 'clothing', true) . '<br/>';
//    $npcHTML .= '<b>Possessions:</b> ' . get_post_meta($npcID, 'possessions', true) . '<br/>';
//    $npcHTML .= '</p>';
//    if (has_post_thumbnail($npcID)) {
//        $npcHTML .= '</div>';
//        $npcHTML .= '<div class="col s2 valign-wrapper center-align">';
//        $npcHTML .= get_the_post_thumbnail($npcID, 'thumbnail', ['class' => 'materialboxed']);
//        $npcHTML .= '</div></div>';
//    }
//
//    return $npcHTML;
//}
//
//function mp_dd_get_npc_header(int $npcID, bool $withLink = true)
//{
//    $npc        = get_post($npcID);
//    $title = $npc->post_title;
//    $profession = get_post_meta($npcID, 'profession', true);
//    if (strpos($title, '(') !== false && strpos($title, ')') !== false) {
//        $profession = explode(')', explode('(', $title)[1])[0];
//        $title = trim(str_replace('('.$profession.')', '', $title));
//    }
//    $profession = $profession ? '<span style="margin-left: 5px;"> (' . $profession . ')</span>' : '';
//    $postLink = get_permalink($npcID);
//    if ($withLink) {
//        return '<div><a href="'.$postLink.'" style="color:inherit; margin-top: 0.4em;"><h2 style="display: inline-block;">'.$title.'</h2></a>'.$profession.'</div>';
//    } else {
//        return '<div><h2 style="display: inline-block; margin-top: 0.4em;">'.$title.'</h2>'.$profession.'</div>';
//    }
//}
//#endregion
//
//#region Other
//function mp_dd_make_modal(string $content, int $objectID): string
//{
//    $modalStart = "<div id=\"modal_$objectID\" class=\"modal\"><div id='modal_top'></div><div class=\"modal-content\">";
//    $modalEnd   = "</div></div>";
//    return $modalStart . $content . $modalEnd;
//}
//#endregion
