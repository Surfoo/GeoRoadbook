<?php
error_reporting(-1);
ini_set('display_errors', '1');

define('ROOT',          dirname(__DIR__));
define('UPLOAD_DIR',    ROOT . '/upload/');
define('ROADBOOKS_DIR', ROOT . '/roadbook/');
define('LIB_DIR',       ROOT . '/lib/');

define('ICON_CACHE_DIR', '32x32');

define('FILE_FORMAT', '%s.%s');
define('ZIP_ARCHIVE', false);

$locales = array('en', 'fr');
$mimes   = array('application/xml');
$available_languages = array('de', 'en', 'es', 'fr', 'it', 'pt');
$available_options_pdf = array('page-size', 'grayscale', 'orientation', 'margin-left',
                               'margin-right', 'margin-top', 'margin-bottom',
                               'header-left', 'header-center', 'header-right', 'header-line',
                               'footer-left', 'footer-center', 'footer-right', 'footer-line');

$user_language = false;
if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
$language = in_array($user_language, $available_languages) ? $user_language : 'en';

require ROOT . '/include/helpers.php';