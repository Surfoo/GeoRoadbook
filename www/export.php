<?php

require dirname(__DIR__) . '/include/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}
if(!array_key_exists('id', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$filename_html = ROADBOOKS_DIR . sprintf(FILE_FORMAT, (string) $_POST['id'], 'html');
$filename_pdf  = ROADBOOKS_DIR . 'pdf/' . sprintf(FILE_FORMAT, (string) $_POST['id'], 'pdf');
$filename_json = ROADBOOKS_DIR . sprintf(FILE_FORMAT, (string) $_POST['id'], 'json');

if (!file_exists($filename_html) || !is_readable($filename_html)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

$options_css = array('page_size' => $_POST['page-size'],
                     'orientation' => $_POST['orientation'],
                     'margin_left' => (int) $_POST['margin-left'],
                     'margin_right' => (int) $_POST['margin-right'],
                     'margin_top' => (int) $_POST['margin-top'],
                     'margin_bottom' => (int) $_POST['margin-bottom'],
                     'header_left' => $_POST['header-left'],
                     'header_center' => $_POST['header-center'],
                     'header_right' => $_POST['header-right'],
                     'footer_left' => $_POST['footer-left'],
                     'footer_center' => $_POST['footer-center'],
                     'footer_right' => $_POST['footer-right'],
                );

$hd = fopen($filename_json, 'w');
fwrite($hd, json_encode($options_css));
fclose($hd);

if(array_key_exists('real_export', $_POST) && $_POST['real_export'] == "false") {
    renderAjax(array('success' => true));
}

require LIB_DIR . 'class.weasyprint.php';

$customCSS_format = "@page {%s}";
$customHeaderFooter = '@%s {%s: "%s"}';
$pageOptions = '';

$globalOptions['size'] = sprintf('%s %s', $options_css['page_size'], $options_css['orientation']);
$globalOptions['margin'] = (int) $options_css['margin_top'] . 'mm ' . (int) $options_css['margin_right'] . 'mm ' . 
                           (int) $options_css['margin_bottom'] . 'mm ' . (int) $options_css['margin_left'] . 'mm';
foreach ($globalOptions as $key => $value) {
    $pageOptions.= sprintf('%s: %s;', $key, $value);
}

foreach ($options_css as &$value) {
    $value = str_replace('[page]', '"counter(page)"', $value);
    $value = str_replace('[topage]', '"counter(pages)"', $value);
    $value = preg_replace('/"{1,}/', '"', $value);
}

if(!empty($options_css['header_left'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'top-left', 'content', $options_css['header_left']);
}
if(!empty($options_css['header_center'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'top-center', 'content', $options_css['header_center']);
}
if(!empty($options_css['header_right'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'top-right', 'content', $options_css['header_right']);
}
if(!empty($options_css['footer_left'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'bottom-left', 'content', $options_css['footer_left']);
}
if(!empty($options_css['footer_center'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'bottom-center', 'content', $options_css['footer_center']);
}
if(!empty($options_css['footer_right'])) {
    $pageOptions.= sprintf($customHeaderFooter, 'bottom-right', 'content', $options_css['footer_right']);
}

$customCSS = sprintf($customCSS_format, $pageOptions);
$customCSS = preg_replace('/"{2,}/', '', $customCSS);

$pdf = new WeasyPrint($filename_html);
if(!$pdf->saveAs($filename_pdf, $customCSS)) {
    renderAjax(array('success' => false, 'error' => ini_get('display_errors') > 0 ? $pdf->getError() : ''));
}

$size = round(filesize($filename_pdf) / 1024 / 1024, 2);

renderAjax(array('success' => true,
                 'size'=> $size,
                 //'command'=> $pdf->command,
                 'link' => '<a href="/roadbook/' . basename($filename_html) . '?pdf">Download your roadbook now</a>'));
