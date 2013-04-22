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

    if ($_FILES['gpx']['error'] == UPLOAD_ERR_OK) {

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
        $display_logs       = isset($_POST['logs'])      ? true : false;
        // $zipArchiveClass    = ZIP_ARCHIVE && class_exists('ZipArchive') ? true : false;

        $uniqid = substr(md5(uniqid(rand(), true)), 0, 12);
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

        // $zip_filename = sprintf(FILE_FORMAT, $uniqid, 'zip');

        $xsl->setParameter('', 'locale_filename', '../../locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
        $xsl->setParameter('', 'icon_cache_dir', ICON_CACHE_DIR);
        $xsl->setParameter('', 'display_note', $display_note);
        $xsl->setParameter('', 'display_short_desc', $display_short_desc);
        $xsl->setParameter('', 'display_logs', $display_logs);
        // $xsl->setParameter('', 'zip_archive', $zipArchiveClass);
        // if ($zipArchiveClass) {
        //     $xsl->setParameter('', 'zip_filename', $zip_filename);
        // }
        $html = $xsl->transformToXML($xmldoc);

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

        // if ($zipArchiveClass) {
        //     $zip = new ZipArchive();
        //     if ($zip->open(ROADBOOKS_DIR . $zip_filename, ZIPARCHIVE::CREATE) !== true) {
        //         exit('Unable to open ' . $zip_filename);
        //     }

        //     $xsl->setParameter('', 'zip_archive', false); //disable zip link for zip archive
        //     $zip->addFromString('roadbook/' . $filename_html, $xsl->transformToDoc($xmldoc));
        //     $zip->addFile('css/mycontent.css');
        //     recurse_zip('img', $zip);
        //     $zip->close();
        // }

        header('Location: roadbook/' . $filename_html);
        exit(0);
    }
}

header('Content-type: text/html; charset=utf-8');

$smarty->assign('max_filesize', ini_get('upload_max_filesize'));
$smarty->display('templates/index.tpl');
