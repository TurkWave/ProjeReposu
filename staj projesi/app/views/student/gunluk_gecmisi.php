<?php
/** @var array $internship */
/** @var array $entries (date => [record=>..., journal=>...]) */
?>
<div class="card">
    <h2>Günlük Kayıtlarım</h2>
    <?php if (!$entries): ?>
        <p class="muted">Kayıt bulunmuyor.</p>
    <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Tarih</th><th>Yoklama</th><th>Defter Notu</th></tr></thead>
            <tbody>
            <?php foreach ($entries as $date => $e): ?>
                <tr>
                    <td><?= \App\e(\App\gun_adi($date)) ?> · <?= \App\e(\App\tarih_tr($date)) ?></td>
                    <td><?= !empty($e['record']['present']) ? '<span class="ok">✓ var</span>' : '<span class="warn">✗ yok</span>' ?></td>
                    <td><?= \App\e(mb_strimwidth($e['journal']['entry'] ?? '(not yok)', 0, 80, '…', 'UTF-8')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="/student" class="btn mt">Geri dön</a>
</div>
