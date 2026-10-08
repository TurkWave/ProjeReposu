<?php
// ============================================================
// Staj Takip - Middleware: oturum kontrolü
// ============================================================
declare(strict_types=1);

namespace App\Middleware;

use App\Auth;

class MustLogin
{
    public static function check(): void
    {
        if (!Auth::check()) {
            // Login sayfasına yönlendir, gelinen URL'i hatırla.
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
            $base = \App\Config::get('app.base_url');
            header('Location: ' . $base . '/login', true, 302);
            exit;
        }
    }
}
