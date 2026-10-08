<?php
/** @var array $conf */
/** @var array $internship */
/** @var array $questions */
/** @var string $csrf */
/** @var string $action_url */
?>
<div class="company-card">
    <h2>Staj Onayı</h2>
    <p class="muted">Öğrencinin staj kaydını onaylayın ve kısa değerlendirmenizi yapın.</p>

    <div class="info">
        <p><strong>Öğrenci:</strong> <?= \App\e($internship['student_name']) ?> (<?= \App\e($internship['student_email']) ?>)</p>
        <p><strong>İşletme:</strong> <?= \App\e($internship['company_name']) ?></p>
        <p><strong>Dönem:</strong> <?= \App\e($internship['term']) ?> — <?= \App\e(date('d.m.Y', strtotime($internship['started_at']))) ?> / <?= \App\e(date('d.m.Y', strtotime($internship['ended_at']))) ?></p>
    </div>

    <form method="post" action="<?= \App\e($action_url) ?>" class="form-med">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <fieldset>
            <legend>Değerlendirme (1 zayıf – 5 çok iyi)</legend>
            <?php foreach ($questions as $key => $label): ?>
                <div class="row">
                    <label><?= \App\e($label) ?></label>
                    <div class="stars">
                        <?php for ($n = 1; $n <= 5; $n++): ?>
                            <label><input type="radio" name="<?= \App\e($key) ?>" value="<?= $n ?>" required> <?= $n ?></label>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <div class="field">
            <label for="feedback">Ek geri bildirim (isteğe bağlı)</label>
            <textarea id="feedback" name="feedback" rows="3" maxlength="1000" placeholder="Yorumlarınız..."></textarea>
        </div>
        <button type="submit" class="btn primary">Onayla ve Gönder</button>
    </form>
</div>
