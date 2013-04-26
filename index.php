<?php

require __DIR__ . '/include/config.php';
require LIB_DIR . 'smarty/Smarty.class.php';

header('Content-type: text/html; charset=utf-8');
$smarty = new Smarty();

if(array_key_exists('deleted', $_GET)) {
    $smarty->assign('deleted', true);
}
$smarty->assign('max_filesize', ini_get('upload_max_filesize'));
$smarty->display('templates/index.tpl');
