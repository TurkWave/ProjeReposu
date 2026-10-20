<?php
/** @var array $internship */
/** @var array|null $record */
/** @var array|null $journal */
/** @var string $today */
/** @var string $csrf */
/** @var string $error */
?>
<div class="card">
    <h2>Günlük Yoklama ve Defter</h2>
    <p class="muted"><?= \App\e(\App\gun_adi($today)) ?>, <?= \App\e(\App\tarih_tr($today)) ?></p>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= \App\e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/student/daily" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <label class="checkrow">
            <input type="checkbox" name="present" value="1" <?= checked($record['present'] ?? true) ?>>
            <span>Bugün stajdayım</span>
        </label>
        <div class="field"><input type="text" name="location" placeholder="Konum (isteğe bağlı)" value="<?= \App\e($record['location'] ?? $journal['location'] ?? '') ?>"></div>
        <div class="field">
            <label>Defter notu (isteğe bağlı)</label>
            <textarea name="note" rows="3" placeholder="Bugün neyinizi öğrendiniz?"><?= \App\e($journal['entry'] ?? '') ?></textarea>
        </div>
        <div class="field">
            <label>Fotoğraf (isteğe bağlı, max 2 MB)</label>
            <input type="file" name="photo" accept="image/*">
            <?php if (!empty($journal['photo_url'])): ?>
                <p class="muted">Yüklenen: <a href="<?= \App\e($journal['photo_url']) ?>" target="_blank">görüntü</a></p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn primary">Kaydet</button>
    </form>
</div>
