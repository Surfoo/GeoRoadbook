<?php

require __DIR__ . '/include/config.php';

if (!array_key_exists('roadbook', $_GET)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

$filename_html = sprintf(FILE_FORMAT, $_GET['roadbook'], 'html');
$filename_pdf  = sprintf(FILE_FORMAT, $_GET['roadbook'], 'pdf');
$path_filename_html = ROADBOOKS_DIR . $filename_html;
$path_filename_pdf  = ROADBOOKS_DIR . 'pdf/' . $filename_pdf;

if (!file_exists($path_filename_html) || !is_readable($path_filename_html)) {
    header("HTTP/1.0 404 Not Found");
    //TODO Page 404
    exit(0);
}

if(array_key_exists('pdf', $_GET)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=roadbook.pdf');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path_filename_pdf));
    ob_clean();
    flush();
    readfile($path_filename_pdf);
    exit(0);
}

if(array_key_exists('zip', $_GET) && ZIP_ARCHIVE && class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $filename_zip = sprintf(FILE_FORMAT, $_GET['roadbook'], 'zip');

    if ($zip->open(ROADBOOKS_DIR . $filename_zip, ZIPARCHIVE::CREATE) !== true) {
        exit('Unable to open ' . $filename_zip);
    }

    $zip->addFromString('roadbook/' . $filename_html, file_get_contents($path_filename_html));
    $zip->addFile('css/mycontent.css');
    recurse_zip('img', $zip);
    $zip->close();

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=roadbook.zip');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(ROADBOOKS_DIR . $filename_zip));
    ob_clean();
    flush();
    readfile(ROADBOOKS_DIR . $filename_zip);
    unlink(ROADBOOKS_DIR . $filename_zip);
    exit(0);
}

if(array_key_exists('raw', $_GET)) {
    echo file_get_contents($path_filename_html);
    exit(0);
}

require LIB_DIR . 'smarty/Smarty.class.php';
$smarty = new Smarty();

$smarty->assign('language', $language);
$smarty->assign('roadbook_id', $_GET['roadbook']);
$smarty->assign('roadbook_content', file_get_contents($path_filename_html));
if(ZIP_ARCHIVE && class_exists('ZipArchive')) {
    $smarty->assign('available_zip', true);
}
if(file_exists($path_filename_pdf)) {
    $smarty->assign('available_pdf', true);
}
$smarty->display('../templates/edit.tpl');
