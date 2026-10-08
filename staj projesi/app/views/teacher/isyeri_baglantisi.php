<?php
/** @var array $internship */
/** @var array $confirm */
/** @var string $base_url */
?>
<div class="card">
    <h2>İşletme Onay Linki</h2>
    <p class="muted">Bu linki ilgili işletmeye gönderin. Link <?= (int)$confirm['confirmed'] ? '<span class="ok">kullanıldı</span>' : '<span class="warn">bekliyor</span>' ?>.</p>
    <table class="tbl">
        <tr><th>Öğrenci</th><td><?= \App\e($internship['student_name']) ?> (<?= \App\e($internship['student_email']) ?>)</td></tr>
        <tr><th>İşletme</th><td><?= \App\e($internship['company_name']) ?></td></tr>
        <tr><th>Link</th><td><input type="text" readonly onclick="this.select()" value="<?= \App\e($base_url) ?>/company/<?= \App\e($confirm['token']) ?>"></td></tr>
        <tr><th>Son Kullanım</th><td><?= \App\e(date('d.m.Y', strtotime($confirm['expires_at']))) ?></td></tr>
    </table>
    <p class="muted small">Link süresiz değil, <strong>tek kullanımlık</strong> ve tek amaçlıdır.
       İşletme hesabı açmaz; yalnızca bu linkle onaylar.</p>
    <a href="/teacher" class="btn mt">Panele dön</a>
</div>
