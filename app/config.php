<?php

date_default_timezone_set('Europe/Paris');

// Locales for the roadbook
$app['locales'] = [
        'cs' => 'Čeština',
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
        'th' => 'ภาษาไทย'
];

$app['suffix_css_js'] = '20170403';
$app['root'] = dirname(__DIR__);
$app['roadbook_dir'] = dirname(__DIR__) . '/web/roadbook';
$app['max_retention'] = 3600 * 24 * 30;
$app['icon_cache_dir'] = '32x32';
$app['available_sorts'] = array('none', 'name', 'owner', 'difficulty', 'terrain');

// TinyMCE available language
$available_languages = array('cs', 'da', 'de', 'en_GB', 'es', 'fr_FR', 'it', 'nl');
// TinyMCE Default language
$app['language'] = 'en';

if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($user_language, $available_languages)) {
        $app['language'] = $user_language;
    }
}