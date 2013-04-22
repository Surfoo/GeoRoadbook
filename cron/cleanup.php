<?php

require dirname(__DIR__) . '/include/config.php';

$now = time();
foreach(glob(ROADBOOKS_DIR . '*.*') as $file) {
    if($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "unlink ".$file."\n";
    }
}

foreach(glob(ROADBOOKS_DIR . 'pdf/*.pdf') as $file) {
    if($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "unlink ".$file."\n";
    }
}