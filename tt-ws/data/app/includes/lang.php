<?php

function loadLang() {

    $supported = ['en', 'de', 'fr', 'es'];

    if (isset($_GET['lang'])) {
        $urlLang = strtolower(trim($_GET['lang']));
        if (in_array($urlLang, $supported)) {
            return require __DIR__ . "/../lang/$urlLang.php";
        }
    }

    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
    if (!in_array($browserLang, $supported)) {
        $browserLang = 'en';
    }

    return require __DIR__ . "/../lang/$browserLang.php";
}
