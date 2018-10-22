<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 22-7-17
 * Time: 15:24
 */

namespace mp_dd\Wizardawn\Models;

use ReflectionClass;

class JsonObject
{

    public $id;

    public function __construct()
    {
        $this->id = uniqid(strtolower((new ReflectionClass($this))->getShortName()) . '_');
    }

    public function toJSON()
    {
        $vars     = get_object_vars($this);
        $jsonVars = [];
        foreach ($vars as $var => $value) {
            if ($value instanceof JsonObject) {
                $array[$var] = $value->getID();
            } elseif (is_array($value)) {
                $array = [];
                foreach ($value as $key => $item) {
                    if ($item instanceof JsonObject) {
                        $array[$key] = $item->getID();
                    } else {
                        $array[$key] = $item;
                    }
                }
                $jsonVars[$var] = $array;
            } else {
                $jsonVars[$var] = $value;
            }
        }
        return json_encode($jsonVars);
    }

    public function fromJSON($json)
    {
        $vars   = json_decode($json);
        $object = new self();
        foreach ($vars as $var => $value) {
            $object->$var = $value;
        }
        return $object;
    }

    public function setID(string $id)
    {
        $this->id = $id;
    }

    public function getID()
    {
        return $this->id;
    }

    public function replaceID($id, $wp_id): bool
    {
        $returner = false;
        if ($this->id == $id) {
            $this->id = $wp_id;
            $returner = true;
        } else {
            $vars = get_object_vars($this);
            foreach ($vars as $varKey => $var) {
                if (is_array($var) && $varKey !== 'vaultItems') {
                    foreach ($var as $key => &$item) {
                        if ($key == $id) {
                            $item = $wp_id;
                            $this->$varKey = $var;
                            $returner = true;
                        }
                        if ($item instanceof JsonObject) {
                            if ($item->replaceID($id, $wp_id)) {
                                $this->$varKey = $var;
                                $returner = true;
                            }
                        }
                    }
                } elseif ($var instanceof JsonObject) {
                    if ($var->replaceID($id, $wp_id)) {
                        $this->$varKey = $var;
                        $returner = true;
                    }
                }
            }
        }
        return $returner;
    }
}
