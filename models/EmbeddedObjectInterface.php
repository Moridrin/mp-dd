<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 28-2-17
 * Time: 18:11
 */
interface EmbeddedObjectInterface
{
    /**
     * @param int $id
     *
     * @return EmbeddedObject
     */
    public static function getByID($id);

    /**
     * @return EmbeddedObject
     */
    public static function fromPOST();

    /**
     * @param string $json
     *
     * @return EmbeddedObject
     */
    public static function fromJSON($json);

    /**
     * @return string
     */
    public function getJSON();
}