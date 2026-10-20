/* ============================================================
   Staj Takip - Service Worker (PWA)
   - Statik varlıklar cache-first
   - Sayfa gezinmeleri network-first, offline'da cache/login fallback
   ============================================================ */
const CACHE = 'staj-takip-v2';
const ASSETS = [
    '/assets/css/gorunum.css',
    '/assets/js/arayuz_etkilesimi.js',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/manifest.json',
    // offline'da görüntülenebilecek "kök" sayfalar
    '/login',
    '/register',
    '/faq',
    '/checklist',
    '/kvkk',
];

self.addEventListener('install', function (e) {
    e.waitUntil(caches.open(CACHE).then(function (cache) {
        return cache.addAll(ASSETS);
    }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (e) {
    e.waitUntil(caches.keys().then(function (keys) {
        return Promise.all(keys.map(function (key) {
            if (key !== CACHE) return caches.delete(key);
        }));
    }).then(function () { return self.clients.claim(); }));
});

self.addEventListener('fetch', function (e) {
    const url = new URL(e.request.url);
    const isNav = e.request.mode === 'navigate' || e.request.headers.get('accept') && e.request.headers.get('accept').includes('text/html');

    // Statik varlıklar: cache-first
    const isAsset = url.pathname.startsWith('/assets/') || url.pathname === '/manifest.json';
    if (isAsset) {
        e.respondWith(caches.match(url.pathname).then(function (resp) {
            return resp || fetch(e.request).then(function (netResp) {
                if (netResp && netResp.status === 200) {
                    const clone = netResp.clone();
                    caches.open(CACHE).then(c => c.put(url.pathname, clone));
                }
                return netResp;
            }).catch(function () { return caches.match(url.pathname); });
        }));
        return;
    }

    // Gezinme: network-first, offline fallback
    if (isNav) {
        e.respondWith(fetch(e.request).catch(function () {
            return caches.match('/login') || new Response('Offline. Lütfen bağlantınızı kontrol edin.', {
                headers: { 'Content-Type': 'text/html; charset=utf-8' }
            });
        }));
        return;
    }

    // diğerleri: network-first
    e.respondWith(fetch(e.request).catch(function () { return caches.match(e.request); }));
});
