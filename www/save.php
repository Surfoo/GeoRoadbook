<?php

if(!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

require dirname(__DIR__) . '/include/config.php';

$filename = ROADBOOKS_DIR . sprintf(FILE_FORMAT, (string) $_POST['id'], 'html');

try {
    $hd = fopen($filename, 'w');
    if(!$hd) {
        throw new Exception("Could not open the file!");
    }

    fwrite($hd, $_POST['content']);
    fclose($hd);
    renderAjax(array('success' => true));
}
catch(Exception $e) {
    renderAjax(array('success' => false,
                     'message' => $e->getMessage()));
}

renderAjax(array('success' => false));
