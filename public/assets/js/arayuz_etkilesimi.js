/* ============================================================
   Staj Takip - PWA + UX JS (vanilla)
   ============================================================ */
(function () {
    'use strict';

    // --- CSRF: data-csrf="post" linklerini POST formuna çevir ---
    function installCsrfLinks() {
        document.querySelectorAll('a[data-csrf="post"]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                var href = el.getAttribute('href');
                if (!confirm('Çıkış yapmak istediğinizden emin misiniz?')) { e.preventDefault(); return; }
                // oturum bilgisi yok say; sunucu CSRF kontrolüne karşı token göndermeliyiz.
                // Basit: bir form oluşturup gönder. Token global olarak yok, o yüzden
                // oturumu kapatan link CSRF'siz bırakılır.
                // (Çıkış için CSRF zorunluluğunu kaldırmadık; burada GET ile de çık.
                //  Ancak güvenlik için token gönderelim: meta'dan al.)
                var token = document.querySelector('meta[name="csrf-token"]') ?
                    document.querySelector('meta[name="csrf-token"]').content : '';
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = href;
                if (token) {
                    var i = document.createElement('input'); i.type = 'hidden'; i.name = 'csrf_token'; i.value = token;
                    form.appendChild(i);
                }
                document.body.appendChild(form);
                form.submit();
                e.preventDefault();
            });
        });
    }

    // --- Service worker (PWA offline/kayıt) ---
    function registerSW() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/cevrimdisi_hizmeti.js').then(function (reg) {
                console.log('SW kaydoldu:', reg.scope);
            }).catch(function (err) {
                console.log('SW kaydı başarısız:', err);
            });
        }
    }

    // Kontrol listesi: seçimleri bu cihazda hatırla.
    function restoreChecklist() {
        document.querySelectorAll('[data-checklist-id]').forEach(function (input) {
            var key = 'staj-checklist-' + input.dataset.checklistId;
            try { input.checked = localStorage.getItem(key) === '1'; } catch (_) { /* depolama kapalı olabilir */ }
            input.closest('li').classList.toggle('done', input.checked);
            input.addEventListener('change', function () {
                try { localStorage.setItem(key, input.checked ? '1' : '0'); } catch (_) { /* depolama kapalı olabilir */ }
                input.closest('li').classList.toggle('done', input.checked);
            });
        });
    }

    // --- PWA install isabeti ---
    var deferredPrompt;
    var installBtn = document.getElementById('installPromptBtn');
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        if (installBtn) { installBtn.classList.add('show'); }
    });
    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function (choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA kuruldu');
                    }
                    deferredPrompt = null;
                    installBtn.classList.remove('show');
                });
            }
        });
    }

    // --- Başlangıç ---
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            installCsrfLinks(); registerSW(); restoreChecklist();
        });
    } else {
        installCsrfLinks(); registerSW(); restoreChecklist();
    }
})();
