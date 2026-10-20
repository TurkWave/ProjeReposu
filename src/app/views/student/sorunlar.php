<?php
/** @var array $internship */
/** @var array $issues */
/** @var array $categories */
/** @var string $csrf */
?>
<div class="card">
    <h2>Sorun Bildir</h2>
    <p class="muted">İşletmenizle ilgili sorunları buradan bildirin. Anonim gönderilebilir.</p>

    <form method="post" action="/student/sorunlar" class="form-med">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <div class="field">
            <label for="category">Kategori</label>
            <select id="category" name="category">
                <?php foreach ($categories as $k => $v): ?>
                    <option value="<?= \App\e($k) ?>"><?= \App\e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="description">Açıklama</label>
            <textarea id="description" name="description" rows="4" required placeholder="Ne sorun yaşıyorsunuz?"></textarea>
        </div>
        <label class="checkrow">
            <input type="checkbox" name="anonymous" value="1">
            <span>Anonim gönder (kimliğiniz gizli kalır)</span>
        </label>
        <button type="submit" class="btn primary">Gönder</button>
    </form>

    <h3 class="mt">Bildirdiğim Sorunlar</h3>
    <?php if (!$issues): ?>
        <p class="muted">Henüz bildirim yok.</p>
    <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Tarih</th><th>Kategori</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($issues as $i): ?>
                <tr>
                    <td><?= \App\e(date('d.m.Y', strtotime($i['created_at']))) ?></td>
                    <td><?= \App\e($categories[$i['category']] ?? $i['category']) ?></td>
                    <td><?= \App\e(\App\durum_tr($i['status'])) ?></td>
                    <td><a href="/student/issue/<?= (int)$i['id'] ?>" class="link">detay</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="/student" class="btn mt">Geri dön</a>
</div>

