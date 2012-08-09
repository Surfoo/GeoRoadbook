<?php

require 'include/config.php';
require LIB_DIR . 'smarty/Smarty.class.php';

$smarty = new Smarty();

header('Content-type: text/html; charset=utf-8');

if(!empty($_POST))
{
    $errors = array();
    if(empty($_FILES['gpx']['tmp_name']))
    {
        $errors[] = 'MISSING_FILE';
    }
    if(!isset($_POST['locale']) || !in_array($_POST['locale'], $locales))
    {
        $errors[] = 'INVALID_LOCALE';
    }

    if(!empty($errors))
    {
        $smarty->assign('errors', $errors);

    }
    else {
        if(!empty($_FILES['gpx']) && $_FILES['gpx']['error'] == UPLOAD_ERR_OK)
        {
            $finfo = new finfo(FILEINFO_MIME);
            $type = $finfo->file($_FILES['gpx']['tmp_name']);

            $mime = substr($type, 0, strpos($type, ';'));
            if(!in_array($mime, $mimes))
            {
                @unlink($_FILES['gpx']['tmp_name']);
                exit();
            }
            $uniqid = uniqid();
            $gpx_filename = sprintf(FILE_FORMAT, $uniqid, 'gpx');

            if(!move_uploaded_file($_FILES['gpx']['tmp_name'], UPLOAD_DIR . $gpx_filename))
            {
               exit(); 
            }

        }

        $current_locale  = isset($_POST['locale'])    ? $_POST['locale']  : $locales[0];
        $display_logs    = isset($_POST['logs'])      ? true : false;
        $zipArchiveClass = class_exists('ZipArchive') ? true : false;
        
        $file_mtime = @filemtime(UPLOAD_DIR . $gpx_filename);
        if(!$file_mtime)
        {
            exit("This file doesn't exist.");
        }

        $xsl = new XSLTProcessor();
        $xsldoc = new DOMDocument();
        $xsldoc->load('templates/xslt/roadbook.xslt');
        $xsl->importStyleSheet($xsldoc);

        $xmldoc = new DOMDocument();
        $xmldoc->load(UPLOAD_DIR . $gpx_filename);

        $zip_filename = sprintf(FILE_FORMAT, $uniqid, 'zip');

        $xsl->setParameter('', 'locale_filename', '../../locales/' . sprintf(FILE_FORMAT, $current_locale, 'xml'));
        $xsl->setParameter('', 'display_logs', $display_logs);
        $xsl->setParameter('', 'zip_archive', $zipArchiveClass);
        if($zipArchiveClass)
            $xsl->setParameter('', 'zip_filename', $zip_filename);

        $html = $xsl->transformToXML($xmldoc);

        $html_filename = sprintf(FILE_FORMAT, $uniqid, 'html');
        $hd = fopen(ROADBOOKS_DIR . $html_filename, 'w');
        fwrite($hd, $html);
        fclose($hd);

        if($zipArchiveClass)
        {
            $zip = new ZipArchive();
            if ($zip->open(ROADBOOKS_DIR . $zip_filename, ZIPARCHIVE::CREATE) !== true) {
                exit('Unable to open ' . $zip_filename);
            }

            $xsl->setParameter('', 'zip_archive', false); //disable zip link for zip archive
            $zip->addFromString('roadbook/' . $html_filename, $xsl->transformToXML($xmldoc));
            $zip->addFile('css/blueprint/screen.css');
            $zip->addFile('css/blueprint/print.css');
            $zip->addFile('css/blueprint/ie.css');
            $zip->addFile('css/georoadbook.css');
            $zip->addFile('css/georoadbook_print.css');
            recurse_zip('img', $zip);
            $zip->close();
        }

        header('Location: roadbook/' . $html_filename);
        exit();
    }
}

$smarty->assign('max_filesize', ini_get('upload_max_filesize'));
$smarty->display('templates/index.tpl');