<?php

function recurse_zip($src, &$zip)
{
    $dir = opendir($src);
    while (false !== ($file=readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_zip($src . '/' . $file, $zip);
            } else {
                $zip->addFile($src . '/' . $file, substr($src . '/' . $file, strlen(dirname(__DIR__).'/web/')));
            }
        }
    }
    closedir($dir);
}

function renderAjax($data)
{
    if (!is_array($data)) {
        exit();
    }
    $content = json_encode($data);

    if (!headers_sent()) {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo $content;
    exit(0);
}
