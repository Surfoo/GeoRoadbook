<?php

require dirname(__DIR__) . '/include/config.php';

georoadbook::ajaxRequestOnly();

if (!array_key_exists('id', $_POST) || !array_key_exists('content', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$rdbk = new georoadbook($_POST['id']);

//hack, bug in TinyMCE
$html = preg_replace('/<head>\s*<\/head>/m', '<head><meta charset="utf-8" /><title>My roadbook</title><link type="text/css" rel="stylesheet" href="../css/roadbook.css" media="all" /></head>', $_POST['content'], 1);
$rdbk->saveFile($rdbk->html_file, $html);

if(!$rdbk->saveFile($rdbk->html_file, $_POST['content'])) {
    renderAjax(array('success' => false));
}

renderAjax(array('success' => true, 'last_modification'=> 'Last saved: ' . $rdbk->getLastSavedDate()));
