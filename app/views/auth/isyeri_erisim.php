<div class="card login-card">
    <a class="back-link" href="/login">← Giriş türünü değiştir</a>
    <h1>İşyeri değerlendirme girişi</h1>
    <p class="muted">İşyeri hesabı veya şifresi gerekmez. Öğretmenin gönderdiği değerlendirme bağlantısını buraya yapıştırın.</p>
    <?php if (!empty($err)): ?>
        <div class="flash flash-error"><?= \App\e($err) ?></div>
    <?php endif; ?>
    <form method="post" action="/giris/isletme" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <div class="field">
            <label for="company_link">Değerlendirme bağlantısı veya token</label>
            <textarea id="company_link" name="company_link" rows="3" required placeholder="Öğretmenin paylaştığı bağlantıyı yapıştırın"></textarea>
        </div>
        <button type="submit" class="btn primary">Bağlantıyı aç</button>
    </form>
</div>
