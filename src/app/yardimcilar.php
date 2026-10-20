<?php
// ============================================================
// Staj Takip - Yardımcı fonksiyonlar (helpers)
// ============================================================
declare(strict_types=1);

namespace App;

/**
 * XSS korumalı çıktı. HTML bağlamında güvenle metin gösterir.
 */
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * CSRF token oluştur / sakla / doğrula.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    return $token !== null && hash_equals($_SESSION['csrf'] ?? '', $token);
}

/**
 * Flash mesajları (session üzerinden).
 */
function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return $value;
    }
    $v = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $v;
}

/**
 * Yönlendirme.
 */
function redirect(string $path, int $code = 302): void
{
    $base = Config::get('app.base_url');
    header('Location: ' . $base . $path, true, $code);
    exit;
}

/**
 * Config erişimi (tek seferde önbelleğe alınır).
 */
class Config
{
    private static ?array $cache = null;

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            self::$cache = require __DIR__ . '/ayarlar.php';
        }
        $parts = explode('.', $key);
        $val = self::$cache;
        foreach ($parts as $p) {
            if (!is_array($val) || !array_key_exists($p, $val)) {
                return $default;
            }
            $val = $val[$p];
        }
        if ($key === 'app.base_url' && $val === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
            if (!preg_match('/\A(?:localhost|127\.0\.0\.1)(?::\d{1,5})?\z/i', $host)) {
                $host = '127.0.0.1:8765';
            }
            return $scheme . '://' . $host;
        }
        return $val;
    }
}

/**
 * URL yardımcıları.
 */
function url(string $path = ''): string
{
    return Config::get('app.base_url') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

/**
 * Seçilen ayarı (checkbox vb.) kontrol eder.
 */
function checked(bool $cond): string
{
    return $cond ? ' checked' : '';
}

/**
 * Tarih formatı (insan okunur).
 */
function tarih(\DateTimeInterface|string|null $dt): string
{
    if ($dt === null) return '-';
    if (is_string($dt)) $dt = new \DateTime($dt);
    return $dt->format('d.m.Y H:i');
}

/** Tarihi Türkçe ay ve gün adlarıyla gösterir. */
function tarih_tr(\DateTimeInterface|string|null $dt, bool $withTime = false): string
{
    if ($dt === null) return '-';
    if (is_string($dt)) $dt = new \DateTime($dt);
    $months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $days = [1 => 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
    $date = $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
    if ($withTime) $date .= ' ' . $dt->format('H:i');
    return $date;
}

/**
 * Gün adı kısaltması (Pazartesi -> Pzt).
 */
function gun_adi(string $isoDate): string
{
    $d = (new \DateTime($isoDate))->format('N');
    $gun = ['1' => 'Pzt', '2' => 'Sal', '3' => 'Çar', '4' => 'Per',
            '5' => 'Cum', '6' => 'Cmt', '7' => 'Paz'];
    return $gun[$d] ?? '';
}

function durum_tr(string $status): string
{
    return [
        'pending_company' => 'İşletme onayı bekliyor',
        'active' => 'Devam ediyor',
        'completed' => 'Tamamlandı',
        'new' => 'Yeni',
        'in_progress' => 'İnceleniyor',
        'resolved' => 'Çözüldü',
    ][$status] ?? $status;
}
