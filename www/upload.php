<?php

if (empty($_POST) || !array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

require dirname(__DIR__) . '/include/config.php';

//Mandatory data
if (!array_key_exists('gpx', $_POST) || empty($_POST['gpx'])) {
    renderAjax(array('success' => false, 'message' => 'GPX file is missing.'));
}
if (!array_key_exists('locale', $_POST) || !in_array($_POST['locale'], array_keys($locales))) {
    renderAjax(array('success' => false, 'message' => 'Locale is missing or invalid.'));
}

$xml_content = $_POST['gpx'];

//detect shema of geocaching gpx
try {
    $sxe = new SimpleXMLElement($xml_content);
} catch (Exception $e) {
    renderAjax(array('success' => false, 'message' => 'Not a xml file.'));
}
$schemaLocation = (string) $sxe->attributes('xsi', true)->schemaLocation;
preg_match('!http://www.groundspeak.com/cache/([0-9/]*)!i', $schemaLocation, $matche);

if (!array_key_exists(1, $matche)) {
    renderAjax(array('success' => false, 'message' => 'Schema is invalid.'));
}
if ($matche[1] == '1/0') {
    renderAjax(array('success' => false, 'message' => 'GPX schema 1/0 is not supported, please use schema 1/0/1.'));
}

$current_locale     = $_POST['locale'];
$html_tidy          = isset($_POST['tidy']) && $_POST['tidy'] == "true"                     ? true : false;
$display_toc        = isset($_POST['toc']) && $_POST['toc']   == "true"                     ? true : false;
$display_note       = isset($_POST['note']) && $_POST['note'] == "true"                     ? true : false;
$display_short_desc = isset($_POST['short_desc']) && $_POST['short_desc'] == "true"         ? true : false;
$display_hint       = isset($_POST['hint']) && $_POST['hint'] == "true"                     ? true : false;
$display_logs       = isset($_POST['logs']) && $_POST['logs'] == "true"                     ? true : false;
$hint_encrypted     = isset($_POST['hint_encrypted']) && $_POST['hint_encrypted'] == "true" ? true : false;

$uniqid = substr(md5(uniqid(mt_rand(), true)), 0, 16);

$filename_gpx = sprintf(FILE_FORMAT, $uniqid, 'gpx');
$hd = fopen(ROADBOOKS_DIR . $filename_gpx, 'w');
fwrite($hd, $_POST['gpx']);
fclose($hd);

$xsldoc = new DOMDocument();
$xsldoc->load(ROOT . '/templates/xslt/roadbook.xslt');

$xsl = new XSLTProcessor();
$xsl->importStyleSheet($xsldoc);
$xsl->setParameter('', 'locale_filename', ROOT . '/locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
$xsl->setParameter('', 'icon_cache_dir', ICON_CACHE_DIR);
$xsl->setParameter('', 'display_note', $display_note);
$xsl->setParameter('', 'display_short_desc', $display_short_desc);
$xsl->setParameter('', 'display_hint', $display_hint);
$xsl->setParameter('', 'display_logs', $display_logs);

$xml = new DOMDocument();
$xml->loadXML($xml_content);
$html = $xsl->transformToXML($xml);
$html = preg_replace('/<\?xml[^>]*\?>/i', '', $html);
$html = preg_replace('/^<!DOCTYPE.*\s<html[^>]*>$/mi', '<!DOCTYPE html>'."\n".'<html lang="' . $current_locale. '">', trim($html));
$html = htmlspecialchars_decode($html);

// HTML Tidy
if ($html_tidy) {
    // http://tidy.sourceforge.net/docs/quickref.html
    $config = array(
               'doctype'        => 'html',
               'output-xhtml'   => true,
               'wrap'           => 0);

    $tidy = new tidy;
    $tidy->parseString($html, $config, 'utf8');
    $tidy->cleanRepair();
    $html = $tidy;
}

// Table of content
if ($display_toc) {
    $dom = new DomDocument();
    $dom->loadHTML($html);
    $finder = new DomXPath($dom);
    $classname="cacheTitle";
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    $toc_content = array();
    foreach ($nodes as $node) {
        $icon  = $node->firstChild->getAttribute('src');
        $title = $node->textContent;
        $toc_content[] = array('icon' => $icon, 'title' => $title);
    }

    if (!empty($toc_content)) {
        $toc = new DomDocument();
        $toc->load(ROOT . '/locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
        $xPath = new DOMXPath($toc);
        $toc_i18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
        $toc_i18n['name']  = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
        $toc_i18n['page']  = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

        require LIB_DIR . 'class.smarty_georoadbook.php';
        $smarty->assign('i18n', $toc_i18n);
        $smarty->assign('content', $toc_content);
        $toc_html = $smarty->fetch('toc.tpl');

        $frag = $dom->createDocumentFragment();
        $frag->appendXML($toc_html);

        $body = $dom->getElementsByTagName('body')->item(0);
        $first = $body->getElementsByTagName('div')->item(0);
        $body->insertBefore($frag, $first);
        // $body->appendChild($frag);
        $html = $dom->saveHtml();
    }
}

// Hint
if ($display_hint && $hint_encrypted) {
    $dom = new DomDocument();
    $dom->loadHTML($html);
    $finder = new DomXPath($dom);
    $classname="cacheHintContent";
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    foreach ($nodes as $node) {
        $encoded_hint = str_rot13($node->textContent);
        $node->nodeValue = $encoded_hint;
    }
    $html = $dom->saveHtml();
}

// if (isset($_POST['typog­ra­phy'])) {
//     require LIB_DIR . '/php-typography/php-typography.php';
//     $typo = new phpTypography();
//     $html = $typo->process($html);
// }

$filename_html = sprintf(FILE_FORMAT, $uniqid, 'html');
$filename_json = sprintf(FILE_FORMAT, $uniqid, 'json');

$hd = fopen(ROADBOOKS_DIR . $filename_html, 'w');
fwrite($hd, $html);
fclose($hd);
touch(ROADBOOKS_DIR . $filename_json);

renderAjax(array('success' => true, 'redirect' => 'roadbook/' . $filename_html));
