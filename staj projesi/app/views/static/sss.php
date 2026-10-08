<?php
/** @var array $items */
?>
<div class="card">
    <h2>Sık Sorulan Sorular</h2>
    <?php if (!$items): ?>
        <p class="muted">Soru bulunmuyor.</p>
    <?php else: ?>
        <div class="faq">
        <?php foreach ($items as $item): ?>
            <details>
                <summary><?= \App\e($item['question']) ?></summary>
                <p><?= nl2br(\App\e($item['answer'])) ?></p>
            </details>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a href="/" class="btn mt">Ana sayfa</a>
</div>
