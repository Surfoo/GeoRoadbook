<?php

define('ROOT',          dirname(__DIR__));
define('UPLOAD_DIR',    ROOT . '/upload/');
define('ROADBOOKS_DIR', ROOT . '/roadbook/');
define('LIB_DIR',       ROOT . '/lib/');

define('FILE_FORMAT',   '%s.%s');

$locales = array('en', 'fr');
$mimes = array('application/xml');


function recurse_zip($src, &$zip) 
{
    $dir = opendir($src);
    while(false !== ($file=readdir($dir)))
    {
        if(($file != '.') && ($file != '..')) 
        {
            if(is_dir($src . '/' . $file)) 
            {
                recurse_zip($src . '/' . $file, $zip);
            }
            else 
            {
                $zip->addFile($src . '/' . $file);
            }
        }
    }
    closedir($dir);
}