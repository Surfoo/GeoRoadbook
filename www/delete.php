<?php

require dirname(__DIR__) . '/include/config.php';

if (!array_key_exists('roadbook', $_POST) || !preg_match('/^[a-z0-9]*$/', $_POST['roadbook'])) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$pattern     = ROADBOOKS_DIR . basename($_POST['roadbook']) . '.*';
$filename_pdf = ROADBOOKS_DIR . 'pdf/'. basename($_POST['roadbook']) . '.pdf';

foreach(glob($pattern) as $file) {
    @unlink($file);
}

@unlink($filename_pdf);

renderAjax(array('success' => true));