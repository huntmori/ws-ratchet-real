<?php

use App\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

echo <<<TEXT
==============================================================================================
TEXT . PHP_EOL;


$app = new Application();
$app->host = '0.0.0.0';
$app->port = 8888;
$app->bootstrap()
    ->run();
echo <<<TEXT
==============================================================================================
TEXT . PHP_EOL;