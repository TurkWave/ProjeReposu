<?php
// ============================================================
// Staj Takip - Middleware: rol kontrolü
// Kullanım: [MustRole::class, ['student']]  -> sadece öğrenci
// ============================================================
declare(strict_types=1);

namespace App\Middleware;

use App\Auth;
use App\Config;

class MustRole
{
    /**
     * @param string[]|string $roles kabul edilen roller
     */
    public static function check($roles = null): void
    {
        // Oturum kontrolü
        if (!Auth::check()) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
            $base = Config::get('app.base_url');
            header('Location: ' . $base . '/login', true, 302);
            exit;
        }
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array(Auth::role(), $allowed, true)) {
            http_response_code(403);
            echo '<!doctype html><meta charset="utf-8">';
            echo '<h1>403 - Erişim reddedildi</h1>';
            echo '<p>Bu sayfayı görüntüleme yetkiniz yok.</p>';
            echo '<p><a href="/">Ana sayfaya dön</a></p>';
            exit;
        }
    }
}
