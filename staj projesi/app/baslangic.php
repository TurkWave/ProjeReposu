<?php
// ============================================================
// Staj Takip - Bootstrap
// ============================================================
declare(strict_types=1);

use App\Config;

$cfg = require __DIR__ . '/ayarlar.php';

// Hata raporlama (dev ortamı).
ini_set('display_errors', ($cfg['app']['debug'] ?? false) ? '1' : '0');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

// --- Session güvenli yapılandırması ---
$sess = $cfg['session'];
session_name($sess['name']);
$sapi = PHP_SAPI;
$cookieSecure = $sess['secure'] && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $cookieSecure,
    'httponly' => $sess['httponly'],
    'samesite' => $sess['samesite'],
]);
session_start();

// --- Ortak sınıfları yükle (manuel autoload; composer yok) ---
spl_autoload_register(function (string $class): void {
    $files = [
        'App\\Auth' => 'Kimlik.php', 'App\\Database' => 'Veritabani.php', 'App\\Router' => 'Yonlendirici.php', 'App\\Controller' => 'Denetleyici.php', 'App\\Model' => 'TemelModel.php',
        'App\\Middleware\\MustLogin' => 'middleware/GirisGerekli.php', 'App\\Middleware\\MustRole' => 'middleware/RolGerekli.php',
        'App\\Controllers\\AuthController' => 'controllers/GirisDenetleyicisi.php', 'App\\Controllers\\StudentController' => 'controllers/OgrenciDenetleyicisi.php',
        'App\\Controllers\\TeacherController' => 'controllers/OgretmenDenetleyicisi.php', 'App\\Controllers\\CompanyController' => 'controllers/IsyeriDenetleyicisi.php', 'App\\Controllers\\StaticController' => 'controllers/GenelSayfaDenetleyicisi.php',
        'App\\Models\\User' => 'models/Kullanici.php', 'App\\Models\\Journal' => 'models/StajDefteri.php', 'App\\Models\\Issue' => 'models/Sorun.php',
        'App\\Models\\Internship' => 'models/Staj.php', 'App\\Models\\FaqItem' => 'models/SssMaddesi.php', 'App\\Models\\DailyRecord' => 'models/GunlukYoklama.php',
        'App\\Models\\CompanyConfirmation' => 'models/IsyeriOnayi.php', 'App\\Models\\Company' => 'models/Isyeri.php', 'App\\Models\\ChecklistItem' => 'models/KontrolMaddesi.php',
    ];
    if (isset($files[$class])) require __DIR__ . '/' . $files[$class];
});

// Yardımcı fonksiyonlar (namespace dışındaki global fonksiyonlar için).
require_once __DIR__ . '/yardimcilar.php';

// Veritabanı başlat.
\App\Database::init($cfg);

// Global config erişimi için yardimcilar.php'deki Config sınıfı zaten dosyayı okur,
// fakat bootstrap'ta da $cfg mevcut; gerekirse Config::get üzerinden.
