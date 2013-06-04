<?php

require dirname(__DIR__) . '/include/config.php';

georoadbook::ajaxRequestOnly();

//Mandatory data
if (!array_key_exists('gpx', $_POST) || empty($_POST['gpx'])) {
    renderAjax(array('success' => false, 'message' => 'GPX file is missing.'));
}
if (!array_key_exists('locale', $_POST) || !in_array($_POST['locale'], array_keys($locales))) {
    renderAjax(array('success' => false, 'message' => 'Locale is missing or invalid.'));
}

//detect shema of geocaching gpx
try {
    $sxe = new SimpleXMLElement($_POST['gpx']);
} catch (Exception $e) {
    renderAjax(array('success' => false, 'message' => 'Not a XML file.'));
}
$schemaLocation = (string) $sxe->attributes('xsi', true)->schemaLocation;
preg_match('!http://www.groundspeak.com/cache/([0-9/]*)!i', $schemaLocation, $matche);

if (!array_key_exists(1, $matche)) {
    renderAjax(array('success' => false, 'message' => 'GPX type is incorrect.'));
}
if ($matche[1] == '1/0') {
    renderAjax(array('success' => false, 'message' => 'GPX version 1/0 is not supported, please use version 1/0/1. <a href="http://www.geocaching.com/account/ManagePreferences.aspx">Check your preferences</a>'));
}

$html_tidy          = isset($_POST['tidy']) && $_POST['tidy'] == "true"                     ? true : false;
$display_toc        = isset($_POST['toc']) && $_POST['toc']   == "true"                     ? true : false;
$display_note       = isset($_POST['note']) && $_POST['note'] == "true"                     ? true : false;
$display_short_desc = isset($_POST['short_desc']) && $_POST['short_desc'] == "true"         ? true : false;
$display_hint       = isset($_POST['hint']) && $_POST['hint'] == "true"                     ? true : false;
$display_logs       = isset($_POST['logs']) && $_POST['logs'] == "true"                     ? true : false;
$hint_encrypted     = isset($_POST['hint_encrypted']) && $_POST['hint_encrypted'] == "true" ? true : false;

$rdbk = new georoadbook();

if(!$rdbk->create($_POST['gpx'])) {
    renderAjax(array('success' => false));
}

$options = array('note' => $display_note,
                 'short_desc' => $display_short_desc,
                 'hint' => $display_hint,
                 'logs' => $display_logs);
$rdbk->convertXmlToHtml($_POST['locale'], $options);

if ($html_tidy) {
    $rdbk->cleanHtml();
}

// Table of content
if ($display_toc) {
    $rdbk->addToc();
}

// Hint
if ($display_hint && $hint_encrypted) {
    $rdbk->encryptHints();
}
// Parse logs
if($display_logs) {
    $rdbk->parseBBcode();
}

$rdbk->saveFile($rdbk->html_file, $rdbk->html);
$rdbk->saveFile($rdbk->json_file);

renderAjax(array('success' => true, 'redirect' => 'roadbook/' . basename($rdbk->html_file)));
