<?php

require 'include/config.php';

if (!array_key_exists('roadbook', $_GET)) {
    die();
}

$filename = ROADBOOKS_DIR . sprintf(FILE_FORMAT, $_GET['roadbook'], 'html');

if (!file_exists($filename) || !is_readable($filename)) {
    die('error roadbook');
}

require LIB_DIR . 'smarty/Smarty.class.php';
$smarty = new Smarty();

$roabook = $_GET['roadbook'];
$roabook_content = file_get_contents($filename);

$smarty->assign('language', $language);
$smarty->assign('roabook_id', $roabook);
$smarty->assign('roabook_content', $roabook_content);

$smarty->display('../templates/edit.tpl');
