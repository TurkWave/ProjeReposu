<?php
// ============================================================
// Built-in PHP dev server router.
// `php -S 127.0.0.1:8765 -t public public/istek_yonlendirici.php`
// Statik dosyalar doğrudan sunulur, diğerleri index.php'ye.
// ============================================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requested = __DIR__ . $uri;

// /favicon.ico gibi statik dosyalar
if ($uri !== '/' && is_file($requested)) {
    return false;
}

// geri kalan her şey front controller'a
require_once __DIR__ . '/index.php';
