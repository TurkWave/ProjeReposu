<?php
/** @var string $err */
/** @var string $ok */
/** @var string $csrf */
?>
<div class="card">
    <h1>Öğrenci Kaydı</h1>
    <?php if (!empty($err)): ?>
        <div class="flash flash-error"><?= \App\e($err) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
        <div class="flash flash-ok"><?= \App\e($ok) ?></div>
    <?php endif; ?>
    <form method="post" action="/register" autocomplete="off" id="registerForm">
        <input type="hidden" name="csrf_token" value="<?= \App\e($csrf) ?>">
        <div class="field">
            <label for="name">Ad Soyad</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="field">
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" required autocomplete="username">
        </div>
        <div class="field">
            <label for="password">Şifre (en az 6)</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>
        <div class="field">
            <label><input type="checkbox" name="is_minor" id="isMinor"> 18 yaş altıyım (veli onayı gerekir)</label>
        </div>
        <div class="field" id="parentField" style="display:none;">
            <label><input type="checkbox" name="consent_parent" id="parentConsent"> Veli onayı alındı.</label>
        </div>
        <div class="field">
            <label>
                <input type="checkbox" name="consent_kvkk" id="kvkk" required>
                <span><a href="/kvkk" target="_blank">KVKK Aydınlatma Metni</a>'nı okudum ve katılıyorum.</span>
            </label>
        </div>
        <button type="submit" class="btn primary">Kayıt Ol</button>
    </form>
    <p class="muted">Zaten hesabınız varsa <a href="/login">giriş yapın</a>.</p>
</div>
<script>
const isMinor = document.getElementById('isMinor');
const parentField = document.getElementById('parentField');
const parentConsent = document.getElementById('parentConsent');
function updateParentConsent() {
    parentField.style.display = isMinor.checked ? 'block' : 'none';
    parentConsent.required = isMinor.checked;
    if (!isMinor.checked) parentConsent.checked = false;
}
updateParentConsent();
isMinor.addEventListener('change', () => {
    updateParentConsent();
});
</script>
