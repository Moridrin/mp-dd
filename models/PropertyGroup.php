<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 7:56
 */
class PropertyGroup extends EmbeddedObject
{
    #region Variables
    /** @var string[] $properties */
    public $properties = array();
    #endregion

    #region fromPOST()
    /**
     * This function builds the Embedded Object from the $_POST variable.
     *
     * @param int $postID is the id of the post where this object is embedded in.
     *
     * @return PropertyGroup|false
     */
    public static function fromPOST($postID)
    {
        $propertyGroup = new PropertyGroup();
        $index         = 0;
        while (isset($_POST['property_' . $index . '_title'])) { //TODO Improve (this removes all after empty title)
            $title       = mp_dd_sanitize($_POST['property_' . $index . '_title']);
            $description = mp_dd_sanitize($_POST['property_' . $index . '_description']);
            if (empty($title)) {
                $index++;
                continue;
            }
            if (isset($description)) {
                $propertyGroup->properties[$title] = $description;
            }
            $index++;
        }
        $propertyGroup->postID = $postID;
        return $propertyGroup;
    }

    #endregion

    public function getPropertiesHTML()
    {
        ob_start();
        ?>
        <div class="card-panel">
            <div class="row">
                <?php foreach ($this->properties as $title => $property): ?>
                    <div class="col s4" style="padding: 10px 0;"><strong><?= mp_dd_to_title($title) ?></strong></div>
                    <div class="col s8" style="padding: 10px 0;"><?= $property ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function getPropertyGroupSelect($propertyGroup, $selected)
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $propertyGroup), ARRAY_A);
        ob_start();
        ?>
        <select name="<?= $propertyGroup ?>" id="<?= $propertyGroup ?>" title="<?= mp_dd_to_title($propertyGroup) ?>">
            <option value="-1">Monster</option>
            <?php foreach ($results as $index => $post): ?>
                <option value="<?= $post['ID'] ?>" <?php selected($selected, $post['ID']) ?>><?= $post['post_title'] ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        return ob_get_clean();
    }

    public function getLink()
    {
        $post = get_post($this->postID);
        ob_start();
        ?><a href="<?= get_permalink($post) ?>"><?= $post->post_title ?></a><?php
        return ob_get_clean();
    }
}
