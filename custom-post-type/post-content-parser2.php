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
                <?php mp_var_export(wp_get_post_terms($visibleObject, 'area_type'), 1); ?>
                <?php list($left, $top) = isset($labelTranslations[$visibleObject]) ? $labelTranslations[$visibleObject] : 'translate(0px, 0px)'; ?>
                <a href="[object-<?= $visibleObject ?>-url]" class="<?= get_post_meta($visibleObject, 'object_type', true) ?>-label" style="left: <?= $left + 3 ?>px; top: <?= $top + 3 ?>px"><?= $number ?></a>
                <?php ++$number; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
