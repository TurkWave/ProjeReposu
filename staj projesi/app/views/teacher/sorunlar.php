<?php
/** @var array $issues */
/** @var string $filter */
/** @var array $categories */
?>
<div class="card">
    <h2>Sorun Bildirimleri</h2>
    <p class="muted">
        <a href="/teacher/sorunlar" class="<?= $filter === 'all' ? 'link-active' : '' ?>">Tümü</a> |
        <a href="/teacher/sorunlar?status=open" class="<?= $filter === 'open' ? 'link-active' : '' ?>">Açık</a>
    </p>

    <?php if (!$issues): ?>
        <p class="ok">Sorun bildirimi bulunmuyor.</p>
    <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Tarih</th><th>Kimlik</th><th>Kategori</th><th>Açıklama</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($issues as $i): ?>
                <tr>
                    <td><?= \App\e(date('d.m.Y', strtotime($i['created_at']))) ?></td>
                    <td><?= (int)$i['anonymous'] ? '<em class="muted">gizli</em>' : \App\e($i['student_name']) ?></td>
                    <td><?= \App\e($categories[$i['category']] ?? $i['category']) ?></td>
                    <td><?= \App\e(mb_strimwidth($i['description'], 0, 140, '…', 'UTF-8')) ?></td>
                    <td>
                        <form method="post" action="/teacher/issue/<?= (int)$i['id'] ?>/status" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= \App\e(csrf_token()) ?>">
                            <select name="status">
                                <option value="new" <?= checked($i['status'] === 'new') ?>>Yeni</option>
                                <option value="in_progress" <?= checked($i['status'] === 'in_progress') ?>>İnceleniyor</option>
                                <option value="resolved" <?= checked($i['status'] === 'resolved') ?>>Çözüldü</option>
                            </select>
                            <button type="submit" class="btn small">Güncelle</button>
                        </form>
                    </td>
                    <td><a href="/teacher/ogrenci_staji/<?= (int)$i['internship_id'] ?>" class="link small">staj</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="/teacher" class="btn mt">Panora dön</a>
</div>
