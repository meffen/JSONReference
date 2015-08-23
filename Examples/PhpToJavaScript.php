<?php
/**
 * @author Steffen Maechtel <info@steffen-maechtel.de>
 * @copyright 2015 Steffen Maechtel
 * @license MIT
 */
require_once '../JSONReference.php';

/**
 * Example data
 */
$dad = (object) array('firstname' => 'Max', 'lastname' => 'Mustermann', 'parent' => null, 'children' => array());
$son = (object) array('firstname' => 'Herbert', 'lastname' => 'Mustermann', 'parent' => null, 'children' => array());
$son->parent = $dad;
$dad->children[] = $son;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>PhpToJavaScript</title>
    </head>
    <body>
        <h1>Output PHP Data</h1>
        <pre>
            <?php print_r($dad); ?>
        </pre>
        <h1>Output PHP Data encoded with JSONReference::encode</h1>
        <pre>
            <?php print_r(JSONReference::encode($dad)); ?>
        </pre>
        <h1>Output JSON encoded</h1>
        <pre>
            <?php echo json_encode(JSONReference::encode($dad)); ?>
        </pre>
    </body>
</html>