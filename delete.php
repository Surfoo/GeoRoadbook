<?php

require __DIR__ . '/include/config.php';

if (!array_key_exists('roadbook', $_POST) || !preg_match('/^[a-z0-9]*$/', $_POST['roadbook'])) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$pattern     = ROADBOOKS_DIR . basename($_POST['roadbook']) . '.*';
$pattern_pdf = ROADBOOKS_DIR . 'pdf/'. basename($_POST['roadbook']) . '.pdf';

foreach(glob($pattern) as $file) {
    @unlink($file);
}

foreach(glob($pattern_pdf) as $file) {
    @unlink($file);
}

renderAjax(array('success' => true));