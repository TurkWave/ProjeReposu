<?php
/** @var array $conf */
/** @var array $internship */
?>
<div class="company-card">
    <h2>Onay Tamamlandı</h2>
    <p class="ok">Bu staj için onayınız zaten kaydedilmiş.</p>
    <div class="info">
        <p><strong>Öğrenci:</strong> <?= \App\e($internship['student_name']) ?></p>
        <p><strong>İşletme:</strong> <?= \App\e($internship['company_name']) ?></p>
        <?php if (!empty($conf['feedback'])): ?>
            <p><strong>Geri bildirim:</strong> <?= nl2br(\App\e($conf['feedback'])) ?></p>
        <?php endif; ?>
    </div>
</div>
