<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 28-2-17
 * Time: 18:11
 */
abstract class EmbeddedObject
{
    /** @var int $post */
    public $postID;

    /**
     * @param int         $id
     * @param string|null $type
     *
     * @return static
     */
    public static function getByID($id, $type = null)
    {
        $type = $type ?: static::class;
        return static::fromJSON(get_post_meta($id, strtolower($type), true));
    }

    /**
     * This function builds the Embedded Object from the $_POST variable.
     *
     * @param int $postID is the id of the post where this object is embedded in.
     *
     * @return static
     */
    public static function fromPOST($postID)
    {
        $embeddedObject = new static;
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $embeddedObject;
        }
        $objectVars = get_object_vars($embeddedObject);
        foreach ($objectVars as $var => $value) {
            if (is_array($value)) {
                $i = 0;
                while (isset($_POST[$var . '_' . $i])) {
                    if (empty($_POST[$var . '_' . $i])) {
                        $i++;
                        continue;
                    }
                    array_push($embeddedObject->$var, mp_dd_sanitize($_POST[$var . '_' . $i]));
                    $i++;
                }
            } elseif (is_bool($value)) {
                $embeddedObject->$var = filter_var($_POST[$var], FILTER_VALIDATE_BOOLEAN);
            } else {
                $embeddedObject->$var = mp_dd_sanitize($_POST[$var]);
            }
        }
        $embeddedObject->postID = $postID;
        return $embeddedObject;
    }

    /**
     * @param string $json
     *
     * @return static
     */
    private static function fromJSON($json)
    {
        $embeddedObject = new static;
        $objectVars     = json_decode($json, true);
        if ($objectVars) {
            foreach ($objectVars as $var => $value) {
                $embeddedObject->$var = $value;
            }
        }
        return $embeddedObject;
    }

    /**
     * @param int $postID
     *
     * @return static
     */
    public static function load($postID)
    {
        $post = get_post($postID);
        return self::fromJSON(get_post_meta($postID, $post->post_type, true));
    }

    /**
     * @return string
     */
    private function getJSON()
    {
        return json_encode(get_object_vars($this));
    }

    public function save()
    {
        $post = get_post($this->postID);
        update_post_meta($this->postID, $post->post_type, $this->getJSON());
    }
}