<?php

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    die('not an ajax request.');
}

require 'include/config.php';
require 'lib/WkHtmlToPdf.php';

$filename = ROADBOOKS_DIR . sprintf(FILE_FORMAT, (string) $_POST['id'], 'html');

if (!file_exists($filename) || !is_readable($filename)) {
    die('error roadbookeee');
}

$options = array('bin' => '/usr/local/bin/wkhtmltopdf',
                 'enableEscaping' => false,
                 'tmp' => ROADBOOKS_DIR . 'pdf/',
                 'page-size' => 'A4');

foreach($_POST as $key => $value) {
    if(!in_array($key, $available_options_pdf))
        continue;
    if(!empty($value)) {
        $value = utf8_decode($value);
        if($value == 'false')
            continue;
        if($value == 'true') {
            $value = '';
        }
        if(!is_numeric($value) && !empty($value)) {
            $value = '"' . $value . '"';
        }
        $options[$key] =  $value;
    }
}

$pdf = new WkHtmlToPdf($options);

// Add a cover (same sources as above are possible)
//$pdf->addCover($filename);

// Add a Table of contents
//$tocOptions = array('xsl-style-sheet' => 'templates/xslt/toc.xsl');
//$pdf->addToc($tocOptions);

// Add a HTML file, a HTML string or a page from a URL
$pageOptions = array();
$pdf->addPage($filename, $pageOptions);

$filename_pdf = sprintf(FILE_FORMAT, (string) $_POST['id'], 'pdf');
$path_pdf     = ROADBOOKS_DIR . 'pdf/' . $filename_pdf;

if (!$pdf->saveAs($path_pdf)) {
    renderAjax(array('success' => false, 'error' => $pdf->getError()));
}
$size = round(filesize($path_pdf) / 1024 / 1024, 2);

renderAjax(array('success' => true,
                 'size'=> $size,
                 'link' => '<a href="../../../../download.php?roadbook=' . $_POST['id'] . '">Download your roadbook</a>'));
