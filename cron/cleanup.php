<?php

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/app/config.php';

$now = time();

foreach (glob($app['roadbook_dir'] . '/*.*') as $file) {
    if ($now > filemtime($file) + $app['max_retention'] && unlink($file)) {
        echo "unlinked " . $file . "\n";
    }
}

foreach (glob($app['roadbook_dir'] . '/pdf/*.pdf') as $file) {
    if ($now > filemtime($file) + $app['max_retention'] && unlink($file)) {
        echo "unlinked " . $file . "\n";
    }
}

echo "done.";
