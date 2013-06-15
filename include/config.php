<?php
error_reporting(-1);
ini_set('display_errors', '1');

define('ROOT',          dirname(__DIR__));
define('ROADBOOKS_DIR', ROOT . '/www/roadbook/');
define('LIB_DIR',       ROOT . '/lib/');
define('TEMPLATE_DIR',  ROOT . '/templates/');
define('TEMPLATE_COMPILED_DIR', ROOT . '/templates_c/');

define('ICON_CACHE_DIR', '32x32');

define('MAX_RETENTION', 3600*24*30);

define('JQUERY_VERSION', '2.0.2');
define('BOOTSTRAP_VERSION', '2.3.2');
define('SUFFIX_CSS_JS', 20130615);

date_default_timezone_set('Europe/Paris');

//Locales for the roadbook
$locales = array('cs' => 'Čeština',
                 'de' => 'Deutsch',
                 'en' => 'English',
                 'es' => 'Español',
                 'fr' => 'Français',
                 'it' => 'Italiano',
                 'nl' => 'Nederlands',
                 'pt' => 'Português',
                 'th' => 'ภาษาไทย',
                 'sv' => 'Svenska');

//TinyMCE
$available_languages = array('de', 'en', 'es', 'fr', 'it', 'pt');

$available_sorts = array('none', 'name', 'owner', 'difficulty', 'terrain');

$user_language = false;
if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
$language = in_array($user_language, $available_languages) ? $user_language : 'en';

require ROOT . '/include/helpers.php';

spl_autoload_register(function ($classname) {
    $classname = ltrim($classname, "\\");
    preg_match('/^(.+)?([^\\\\]+)$/U', $classname, $match);
    $classname = str_replace("\\", "/", $match[1]). str_replace(["\\", "_"], "/", $match[2]) . ".php";
    include_once dirname(__DIR__) . '/lib/' . $classname;
});

