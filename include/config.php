<?php
error_reporting(-1);
ini_set('display_errors', '1');

define('ROOT',          dirname(__DIR__));
define('ROADBOOKS_DIR', ROOT . '/web/roadbook/');
define('TEMPLATE_DIR',  ROOT . '/templates');
define('TEMPLATE_COMPILED_DIR', ROOT . '/cache');

define('ICON_CACHE_DIR', '32x32');

define('MAX_RETENTION', 3600*24*30);

define('JQUERY_VERSION', '2.0.3');
define('BOOTSTRAP_VERSION', '2.3.2');
define('SUFFIX_CSS_JS', 20130920);

date_default_timezone_set('Europe/Paris');

//Locales for the roadbook
$locales = array('cs' => 'Čeština',
                 'da' => 'Dansk',
                 'de' => 'Deutsch',
                 'en' => 'English',
                 'es' => 'Español',
                 'fr' => 'Français',
                 'it' => 'Italiano',
                 'lt' => 'Lietuvių',
                 'nl' => 'Nederlands',
                 'pt' => 'Português',
                 'sv' => 'Svenska',
                 'th' => 'ภาษาไทย');

//TinyMCE
$available_languages = array('cs', 'da', 'de', 'en', 'es', 'fr', 'it', 'nl');

$available_sorts = array('none', 'name', 'owner', 'difficulty', 'terrain');

$language = 'en';
if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if(in_array($user_language, $available_languages)) {
        $language = $user_language;
    }
}

require ROOT . '/include/helpers.php';
require ROOT . '/vendor/autoload.php';
