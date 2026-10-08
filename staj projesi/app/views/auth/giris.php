<?php
/** @var string $err */
/** @var string $csrf */
?>
<div class="card login-card">
    <a class="back-link" href="/login">← Giriş türünü değiştir</a>
    <h1><?= \App\e($roleLabel) ?> Girişi</h1>
    <?php if (!empty($err)): ?>
        <div class="flash flash-error"><?= \App\e($err) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= \App\e($formAction) ?>" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <div class="field">
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" required autofocus autocomplete="username">
        </div>
        <div class="field">
            <label for="password">Şifre</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn primary">Giriş Yap</button>
    </form>
    <?php if ($role === 'ogrenci'): ?>
        <p class="muted">Öğrenci hesabınız yok mu? <a href="/register">Öğrenci kaydı oluşturun</a></p>
    <?php endif; ?>
</div>
