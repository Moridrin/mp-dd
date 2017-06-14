<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 1-3-17
 * Time: 17:14
 */

/**
 * @param string $content
 *
 * @return string content
 */
function mp_dd_item_custom_content($content)
{
    #region Init
    global $post;
    if (!in_array($post->post_type, array('item', 'weapon', 'armor'))) {
        return $content;
    }
    $item = Item::load($post->ID);
    #endregion

    #region $description & $properties
    $description = '<h2>Description</h2>' . $content;
    ob_start();
    ?>
    <h2>Properties</h2>
    <ul class="collection">
        <?php foreach ($item->properties as $property): ?>
            <li class="collection-item">
                <?= $property ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    $properties = ob_get_clean();
    #endregion

    #region $content
    if (!empty($item->properties) && !empty($description)) {
        ob_start();
        ?>
        <div class="row">
            <div class="col s12 l8">
                <?= $description ?>
            </div>
            <div class="col s12 l4">
                <?= $properties ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
    } elseif (!empty($item->properties) && empty($description)) {
        $content = $properties;
    } elseif (empty($item->properties) && !empty($description)) {
        $content = $description;
    }
    #endregion

    return $content;
}

add_filter('the_content', 'mp_dd_item_custom_content');
