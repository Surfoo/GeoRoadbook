<?php

require dirname(__DIR__) . '/include/config.php';

georoadbook::ajaxRequestOnly();

if (!array_key_exists('page-size', $_POST) ||
    !array_key_exists('orientation', $_POST) ||
    !array_key_exists('margin-left', $_POST) ||
    !array_key_exists('margin-right', $_POST) ||
    !array_key_exists('margin-top', $_POST) ||
    !array_key_exists('margin-bottom', $_POST) ||
    !array_key_exists('header-align', $_POST) ||
    !array_key_exists('header-text', $_POST) ||
    !array_key_exists('header-pagination', $_POST) ||
    !array_key_exists('footer-align', $_POST) ||
    !array_key_exists('footer-text', $_POST) ||
    !array_key_exists('footer-pagination', $_POST)) {
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
                     'header_align' => $_POST['header-align'],
                     'header_text' => $_POST['header-text'],
                     'header_pagination' => $_POST['header-pagination'] == "true" ? 1 : 0,
                     'footer_align' => $_POST['footer-align'],
                     'footer_text' => $_POST['footer-text'],
                     'footer_pagination' => $_POST['footer-pagination'] == "true" ? 1 : 0,
                );

$rdbk->saveOptions($options_css);

if (array_key_exists('real_export', $_POST) && $_POST['real_export'] == "false") {
    renderAjax(array('success' => true));
}

if(!$rdbk->export()) {
    renderAjax(array('success' => false, 'error' => $rdbk->result));
}

renderAjax(array('success' => true,
                 'size'=> round(filesize($rdbk->pdf_file) / 1024 / 1024, 2),
                 // 'command'=> $pdf->command,
                 'link' => '<a href="/roadbook/' . basename($rdbk->html_file) . '?pdf">Download your roadbook now</a>'));
