<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 28-2-17
 * Time: 18:11
 */
abstract class EmbeddedObject
{
    /**
     * @param int $id
     *
     * @return EmbeddedObject
     */
    public static function getByID($id)
    {
        return static::fromJSON(get_post_meta($id, 'item', true));
    }

    /**
     * @return EmbeddedObject
     */
    public static function fromPOST()
    {
        $embeddedObject = new static;
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $embeddedObject;
        }
        $objectVars = get_object_vars($embeddedObject);
        foreach ($objectVars as $var => $value) {
            $embeddedObject->$var = mp_dd_sanitize($_POST[$var]);
        }
        return $embeddedObject;
    }

    /**
     * @param string $json
     *
     * @return EmbeddedObject
     */
    public static function fromJSON($json)
    {
        $embeddedObject = new static;
        $objectVars     = json_decode($json);
        foreach ($objectVars as $var => $value) {
            $embeddedObject->$var = $value;
        }
        return $embeddedObject;
    }

    /**
     * @return string
     */
    public function getJSON()
    {
        return json_encode(get_object_vars($this));
    }
}
