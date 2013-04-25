<?php

require __DIR__ . '/include/config.php';
require LIB_DIR . 'smarty/Smarty.class.php';

$smarty = new Smarty();

if (!empty($_POST)) {

    $errors = array();
    if (empty($_FILES['gpx']['tmp_name'])) {
        $errors[] = 'MISSING_FILE';
    }
    if (isset($_POST['locale']) && !in_array($_POST['locale'], $locales)) {
        $errors[] = 'INVALID_LOCALE';
    }

    if (empty($errors) && $_FILES['gpx']['error'] == UPLOAD_ERR_OK) {

        $finfo = new finfo(FILEINFO_MIME);
        $type = $finfo->file($_FILES['gpx']['tmp_name']);

        $mime = substr($type, 0, strpos($type, ';'));
        if (!in_array($mime, $mimes)) {
            unlink($_FILES['gpx']['tmp_name']);
            $errors[] = 'INVALID_FILE';
        }
    }

    if (!empty($errors)) {
        $smarty->assign('errors', $errors);
    } else {
        //detect shema of geocaching gpx
        $xml = new DOMDocument();
        $xml->load($_FILES['gpx']['tmp_name']);
        $searchNode = $xml->getElementsByTagName('gpx');
        $schemas = null;
        foreach ($searchNode as $searchNode) {
            $schemas = explode(' ', $searchNode->getAttribute('xsi:schemaLocation'));
        }
        if (!is_array($schemas)) {
            $errors[] = 'INVALID_SCHEMA';
        } else {
            foreach ($schemas as $schema) {
                if (preg_match('!^http://www.groundspeak.com/cache/([0-9/]*)$!i', $schema, $matche)) {
                    break;
                }
            }

            if (!array_key_exists(1, $matche)) {
                $errors[] = 'INVALID_SCHEMA';
            } else {
                $schema_version = $matche[1];
            }
        }

        $current_locale = $locales[0];
        if(array_key_exists('locale', $_POST) && in_array($_POST['locale'], $locales)) {
            $current_locale     = $_POST['locale'];
        }
        $display_note       = isset($_POST['note'])      ? true : false;
        $display_short_desc = isset($_POST['short_desc'])? true : false;
        $display_hint       = isset($_POST['hint'])      ? true : false;
        $display_logs       = isset($_POST['logs'])      ? true : false;
        $hint_encrypted     = (bool) (isset($_POST['hint_encrypted'] ) ? (int) $_POST['hint_encrypted'] : 0);

        $uniqid = substr(md5(uniqid(rand(), true)), 0, 16);
        $filename_gpx = sprintf(FILE_FORMAT, $uniqid, 'gpx');

        if (!move_uploaded_file($_FILES['gpx']['tmp_name'], UPLOAD_DIR . $filename_gpx)) {
           exit();
        }

        $xsl = new XSLTProcessor();
        $xsldoc = new DOMDocument();

        if ($schema_version == '1/0/1') {
            $xsldoc->load('templates/xslt/roadbook101.xslt'); // schema 1/0/1/cache.xsd
        } else {
            $xsldoc->load('templates/xslt/roadbook1.xslt'); // schema 1/0/cache.xsd
        }

        $xsl->importStyleSheet($xsldoc);

        $xmldoc = new DOMDocument();
        $xmldoc->load(UPLOAD_DIR . $filename_gpx);

        $xsl->setParameter('', 'locale_filename', '../../locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
        $xsl->setParameter('', 'icon_cache_dir', ICON_CACHE_DIR);
        $xsl->setParameter('', 'display_note', $display_note);
        $xsl->setParameter('', 'display_short_desc', $display_short_desc);
        $xsl->setParameter('', 'display_hint', $display_hint);
        $xsl->setParameter('', 'display_logs', $display_logs);

        $html = $xsl->transformToXML($xmldoc);

        if($hint_encrypted) {
            $dom = new DomDocument();
            $dom->loadHTML($html);
            $finder = new DomXPath($dom);
            $classname="cacheHintContent";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            foreach($nodes as $node) {
                $encoded_hint = str_rot13($node->textContent);
                $node->nodeValue = $encoded_hint;
            }
            $html = $dom->saveHtml();
        }


        $html = preg_replace('/<\?xml[^>]*\?>/i', '', $html);
        $html = preg_replace('/^<!DOCTYPE.*\s<html[^>]*>$/mi', '<!DOCTYPE html>'."\n".'<html lang="' . $current_locale. '">', trim($html));
        $html = htmlspecialchars_decode($html);

        if(isset($_POST['typog­ra­phy'])) {
            require LIB_DIR . '/php-typography/php-typography.php';
            $typo = new phpTypography();
            $html = $typo->process($html);
        }
        //die(str_replace("../img/", "img/", str_replace("../css/", "css/", $html)));

        $filename_html = sprintf(FILE_FORMAT, $uniqid, 'html');
        $filename_json = sprintf(FILE_FORMAT, $uniqid, 'json');

        $hd = fopen(ROADBOOKS_DIR . $filename_html, 'w');
        fwrite($hd, $html);
        fclose($hd);
        touch(ROADBOOKS_DIR . $filename_json);

        header('Location: roadbook/' . $filename_html);
        exit(0);
    }
}

header('Content-type: text/html; charset=utf-8');

if(array_key_exists('deleted', $_GET)) {
    $smarty->assign('deleted', true);
}
$smarty->assign('max_filesize', ini_get('upload_max_filesize'));
$smarty->display('templates/index.tpl');
