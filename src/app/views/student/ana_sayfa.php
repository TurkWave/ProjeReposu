<?php
/** @var array $internship */
/** @var array|null $todayRecord */
/** @var array|null $todayJournal */
/** @var string $today */
/** @var string $csrf */
/** @var string $error */
/** @var string $success */
?>
<div class="topgrid">
    <div class="card">
        <h1>Merhaba, <?= \App\e(\App\Auth::user()['name'] ?? 'Öğrenci') ?></h1>
        <p class="muted">Staj: <strong><?= \App\e($internship['company_name']) ?></strong></p>

        <?php if ($todayRecord OR $todayJournal): ?>
            <p class="ok">Bugünkü kaydınız hazır.</p>
        <?php else: ?>
            <p class="warn">Bugün kaydınız yok. Hemen gönderin!</p>
        <?php endif; ?>

        <form method="post" action="/student/daily" enctype="multipart/form-data" class="quick-form">
            <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
            <label class="row checkrow">
                <input type="checkbox" name="present" value="1" <?= checked((bool)($todayRecord['present'] ?? true)) ?>>
                <span>Bugün stajdayım</span>
            </label>
            <input type="text" name="location" placeholder="Konum (isteğe bağlı)" value="<?= \App\e($todayRecord['location'] ?? '') ?>">
            <textarea name="note" rows="2" placeholder="Defter notu (isteğe bağlı)"></textarea>
            <input type="file" name="photo" accept="image/*">
            <button type="submit" class="btn primary">Kaydet (1 dk)</button>
        </form>
    </div>

    <div class="grid2">
        <a href="/student/daily" class="tile">📋 Detay Yoklama</a>
        <a href="/student/daily/history" class="tile">📔 Defterim</a>
        <a href="/student/sorunlar" class="tile">🚨 Sorun Bildir</a>
        <a href="/student/kontrol_listesi" class="tile">✅ Kontrol</a>
        <a href="/student/sss" class="tile">❓ SSS</a>
        <a href="/student/staj_bilgileri" class="tile">🏢 Stajım</a>
    </div>
</div>
