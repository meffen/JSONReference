<?php

/**
 * @author Steffen Maechtel <info@steffen-maechtel.de>
 * @copyright 2015 Steffen Maechtel
 * @license MIT
 */
class JSONReference {

    public static function encode($data) {
        $objects = new stdClass();

        $root = self::extractObjectsAndReplaceWithHashRecursive($data, $objects);

        return (object) array('root' => $root, 'objects' => $objects);
    }

    public static function decode($data) {
        
    }

    protected static function extractObjectsAndReplaceWithHashRecursive($data, $objects, & $reached = array()) {
        switch (true) {
            case is_array($data):
                foreach ($data as $key => $value) {
                    $data[$key] = self::extractObjectsAndReplaceWithHashRecursive($value, $objects, $reached);
                }
                return $data;
            case is_object($data):
                $hash = '@' . spl_object_hash($data);
                if (in_array($hash, $reached) === false) {
                    $reached[] = $hash;
                    $object = new stdClass();
                    foreach ($data as $property => $value) {
                        $object->$property = self::extractObjectsAndReplaceWithHashRecursive($value, $objects, $reached);
                    }
                    $objects->$hash = $object;
                }
                return $hash;

            default:
                return $data;
        }
    }

}
