<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 27-2-17
 * Time: 7:56
 */
class Race extends EmbeddedObject
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
     * @return Race|false
     */
    public static function fromPOST($postID)
    {
        $race  = new Race();
        $index = 0;
        while (isset($_POST['property_' . $index . '_title'])) { //TODO Improve (this removes all after empty title)
            $title       = mp_dd_sanitize($_POST['property_' . $index . '_title']);
            $description = str_replace(PHP_EOL, '<br/>', mp_dd_sanitize($_POST['property_' . $index . '_description']));
            if (empty($title)) {
                $index++;
                continue;
            }
            if (isset($description)) {
                $race->properties[$title] = $description;
            }
            $index++;
        }
        $race->postID = $postID;
        return $race;
    }

    #endregion

    public function getPropertiesHTML()
    {
        ob_start();
        ?>
        <div class="card-panel">
            <table class="striped">
                <?php foreach ($this->properties as $title => $property): ?>
                    <tr>
                        <th><?= mp_dd_to_title($title) ?></th>
                        <td><?= $property ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function getRaceSelect($selected)
    {
        global $wpdb;
        $custom_post_type = 'race';
        $results          = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $custom_post_type), ARRAY_A);
        ob_start();
        ?>
        <select name="race" id="race" title="Race">
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
