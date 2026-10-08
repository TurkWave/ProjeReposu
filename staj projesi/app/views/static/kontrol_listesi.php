<?php
/** @var array $items */
?>
<div class="card">
    <h2>Staj Kontrol Listesi</h2>
    <p class="muted">Staj sürecinizde yapmanız gerekenler.</p>
    <?php if (!$items): ?>
        <p class="muted">Kontrol listesi bulunmuyor.</p>
    <?php else: ?>
        <ol class="checklist">
        <?php foreach ($items as $item): ?>
            <li>
                <label class="checkrow">
                    <input type="checkbox" data-checklist-id="<?= (int)$item['id'] ?>">
                    <span>
                        <strong><?= \App\e($item['title']) ?></strong>
                        <small><?= \App\e($item['description']) ?></small>
                    </span>
                </label>
            </li>
        <?php endforeach; ?>
        </ol>
    <?php endif; ?>
    <a href="/" class="btn mt">Ana sayfa</a>
</div>
