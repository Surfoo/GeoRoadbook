<?php

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
umask(0000);

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();

require dirname(__DIR__) . '/app/config.php';
require dirname(__DIR__) . '/app/app.php';

$app->run();
