<?php

require 'include/config.php';

if(!array_key_exists('roadbook', $_GET)) {
	die();
}

$filename = ROADBOOKS_DIR . 'pdf/' . sprintf(FILE_FORMAT, $_GET['roadbook'], 'pdf');

if(!file_exists($filename) || !is_readable($filename)) {
	die();
}

if(file_exists($filename)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=roadbook_' . date('Ymd-His') . '.pdf');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    ob_clean();
    flush();
    readfile($filename);
    exit(0);
}
