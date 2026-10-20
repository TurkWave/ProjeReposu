<?php
/** @var array $internship */
?>
<div class="card">
    <h2>Stajının Bilgileri</h2>
    <table class="tbl">
        <tr><th>Öğrenci</th><td><?= \App\e(\App\Auth::user()['name'] ?? '') ?></td></tr>
        <tr><th>İşletme</th><td><?= \App\e($internship['company_name']) ?></td></tr>
        <tr><th>Dönem</th><td><?= \App\e($internship['term']) ?></td></tr>
        <tr><th>Başlangıç</th><td><?= \App\e(date('d.m.Y', strtotime($internship['started_at']))) ?></td></tr>
        <tr><th>Bitiş</th><td><?= \App\e(date('d.m.Y', strtotime($internship['ended_at']))) ?></td></tr>
        <tr><th>Durum</th><td><?= \App\e(\App\durum_tr($internship['status'])) ?></td></tr>
        <tr><th>İşletme onayı</th><td><?= (int)$internship['company_confirmed'] ? '<span class="ok">✓ onaylı</span>' : '<span class="warn">bekliyor</span>' ?></td></tr>
    </table>
    <?php if (!empty($internship['teacher_comment'])): ?>
        <div class="field"><label>Öğretmen yorumu</label><div class="box"><?= nl2br(\App\e($internship['teacher_comment'])) ?></div></div>
    <?php endif; ?>
    <a href="/student" class="btn mt">Geri dön</a>
</div>
