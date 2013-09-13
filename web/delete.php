<?php

require dirname(__DIR__) . '/include/config.php';

Georoadbook::ajaxRequestOnly();

if (!array_key_exists('id', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$rdbk = new Georoadbook($_POST['id']);

$rdbk->delete();

renderAjax(array('success' => true));
