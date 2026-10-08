<?php
// ============================================================
// Staj Takip ve Destek Uygulaması - Konfigürasyon
// ============================================================
declare(strict_types=1);

return [
    // --- Veritabanı (XAMPP / MariaDB) ---
    'db' => [
        'host'     => '127.0.0.1',
        'name'     => 'staj_app',
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'collate'  => 'utf8mb4_unicode_ci',
    ],

    // --- Uygulama ---
    'app' => [
        'base_url'  => '',   // İstek adresinden otomatik belirlenir (Apache veya php -S)
        'name'      => 'Staj Takip',
        'locale'    => 'tr',
        'debug'     => true,
    ],

    // --- Oturum (session) ---
    'session' => [
        'name'      => 'staj_sess',
        'secure'    => false,   // yerel HTTP; prod'da true + HTTPS
        'httponly'  => true,
        'samesite'  => 'Lax',
    ],

    // --- Dosya yükleme ---
    'upload' => [
        // public/storage/uploads içinde, web erişilebilir
        'dir'       => __DIR__ . '/../public/storage/uploads',
        'base_url'  => '/storage/uploads',
        'max_kb'    => 2048,        // 2 MB
        'allowed'   => ['image/jpeg','image/png','image/webp'],
    ],

    // --- Şirket onay linki */
    'company' => [
        'token_bytes' => 32,        // 32 byte = 64 hex karakter
        'ttl_days'    => 14,        // link 14 gün geçerli
    ],

    // --- Şifreleme ---
    'auth' => [
        // PHP 7.4+ varsayılanı (PASSWORD_BCRYPT = bcrypt). Argon2 idealdir.
        'hash_algo' => PASSWORD_DEFAULT, // argon2i/2id mevcutsa
    ],
];
