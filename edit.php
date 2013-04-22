<?php

require __DIR__ . '/include/config.php';

if (!array_key_exists('roadbook', $_GET)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

$filename = ROADBOOKS_DIR . sprintf(FILE_FORMAT, $_GET['roadbook'], 'html');

if (!file_exists($filename) || !is_readable($filename)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

$roabook_content = file_get_contents($filename);

if(array_key_exists('raw', $_GET)) {
    echo $roabook_content;
    exit(0);
}
require LIB_DIR . 'smarty/Smarty.class.php';
$smarty = new Smarty();

$smarty->assign('language', $language);
$smarty->assign('roabook_id', $_GET['roadbook']);
$smarty->assign('roabook_content', $roabook_content);

$smarty->display('../templates/edit.tpl');
