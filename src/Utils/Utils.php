<?php

namespace Utils;

class Utils
{
    /**
     * @param string $data
     *
     * @return string
     */
    public static function renderAjax($data)
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
}
