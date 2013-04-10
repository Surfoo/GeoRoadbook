<?php

require 'include/config.php';
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

    if ($_FILES['gpx']['error'] == UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME);
        $type = $finfo->file($_FILES['gpx']['tmp_name']);

        $mime = substr($type, 0, strpos($type, ';'));
        if (!in_array($mime, $mimes)) {
            @unlink($_FILES['gpx']['tmp_name']);
            exit();
        }

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

        $current_locale  = isset($_POST['locale'])    ? $_POST['locale']  : $locales[0];
        $display_logs    = isset($_POST['logs'])      ? true : false;
        $zipArchiveClass = ZIP_ARCHIVE && class_exists('ZipArchive') ? true : false;
    }

    if (!empty($errors)) {
        $smarty->assign('errors', $errors);
    } else {
        $uniqid = uniqid();
        $gpx_filename = sprintf(FILE_FORMAT, $uniqid, 'gpx');

        if (!move_uploaded_file($_FILES['gpx']['tmp_name'], UPLOAD_DIR . $gpx_filename)) {
           exit();
        }

        $xsl = new XSLTProcessor();
        $xsldoc = new DOMDocument();

        if ($schema_version == '1/0/1') {
            $xsldoc->load('templates/xslt/roadbook101-table.xslt'); // schema 1/0/1/cache.xsd
        } else {
            $xsldoc->load('templates/xslt/roadbook1-table.xslt'); // schema 1/0/cache.xsd
        }

        $xsl->importStyleSheet($xsldoc);

        $xmldoc = new DOMDocument();
        $xmldoc->load(UPLOAD_DIR . $gpx_filename);

        $zip_filename = sprintf(FILE_FORMAT, $uniqid, 'zip');

        $xsl->setParameter('', 'locale_filename', '../../locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
        $xsl->setParameter('', 'display_logs', $display_logs);
        $xsl->setParameter('', 'zip_archive', $zipArchiveClass);
        if ($zipArchiveClass) {
            $xsl->setParameter('', 'zip_filename', $zip_filename);
        }
        $html = $xsl->transformToXML($xmldoc);
        $html = htmlspecialchars_decode($html);

        //$html = str_replace("../css/", "css/", $html);
        //die(str_replace("../img/", "img/", $html));

        $html_filename = sprintf(FILE_FORMAT, $uniqid, 'html');

        $hd = fopen(ROADBOOKS_DIR . $html_filename, 'w');
        fwrite($hd, $html);
        fclose($hd);

        if ($zipArchiveClass) {
            $zip = new ZipArchive();
            if ($zip->open(ROADBOOKS_DIR . $zip_filename, ZIPARCHIVE::CREATE) !== true) {
                exit('Unable to open ' . $zip_filename);
            }

            $xsl->setParameter('', 'zip_archive', false); //disable zip link for zip archive
            $zip->addFromString('roadbook/' . $html_filename, $xsl->transformToXML($xmldoc));
            //$zip->addFile('css/blueprint/screen.css');
            //$zip->addFile('css/blueprint/print.css');
            //$zip->addFile('css/blueprint/ie.css');
            //$zip->addFile('css/georoadbook.css');
            //$zip->addFile('css/georoadbook_print.css');
            recurse_zip('img', $zip);
            $zip->close();
        }

        header('Location: roadbook/' . $html_filename);
        exit(0);
    }
}

header('Content-type: text/html; charset=utf-8');

$smarty->assign('max_filesize', ini_get('upload_max_filesize'));
$smarty->display('templates/index.tpl');
