<?php

/**
 * @author Steffen Maechtel <info@steffen-maechtel.de>
 * @copyright 2015 Steffen Maechtel
 * @license MIT
 */
class JSONReference {

    /**
     * @param mixed $data
     * @return \stdClass {root: ..., objects: ...}
     */
    public static function encode($data) {
        $objects = new \stdClass();

        $root = self::extractObjectsAndReplaceWithHashRecursive($data, $objects);

        return (object) array('root' => $root, 'objects' => $objects);
    }

    /**
     * @param \stdClass $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function decode($data) {
        if (($data instanceof \stdClass) === false) {
            throw new \InvalidArgumentException('Parameter data is not an instance of stdClass');
        }

        if (property_exists($data, 'root') === false) {
            throw new \InvalidArgumentException('Parameter data has no property root');
        }

        if (property_exists($data, 'objects') === false) {
            throw new \InvalidArgumentException('Parameter data has no property objects');
        }

        if (is_array($data->objects) && count($data->objects) > 0) {
            throw new \InvalidArgumentException('Parameter data->objects has items, but is not an instance of stdClass');
        }

        return self::replace_hash_with_objects($data->root, clone $data->objects);
    }

    /**
     * @param mixed $data
     * @param \stdClass $objects
     * @param array $reached
     * @return string
     */
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

    /**
     * 
     * @param mixed $data
     * @param \stdClass $objects
     * @param array $reached
     * @return mixed
     * @throws \Exception
     */
    protected static function replaceHashWithObjectsRecursive($data, $objects, & $reached = array()) {
        switch (true) {
            case is_array($data):
                foreach ($data as $key => $value) {
                    $data[$key] = self::replaceHashWithObjectsRecursive($value, $objects, $reached);
                }
                return $data;
            case is_string($data) === true && $data[0] === '@':
                if (property_exists($objects, $data) === false) {
                    throw new \Exception('Object with hash "' . $data . '" does not exists in objects');
                }
                if (in_array($data, $reached) === false) {
                    $reached[] = $data;
                    foreach ($objects->$data as $property => $value) {
                        $objects->$data->$property = self::replaceHashWithObjectsRecursive($value, $objects, $reached);
                    }
                }
                return $objects->$data;
            default:
                return $data;
        }
    }

}
