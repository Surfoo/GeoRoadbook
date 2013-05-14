<?php

require dirname(__DIR__) . '/include/config.php';

georoadbook::ajaxRequestOnly();

if (!array_key_exists('id', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$rdbk = new georoadbook($_POST['id']);

if (!file_exists($rdbk->html_file) || !is_readable($rdbk->html_file)) {
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

$rdbk->saveOptions($options_css);

if (array_key_exists('real_export', $_POST) && $_POST['real_export'] == "false") {
    renderAjax(array('success' => true));
}

if(!$rdbk->export($options_css)) {
    renderAjax(array('success' => false, 'error' => $rdbk->export_errors));
}

renderAjax(array('success' => true,
                 'size'=> round(filesize($rdbk->pdf_file) / 1024 / 1024, 2),
                 // 'command'=> $pdf->command,
                 'link' => '<a href="/roadbook/' . basename($rdbk->html_file) . '?pdf">Download your roadbook now</a>'));
