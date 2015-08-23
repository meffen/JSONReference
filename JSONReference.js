/**
 * @author Steffen Maechtel <info@steffen-maechtel.de>
 * @copyright 2015 Steffen Maechtel
 * @license MIT
 */

var JSONReference;

(function () {

    JSONReference = {
        /**
         * @param mixed data
         * @returns object (can be used as argument for of JSON.stringify)
         */
        encode: function (data) {
            var objects, reached, root;
            objects = {};
            reached = {};
            root = extractObjectsAndReplaceWithHashRecursive(data, objects, reached);

            cleanupData(data);

            return {root: root, objects: objects};
        },
        /**
         * @param object data (result of JSON.parse)
         * @returns mixed
         */
        decode: function (data) {
            if (('root' in data) === false) {
                throw 'Parameter data has no property root.';
            }

            if (('objects' in data) === false) {
                throw 'Parameter data has no property objects.';
            }

            var reached, data;
            reached = {};
            data = replaceHashWithObjectsRecursive(data.root, data.objects, reached);
            return data;
        }
    };

    /**
     * While processing data, the script add two properies to all objects
     * object.__JSONReference__hash
     * object.__JSONReference__process
     *
     * Cleanup data remove both helper properties in all objects
     * 
     * @param mixed data
     * @param object objects
     */
    function cleanupData(data, objects) {
        var property;
        if (typeof objects === 'undefined') {
            objects = {};
        }
        if (typeof data !== 'object' || data === null) {
            return;
        }
        if (typeof data === 'object' && data !== null) {
            if ('__JSONReference__hash' in data && data.__JSONReference__hash) {
                if (data.__JSONReference__hash in objects && objects[data.__JSONReference__hash] === true) {
                    return;
                }
                objects[data.__JSONReference__hash] = true;
            }
            for (property in data) {
                if (data.hasOwnProperty(property) === false) {
                    continue;
                }
                if (property === '__JSONReference__hash') {
                    continue;
                }
                if (property === '__JSONReference__process') {
                    continue;
                }
                cleanup_data(data[property], objects);
            }
            if ('__JSONReference__hash' in data) {
                delete data.__JSONReference__hash;
            }
            if ('__JSONReference__process' in data) {
                delete data.__JSONReference__process;
            }
        }
    }

    function extractObjectsAndReplaceWithHashRecursive(data, objects, reached) {
        var key, property, hash, array, object;
        if (typeof data === 'object' && Array.isArray(data)) {
            array = [];
            for (key in data) {
                if (data.hasOwnProperty(key) === false) {
                    continue;
                }
                array[key] = extractObjectsAndReplaceWithHashRecursive(data[key], objects, reached);
            }
            return array;
        } else if (typeof data === 'object' && data !== null) {
            hash = '@' + get_object_hash(data);
            if ((hash in reached) === false) {
                reached[hash] = true;
                object = {};
                for (property in data) {
                    if (data.hasOwnProperty(property) === false) {
                        continue;
                    }
                    if (property === '__JSONReference__hash') {
                        continue;
                    }
                    if (property === '__JSONReference__process') {
                        continue;
                    }
                    object[property] = extractObjectsAndReplaceWithHashRecursive(data[property], objects, reached);
                }
                objects[hash] = object;
            }
            return hash;
        } else {
            return data;
        }
    }

    function replaceHashWithObjectsRecursive(root, objects, reached) {
        var key, property;
        if (typeof root === 'object' && Array.isArray(root)) {
            for (key in root) {
                if (root.hasOwnProperty(key) === false) {
                    continue;
                }
                root[key] = replaceHashWithObjectsRecursive(root[key], objects, reached);
            }
            return root;
        } else if (typeof root === 'string' && root[0] === '@') {
            if ((root in reached) === false) {
                reached[root] = true;
                for (property in objects[root]) {
                    if (objects[root].hasOwnProperty(property) === false) {
                        continue;
                    }
                    objects[root][property] = replaceHashWithObjectsRecursive(objects[root][property], objects, reached);
                }
            }
            return objects[root];
        } else {
            return root;
        }
    }

    /**
     * Calculate a 32 bit FNV-1a hash
     * Found here: https://gist.github.com/vaiorabbit/5657561
     * Ref.: http://isthe.com/chongo/tech/comp/fnv/
     *
     * @param {string} str the input value
     * @param {boolean} [asString=false] set to true to return the hash value as
     *     8-digit hex string instead of an integer
     * @param {integer} [seed] optionally pass the hash of the previous chunk
     * @returns {integer | string}
     */
    function hashFnv32a(str, asString, seed) {
        /*jshint bitwise:false */
        var i, l, hval = (seed === undefined) ? 0x811c9dc5 : seed;

        for (i = 0, l = str.length; i < l; i++) {
            hval ^= str.charCodeAt(i);
            hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
        }
        if (asString) {
            // Convert to 8 digit hex string
            return ("0000000" + (hval >>> 0).toString(16)).substr(-8);
        }
        return hval >>> 0;
    }

    function getObjectHash(object) {
        var hashParameter, property;
        hashParameter = '';
        if (object.__JSONReference__hash) {
            return object.__JSONReference__hash;
        }
        if (typeof object === 'object' && Array.isArray(object) === false) {
            object.__JSONReference__process = true;
        }
        for (property in object) {
            if (object.hasOwnProperty(property) === false) {
                continue;
            }
            if (property === '__JSONReference__hash') {
                continue;
            }
            if (property === '__JSONReference__process') {
                continue;
            }
            if (typeof object[property] === 'object' && Array.isArray(object[property])) {
                hashParameter += property + '=' + getObjectHash(object[property]);
            } else if (typeof object[property] === 'object' && object[property] !== null) {
                if (!object[property].__JSONReference__process) {
                    hashParameter += property + '=' + getObjectHash(object[property]);
                } else {
                    hashParameter += property + '=[ref]|';
                }
            } else {
                hashParameter += property + '=' + object[property] + '|';
            }
        }
        object.__JSONReference__hash = hashFnv32a(hashParameter, true);
        return object.__JSONReference__hash;
    }

}(JSONReference));
