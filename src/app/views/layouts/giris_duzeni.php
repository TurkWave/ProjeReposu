<?php
/** @var string $content */
/** @var string $title */
$title = $title ?? 'Staj Takip';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#142b4a">
    <title><?= \App\e($title) ?> | Staj Takip</title>
    <meta name="description" content="Staj takip, yoklama, defter ve sorun bildirme uygulaması.">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/assets/icons/icon-192.png?v=2" sizes="192x192">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png?v=2" sizes="192x192">
    <link rel="stylesheet" href="/assets/css/gorunum.css?v=2">
</head>
<body class="authbg">
<div class="card-wrap">
    <?= $content ?>
</div>
<script src="/assets/js/arayuz_etkilesimi.js?v=2"></script>
</body>
</html>
