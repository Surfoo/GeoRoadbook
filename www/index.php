<?php

require dirname(__DIR__) . '/include/config.php';
require LIB_DIR . 'class.smarty_georoadbook.php';

header('Content-type: text/html; charset=utf-8');

if(array_key_exists('deleted', $_GET)) {
    $smarty->assign('deleted', true);
}
$smarty->assign('language', $language);
$smarty->display('index.tpl');
