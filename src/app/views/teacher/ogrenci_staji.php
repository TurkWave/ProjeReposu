<?php
/** @var array $internship */
/** @var array $entries */
/** @var string $confirmToken */
/** @var string $csrf */
/** @var string $error */
/** @var string $success */
?>
<div class="card">
    <h2><?= \App\e($internship['student_name']) ?> - Stajı</h2>
    <table class="tbl">
        <tr><th>Öğrenci E-posta</th><td><?= \App\e($internship['student_email']) ?></td></tr>
        <tr><th>İşletme</th><td><?= \App\e($internship['company_name']) ?></td></tr>
        <tr><th>Öğretmen</th><td><?= \App\e($internship['teacher_name']) ?></td></tr>
        <tr><th>Dönem</th><td><?= \App\e($internship['term']) ?> (<?= \App\e(date('d.m.Y', strtotime($internship['started_at']))) ?> – <?= \App\e(date('d.m.Y', strtotime($internship['ended_at']))) ?>)</td></tr>
        <tr><th>Durum</th><td><?= \App\e(\App\durum_tr($internship['status'])) ?> | <?= (int)$internship['company_confirmed'] ? '<span class="ok">işletme onayı var</span>' : '<span class="warn">işletme onayı yok</span>' ?></td></tr>
    </table>

    <form method="post" action="/teacher/comment" class="form-med">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <input type="hidden" name="internship_id" value="<?= (int)$internship['id'] ?>">
        <div class="field">
            <label for="teacher_comment">Yorum / Not</label>
            <textarea id="teacher_comment" name="teacher_comment" rows="3" placeholder="Öğrenci hakkında yorum..."><?= \App\e($internship['teacher_comment'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn primary">Yorumu Kaydet</button>
    </form>

    <?php if ($confirmToken): ?>
        <div class="field">
            <label>İşletme Onay Linki</label>
            <input type="text" readonly onclick="this.select()" value="<?= \App\e(\App\Config::get('app.base_url')) ?>/company/<?= \App\e($confirmToken) ?>">
        </div>
    <?php else: ?>
        <p class="warn">Onay linki oluşturulmamış.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Günlük Kayıtlar</h2>
    <?php if (!$entries): ?>
        <p class="muted">Kayıt yok.</p>
    <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Tarih</th><th>Yoklama</th><th>Defter Notu</th></tr></thead>
            <tbody>
            <?php foreach ($entries as $date => $e): ?>
                <tr>
                    <td><?= \App\e(date('d.m.Y', strtotime($date))) ?></td>
                    <td><?= !empty($e['record']['present']) ? '✓ var' : '✗ yok' ?></td>
                    <td><?= \App\e(mb_strimwidth($e['journal']['entry'] ?? '', 0, 100, '…', 'UTF-8')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="/teacher" class="btn mt">Panele dön</a>
</div>
