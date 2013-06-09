<?php

require dirname(__DIR__) . '/include/config.php';
require LIB_DIR . 'class.smarty_georoadbook.php';

header('Content-type: text/html; charset=utf-8');

if (array_key_exists('deleted', $_GET)) {
    $smarty->assign('deleted', true);
}
$smarty->assign('jquery_version', JQUERY_VERSION);
$smarty->assign('bootstrap_version', BOOTSTRAP_VERSION);
$smarty->assign('suffix_css_js', SUFFIX_CSS_JS);
$smarty->assign('locales', $locales);
$smarty->assign('language', $language);
$smarty->display('index.tpl');
