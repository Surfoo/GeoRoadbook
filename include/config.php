<?php
error_reporting(-1);
ini_set('display_errors', '1');

define('ROOT',          dirname(__DIR__));
define('ROADBOOKS_DIR', ROOT . '/www/roadbook/');
define('LIB_DIR',       ROOT . '/lib/');
define('TEMPLATE_DIR',  ROOT . '/templates/');
define('TEMPLATE_COMPILED_DIR', ROOT . '/templates_c/');

define('ICON_CACHE_DIR', '32x32');

define('FILE_FORMAT', '%s.%s');
define('MAX_RETENTION', 3600*24*30);
define('ZIP_ARCHIVE', true);

//Locales for the roadbook
$locales = array('de' => 'Deutsch',
                 'en' => 'English',
                 'fr' => 'Français');

//TinyMCE
$available_languages = array('de', 'en', 'es', 'fr', 'it', 'pt');

$available_options_pdf = array('page-size', 'orientation', 'margin-left',
                               'margin-right', 'margin-top', 'margin-bottom',
                               'header-left', 'header-center', 'header-right', 'header-line', 'header-spacing',
                               'footer-left', 'footer-center', 'footer-right', 'footer-line', 'footer-spacing');

$user_language = false;
if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
$language = in_array($user_language, $available_languages) ? $user_language : 'en';

require ROOT . '/include/helpers.php';
