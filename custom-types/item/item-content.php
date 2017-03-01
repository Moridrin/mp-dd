<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 1-3-17
 * Time: 17:14
 */

function mp_dd_item_custom_content($content)
{
    global $post;
    if (!in_array($post->post_type, array('item', 'weapon', 'armor'))) {
        return $content;
    }
    $item        = Item::load($post->ID);
    $description = $content;
    if (!empty($item->properties) && !empty($description)) {
        ob_start();
        ?>
        <div class="row">
            <div class="col s12 l8">
                <h2>Description</h2>
                <?= $description ?>
            </div>
            <div class="col s12 l4">
                <h2>Properties</h2>
                <ul class="collection">
                    <?php foreach ($item->properties as $property): ?>
                        <li class="collection-item">
                            <?= $property ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
    } elseif (!empty($item->properties) && empty($description)) {
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
        $content = ob_get_clean();
    } elseif (empty($item->properties) && !empty($description)) {
        ob_start();
        ?>
        <h2>Properties</h2>
        <?= $description ?>
        <?php
        $content = ob_get_clean();
    }
    return $content;
}

add_filter('the_content', 'mp_dd_item_custom_content');
