<?php
/** @var array $internships */
/** @var array $missingToday */
/** @var array $pendingCompany */
/** @var array $openIssues */
/** @var string $today */
/** @var array $count */
/** @var string $csrf */
?>
<div class="topgrid">
    <div class="cards">
        <div class="stat green"><div class="num"><?= $count['total'] ?></div><div class="lbl">Toplam Öğrenci</div></div>
        <div class="stat red"><div class="num"><?= $count['missing'] ?></div><div class="lbl">Bugün Giriş Yapmayan</div></div>
        <div class="stat amber"><div class="num"><?= $count['pending'] ?></div><div class="lbl">Onay Bekleyen</div></div>
        <div class="stat blue"><div class="num"><?= $count['issues'] ?></div><div class="lbl">Açık Sorun</div></div>
    </div>

    <div class="card">
        <h2>Yoklama girişi beklenenler</h2>
        <?php if (!$missingToday): ?>
            <p class="ok">Hepiniz bugün yoklamaya girdiniz. 🎉</p>
        <?php else: ?>
            <ul class="list">
            <?php foreach ($missingToday as $r): ?>
                <li><a href="/teacher/ogrenci_staji/<?= (int)$r['id'] ?>"><?= \App\e($r['student_name']) ?></a>
                    <span class="muted small"> — <?= \App\e($r['company_name']) ?></span></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>İşletme Onayını Bekleyen Stajlar</h2>
        <?php if (!$pendingCompany): ?>
            <p class="ok">Tüm stajlar onaylı.</p>
        <?php else: ?>
            <ul class="list">
            <?php foreach ($pendingCompany as $r): ?>
                <li>
                    <a href="/teacher/ogrenci_staji/<?= (int)$r['id'] ?>"><?= \App\e($r['student_name']) ?></a>
                    <span class="muted small">— <?= \App\e($r['company_name']) ?></span>
                    <a href="/teacher/company-link/<?= (int)$r['id'] ?>" class="link small">linki göster</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Açık Sorun Bildirimleri</h2>
        <?php if (!$openIssues): ?>
            <p class="ok">Açık sorun bildirimi yok.</p>
        <?php else: ?>
            <table class="tbl">
                <thead><tr><th>Tarih</th><th><?= 'Kimlik' ?></th><th>Kategori</th><th>Açıklama</th><th>Durum</th></tr></thead>
                <tbody>
                <?php foreach ($openIssues as $i): ?>
                    <tr>
                        <td><?= \App\e(date('d.m.Y', strtotime($i['created_at']))) ?></td>
                        <td><?= (int)$i['anonymous'] ? '<em class="muted">gizli</em>' : \App\e($i['student_name']) ?></td>
                        <td><?= \App\e($i['category']) ?></td>
                        <td><?= \App\e(mb_strimwidth($i['description'], 0, 120, '…', 'UTF-8')) ?></td>
                        <td><?= \App\e(\App\durum_tr($i['status'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="/teacher/sorunlar" class="link">Tüm sorunları yönet...</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Tüm Öğrencileriniz</h2>
        <table class="tbl">
            <thead><tr><th>Öğrenci</th><th>İşletme</th><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach ($internships as $r): ?>
                <tr>
                    <td><?= \App\e($r['student_name']) ?> <small><?= \App\e($r['student_email']) ?></small></td>
                    <td><?= \App\e($r['company_name']) ?></td>
                    <td><?= \App\e(\App\durum_tr($r['status'])) ?> <?= (int)$r['company_confirmed'] ? '<span class="ok">· Onaylı</span>' : '<span class="warn">· Onay bekliyor</span>' ?></td>
                    <td><a href="/teacher/ogrenci_staji/<?= (int)$r['id'] ?>" class="link">detay</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
