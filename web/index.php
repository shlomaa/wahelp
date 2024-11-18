<?php

$autoload = require_once '../src/autoload.php';
foreach ($autoload as $class) {
    require_once '../src/' . $class . '.php';
}

//global $config;
$config = require_once '../config.php';

use classes\Application;
(new Application($config))->run();
