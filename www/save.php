<?php

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || !array_key_exists('id', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

require dirname(__DIR__) . '/include/config.php';

$filename = ROADBOOKS_DIR . sprintf(FILE_FORMAT, (string) $_POST['id'], 'html');

try {
    $hd = fopen($filename, 'w');
    if (!$hd) {
        throw new Exception("Could not open the file!");
    }

    fwrite($hd, $_POST['content']);
    fclose($hd);
    renderAjax(array('success' => true, 'last_modification'=> 'Last saved: ' . date('Y-m-d H:i:s', filemtime($filename))));
} catch (Exception $e) {
    renderAjax(array('success' => false,
                     'message' => $e->getMessage()));
}
