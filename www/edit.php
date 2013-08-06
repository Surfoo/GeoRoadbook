<?php

require dirname(__DIR__) . '/include/config.php';

if (!array_key_exists('roadbook', $_GET)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => false, 'cache' => TEMPLATE_COMPILED_DIR));

use Geocaching\Georoadbook\Georoadbook;

$rdbk = new Georoadbook($_GET['roadbook']);

if (!file_exists($rdbk->html_file) || !is_readable($rdbk->html_file)) {
    header("HTTP/1.0 404 Not Found");
    exit(0);
}

if (array_key_exists('pdf', $_GET)) {
    $rdbk->downloadPdf();
}

if (array_key_exists('zip', $_GET)) {
    $rdbk->downloadZip();
}

if (array_key_exists('raw', $_GET)) {
    $twig_vars = array('style' => $rdbk->getCustomCss(),
                       'content' => file_get_contents($rdbk->html_file));
    echo $twig->render('raw.tpl', $twig_vars);
    exit(0);
}

$twig_vars = array('language'          => $language,
                   'roadbook_id'       => $rdbk->id,
                   'roadbook_content'  => file_get_contents($rdbk->html_file),
                   'last_modification' => 'Last saved: ' . $rdbk->getLastSavedDate()
                   );

if (class_exists('ZipArchive')) {
    $twig_vars['available_zip'] = true;
}
if (file_exists($rdbk->pdf_file)) {
    $twig_vars['available_pdf'] = true;
}

echo $twig->render('edit.tpl', $twig_vars);
