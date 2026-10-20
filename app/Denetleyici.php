<?php
// ============================================================
// Staj Takip - Temel Controller
// ============================================================
declare(strict_types=1);

namespace App;

use PDO;

abstract class Controller
{
    protected array $cfg;
    protected PDO $db;

    public function __construct()
    {
        $this->cfg = require __DIR__ . '/ayarlar.php';
        $this->db   = Database::get();
    }

    /** Override edilebilir middleware listesi (class bazlı). */
    public function middleware(): array
    {
        return [];
    }

    // CSRF koruması: tüm POST isteklerinde token zorunlu.
    protected function checkCsrf(): void
    {
        if (RequestMethod::isPost()) {
            $token = $_POST['csrf_token'] ?? null;
            if (!csrf_check($token)) {
                $this->error('Geçersiz istek (CSRF). Lütfen tekrar deneyin.', 419);
            }
        }
    }

    /** View render + layout. */
    protected function view(string $template, array $data = [], string $layout = 'layout'): void
    {
        $viewFile = __DIR__ . "/views/{$template}.php";
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo '<h1>500 - View bulunamadı: ' . e($template) . '</h1>';
            return;
        }
        // View değişkenlerini extract et.
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layouts = ['app' => 'ana_duzen', 'auth' => 'giris_duzeni', 'company' => 'isyeri_duzeni'];
        $layoutName = $layouts[$layout] ?? $layout;
        $layoutFile = __DIR__ . "/views/layouts/{$layoutName}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /** JSON yanıt. */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Hata mesajı + yönlendirme patterni için flash + redirect. */
    protected function back(string $message = ''): void
    {
        if ($message) flash('error', $message);
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        // base_url içermeyen dış linkleri engelle.
        $base = Config::get('app.base_url');
        if (strpos($referer, $base) !== 0) {
            $referer = $base;
        }
        redirect(parse_url($referer, PHP_URL_PATH));
    }

    protected function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo '<!doctype html><meta charset="utf-8"><title>Hata</title>';
        echo '<h1>' . e((string)$code) . ' - Hata</h1>';
        echo '<p>' . e($message) . '</p>';
        echo '<p><a href="/">Ana sayfaya dön</a></p>';
        exit;
    }
}

/** Basit request-yardımcısı. */
class RequestMethod
{
    public static function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    public static function isGet(): bool  { return $_SERVER['REQUEST_METHOD'] === 'GET'; }
}
