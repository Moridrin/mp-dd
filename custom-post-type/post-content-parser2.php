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
        if (strpos($content, '[map]') === false) {
            $content = '[map]' . $content;
        }
        $content = str_replace('[map]', mp_dd_get_map($post), $content);
    }
    return $content;
}

add_filter('the_content', 'mp_dd_filter_content');
#endregion

function mp_dd_get_map(WP_Post $post): string
{
    $image_id          = get_post_meta($post->ID, 'map_image_id', true);
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
    /** @var WP_Term $term */
    $term = end($terms);
    $color = $term->description;
    return empty($color) ? '#A0A0A0' : $color;
}
