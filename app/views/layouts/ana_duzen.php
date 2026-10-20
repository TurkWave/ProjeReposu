<?php
/** @var string $content */
/** @var string $title */
$title = $title ?? 'Staj Takip';
$user = \App\Auth::user();
$isTeacher = \App\Auth::isTeacher();
$isStudent = \App\Auth::isStudent();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#142b4a">
    <title><?= \App\e($title) ?> | Staj Takip</title>
    <meta name="description" content="Staj takip, yoklama, defter ve sorun bildirme uygulaması.">
    <?php if ($user): ?>
        <meta name="csrf-token" content="<?= \App\e(\App\csrf_token()) ?>">
    <?php endif; ?>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/assets/icons/icon-192.png?v=2" sizes="192x192">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png?v=2" sizes="192x192">
    <link rel="stylesheet" href="/assets/css/gorunum.css?v=2">
</head>
<body>
<header class="top">
    <div class="brand"><a href="<?= $isTeacher ? '/teacher' : ($isStudent ? '/student' : '/') ?>">Staj Takip</a></div>
    <?php if ($user): ?>
    <nav class="nav">
        <?php if ($isStudent): ?>
            <a href="/student" class="navlink">Anasayfa</a>
            <a href="/student/daily" class="navlink">Yoklama</a>
            <a href="/student/sorunlar" class="navlink">Sorun Bildir</a>
            <a href="/student/kontrol_listesi" class="navlink">Kontrol</a>
            <a href="/student/sss" class="navlink">SSS</a>
            <a href="/student/staj_bilgileri" class="navlink">Stajım</a>
        <?php elseif ($isTeacher): ?>
            <a href="/teacher" class="navlink">Öğrenci paneli</a>
            <a href="/teacher/sorunlar" class="navlink">Sorunlar</a>
            <a href="/checklist" class="navlink">Kontrol listesi</a>
            <a href="/faq" class="navlink">SSS</a>
        <?php endif; ?>
        <a href="/logout" class="navlink" data-csrf="post">Çıkış</a>
    </nav>
    <?php else: ?>
    <nav class="nav">
        <a href="/login" class="navlink">Giriş</a>
        <a href="/register" class="navlink">Kayıt</a>
    </nav>
    <?php endif; ?>
</header>

<main class="main">
    <?php
    $err = \App\flash('error');
    $ok  = \App\flash('success');
    if ($err): ?>
        <div class="flash flash-error"><?= \App\e($err) ?></div>
    <?php endif; ?>
    <?php if ($ok): ?>
        <div class="flash flash-ok"><?= \App\e($ok) ?></div>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="foot">
    <small>© <?= date('Y') ?> Staj Takip · Staj süreciniz için sade bir takip alanı.</small>
</footer>

<button type="button" id="installPromptBtn">Ana ekrana ekle</button>

<script src="/assets/js/arayuz_etkilesimi.js?v=2"></script>
</body>
</html>
