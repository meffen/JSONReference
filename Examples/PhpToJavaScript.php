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
        <h1>JavaScript</h1>
        <pre>
            dad.firstname = <span id="firstname1"></span>
            dad.children[0].parent.firstname = <span id="firstname2"></span>

            dad.firstname = 'Not Max';

            dad.firstname = <span id="firstname3"></span>
            dad.children[0].parent.firstname = <span id="firstname4"></span>
        </pre>
        <script src="../JSONReference.js"></script>
        <script>
            var data = <?php echo json_encode(JSONReference::encode($dad)); ?>;

            dad = JSONReference.decode(data);

            document.getElementById('firstname1').innerHTML = dad.firstname;
            document.getElementById('firstname2').innerHTML = dad.children[0].parent.firstname;
            
            dad.firstname = 'Not Max';
            
            document.getElementById('firstname3').innerHTML = dad.firstname;
            document.getElementById('firstname4').innerHTML = dad.children[0].parent.firstname;
        </script>
    </body>
</html>