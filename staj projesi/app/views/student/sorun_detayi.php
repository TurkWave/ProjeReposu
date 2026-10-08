<?php
/** @var array $internship */
/** @var array $issue */
/** @var array $categories */
/** @var string $csrf */
?>
<div class="card">
    <h2>Sorun Bildirisi</h2>
    <table class="tbl">
        <tr><th>Kategori</th><td><?= \App\e($categories[$issue['category']] ?? $issue['category']) ?></td></tr>
        <tr><th>Durum</th><td><?= \App\e(\App\durum_tr($issue['status'])) ?></td></tr>
        <tr><th>Gizlilik</th><td><?= (int)$issue['anonymous'] ? 'Anonim (kimliğiniz gizli)' : 'Kimliğiniz öğretmeninizle paylaşılır' ?></td></tr>
        <tr><th>Tarih</th><td><?= \App\e(date('d.m.Y H:i', strtotime($issue['created_at']))) ?></td></tr>
    </table>
    <div class="field"><label>Açıklama</label><div class="box"><?= nl2br(\App\e($issue['description'])) ?></div></div>

    <?php if (!(int)$issue['anonymous']): ?>
        <p class="muted">Sorununuz öğrenci kimliğinizle iliştirilmiştir.</p>
    <?php else: ?>
        <p class="muted">Bu rapor anonim gönderilmiştir; öğretmeniniz kimliğinizi görmeyecek.</p>
    <?php endif; ?>

    <form method="post" action="/student/issue/<?= (int)$issue['id'] ?>" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <input type="hidden" name="_action" value="delete">
        <button type="submit" class="btn warn">Sil</button>
    </form>
    <a href="/student/sorunlar" class="btn mt">Geri dön</a>
</div>
