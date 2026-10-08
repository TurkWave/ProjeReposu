<?php
// ============================================================
// AuthController - kimlik, anasayfa, kayıt
// ============================================================
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Controller;
use App\RequestMethod;

// Namespace içi yardımcı fonksiyonlar (App\yardimcilar.php)
use function App\flash;
use function App\redirect;
use function App\csrf_token;

class AuthController extends Controller
{
    public function middleware(): array
    {
        return []; // kimse giriş yapmaya ya da kaydolmaya erişebilir
    }

    /** Anasayfa: rol yönlendirmesi */
    public function home(): void
    {
        if (Auth::isStudent()) {
            redirect('/student');
        }
        if (Auth::isTeacher()) {
            redirect('/teacher');
        }
        redirect('/login');
    }

    public function loginForm(?string $role = null): void
    {
        if (Auth::check()) {
            $this->home();
        }
        if ($role === null) {
            $this->view('auth/giris_turu', [
                'title' => 'Giriş Türünü Seç',
            ], 'auth');
            return;
        }
        $roles = ['ogrenci' => ['key' => 'student', 'label' => 'Öğrenci'], 'ogretmen' => ['key' => 'teacher', 'label' => 'Öğretmen']];
        if (!isset($roles[$role])) {
            $this->error('Giriş türü bulunamadı.', 404);
        }
        $err = flash('error');
        $this->view('auth/giris', [
            'err' => $err,
            'csrf' => csrf_token(),
            'role' => $role,
            'roleLabel' => $roles[$role]['label'],
            'formAction' => '/giris/' . $role,
            'title' => $roles[$role]['label'] . ' Girişi',
        ], 'auth');
    }

    public function roleLogin(string $role): void
    {
        if (RequestMethod::isPost()) {
            $this->login($role);
            return;
        }
        $this->loginForm($role);
    }

    public function companyAccess(): void
    {
        if (RequestMethod::isPost()) {
            $this->checkCsrf();
            $provided = trim($_POST['company_link'] ?? '');
            $path = parse_url($provided, PHP_URL_PATH);
            $candidate = is_string($path) ? $path : $provided;
            if (preg_match('~/(?:company|isletme)/([a-f0-9]{64})\z~i', $candidate, $match)) {
                redirect('/company/' . strtolower($match[1]));
            }
            if (preg_match('/\A[a-f0-9]{64}\z/i', $provided)) {
                redirect('/company/' . strtolower($provided));
            }
            $this->view('auth/isyeri_erisim', [
                'err' => 'Geçerli bir işletme değerlendirme bağlantısı girin.',
                'csrf' => csrf_token(),
                'title' => 'İşyeri Girişi',
            ], 'auth');
            return;
        }
        $this->view('auth/isyeri_erisim', [
            'err' => flash('error'),
            'csrf' => csrf_token(),
            'title' => 'İşyeri Girişi',
        ], 'auth');
    }

    public function login(?string $role = null): void
    {
        $this->checkCsrf();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roles = ['ogrenci' => ['key' => 'student', 'label' => 'Öğrenci'], 'ogretmen' => ['key' => 'teacher', 'label' => 'Öğretmen']];
        $expectedRole = $role !== null && isset($roles[$role]) ? $roles[$role]['key'] : null;
        if ($role !== null && $expectedRole === null) {
            $this->error('Giriş türü bulunamadı.', 404);
        }

        $user = Auth::login($email, $password, $expectedRole);
        if (!$user) {
            flash('error', $role === null ? 'E-posta veya şifre hatalı.' : 'Bilgiler hatalı veya bu hesap seçilen giriş türüne ait değil.');
            redirect($role === null ? '/login' : '/giris/' . $role);
        }

        // Kayıtlı yönlendirme varsa oraya, yoksa rol dashboardına.
        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        $path = ($intended && strpos($intended, Config::get('app.base_url')) === 0)
            ? parse_url($intended, PHP_URL_PATH)
            : ($user['role'] === 'teacher' ? '/teacher' : '/student');
        redirect($path);
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->home();
        }
        $this->view('auth/kayit', [
            'err'   => flash('error'),
            'ok'    => flash('success'),
            'csrf'  => csrf_token(),
        ], 'auth');
    }

    public function register(): void
    {
        $this->checkCsrf();
        $res = Auth::register([
            'name'         => $_POST['name'] ?? '',
            'email'        => $_POST['email'] ?? '',
            'password'     => $_POST['password'] ?? '',
            'is_minor'     => $_POST['is_minor'] ?? 0,
            'consent_kvkk' => $_POST['consent_kvkk'] ?? 0,
            'consent_parent'=> $_POST['consent_parent'] ?? 0,
        ]);

        if (!$res['ok']) {
            flash('error', $res['error']);
            redirect('/register');
        }
        flash('success', 'Kayıt başarılı. Hoş geldiniz!');
        redirect('/student');
    }

    public function logout(): void
    {
        $this->checkCsrf();
        Auth::logout();
        redirect('/login');
    }
}
