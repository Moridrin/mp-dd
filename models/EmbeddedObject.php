<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 28-2-17
 * Time: 18:11
 */
abstract class EmbeddedObject
{
    const HTML
        = array(
//            'map',
        );
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
            if ($var == 'postID') {
                continue;
            } elseif (is_array($value)) {
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
                if (isset($_POST[$var])) {
                    if (in_array($var, self::HTML)) {
                        $newValue = str_replace("\r\n", '<br/>', $_POST[$var]);
                        $newValue = str_replace('<', '[', $newValue);
                        $newValue = str_replace('>', ']', $newValue);
                        $newValue = str_replace('"', '\'', $newValue);
                        $newValue = stripslashes($newValue);
                    } else {
                        $newValue = mp_dd_sanitize($_POST[$var]);
                    }
                    $embeddedObject->$var = $newValue;
                } elseif (isset($_POST[mp_dd_to_snake_case($var)])) {
                    $embeddedObject->$var = mp_dd_sanitize($_POST[mp_dd_to_snake_case($var)]);
                }
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
        mp_dd_var_export($objectVars);
        if ($objectVars) {
            foreach ($objectVars as $var => $value) {
                if (in_array($var, self::HTML)) {
                    $newValue = str_replace('[br/]', "\r\n", $value);
                    $newValue = str_replace('[', '<', $newValue);
                    $newValue = str_replace(']', '>', $newValue);
                    $newValue = str_replace('\'', '"', $newValue);
                    $newValue = stripslashes($newValue);
                } else {
                    $newValue = $value;
                }
                mp_dd_var_export($var);
                mp_dd_var_export($newValue);
                $embeddedObject->$var = $newValue;
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
