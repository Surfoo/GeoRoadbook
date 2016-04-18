<?php

require dirname(__DIR__) . '/include/config.php';

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => false, 'cache' => false));

header('Content-type: text/html; charset=utf-8');

$twig_vars = array('locales'           => $locales,
                   'language'          => $language,
                   );

if (array_key_exists('deleted', $_GET)) {
    $twig_vars['deleted'] = true;
}

echo $twig->render('index.tpl', $twig_vars);
