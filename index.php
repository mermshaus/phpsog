<?php

namespace org\ermshaus\phpsog;

ob_start();

try {
    require_once 'phpsog.php';
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

$data = ob_get_clean();

?><!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <title>phpsog</title>
    </head>

    <body>

        <pre><?=e($data);?></pre>

    </body>

</html>
