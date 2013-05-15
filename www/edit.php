<?php

require dirname(__DIR__) . '/include/config.php';

if (!array_key_exists('roadbook', $_GET)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

$rdbk = new georoadbook($_GET['roadbook']);

if (!file_exists($rdbk->html_file) || !is_readable($rdbk->html_file)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

if (array_key_exists('pdf', $_GET)) {
    $rdbk->downloadPdf();
}

if (array_key_exists('zip', $_GET) && class_exists('ZipArchive')) {
    $rdbk->downloadZip();
}

if (array_key_exists('raw', $_GET)) {
    $html = file_get_contents($rdbk->html_file);
    
    //hack, bug in TinyMCE
    $html = preg_replace('/<head>\s*<\/head>/m', '<head><meta charset="utf-8" /><title>My roadbook</title><link type="text/css" rel="stylesheet" href="../design/roadbook.css" media="all" /></head>', $html, 1);
    $rdbk->saveFile($rdbk->html_file, $html);

    $options_css = $rdbk->getCustomCss();
    if ($options_css) {
        $customCSS = '<style type="text/css">' . $options_css . '</style>';
        $html = str_replace("</head>", $customCSS . "</head>", $html);
    }
    echo $html;
    exit(0);
}

require LIB_DIR . 'class.smarty_georoadbook.php';

$smarty->assign('language', $language);
$smarty->assign('roadbook_id', $rdbk->id);
$smarty->assign('roadbook_content', file_get_contents($rdbk->html_file));
$smarty->assign('last_modification', 'Last saved: ' . $rdbk->getLastSavedDate());

if (class_exists('ZipArchive')) {
    $smarty->assign('available_zip', true);
}
if (file_exists($rdbk->pdf_file)) {
    $smarty->assign('available_pdf', true);
}
$smarty->display('../templates/edit.tpl');
