<?php

require dirname(__DIR__) . '/include/config.php';

georoadbook::ajaxRequestOnly();

if (!array_key_exists('id', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$rdbk = new georoadbook($_POST['id']);

$rdbk->delete();

renderAjax(array('success' => true));
