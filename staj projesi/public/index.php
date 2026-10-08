<?php
// ============================================================
// Staj Takip - Front Controller
// Tüm istekler buraya gelir. (Apache .htaccess / php -S router)
// ============================================================
declare(strict_types=1);

require __DIR__ . '/../app/baslangic.php';

use App\Controllers\AuthController;
use App\Controllers\StudentController;
use App\Controllers\TeacherController;
use App\Controllers\CompanyController;
use App\Controllers\StaticController;

$router = new App\Router();

// --- Kimlik (giriş / kayıt / çıkış) ---     AuthController (genel)
$router->get('/login',    [AuthController::class, 'loginForm']);
$router->post('/login',   [AuthController::class, 'login']);
$router->match(['GET','POST'], '/giris/isletme', [AuthController::class, 'companyAccess']);
$router->match(['GET','POST'], '/giris/{role}', [AuthController::class, 'roleLogin']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register',[AuthController::class, 'register']);
$router->post('/logout',  [AuthController::class, 'logout']);

// --- Anasayfa (rol yönlendirimi) ---
$router->get('/',  [AuthController::class, 'home']);

// --- Öğrenci akışı (StudentController::middleware -> student) ---
$router->get('/student',                 [StudentController::class, 'dashboard']);
$router->match(['GET','POST'], '/student/daily',        [StudentController::class, 'dailyEntry']);
$router->get('/student/daily/history',   [StudentController::class, 'dailyHistory']);
$router->match(['GET','POST'], '/student/sorunlar',       [StudentController::class, 'issues']);
$router->match(['GET','POST'], '/student/issue/{id}',   [StudentController::class, 'issueEdit']);
$router->get('/student/kontrol_listesi',       [StudentController::class, 'checklist']);
$router->get('/student/sss',             [StudentController::class, 'faq']);
$router->match(['GET','POST'], '/student/staj_bilgileri',   [StudentController::class, 'internship']);

// --- Öğretmen akışı (TeacherController::middleware -> teacher) ---
$router->get('/teacher',                  [TeacherController::class, 'dashboard']);
$router->match(['GET','POST'], '/teacher/ogrenci_staji/{id}', [TeacherController::class, 'internshipView']);
$router->match(['GET','POST'], '/teacher/comment',         [TeacherController::class, 'comment']);
$router->get('/teacher/sorunlar',          [TeacherController::class, 'issues']);
$router->post('/teacher/issue/{id}/status',[TeacherController::class, 'issueStatus']);
$router->get('/teacher/company-link/{id}',[TeacherController::class, 'companyLink']);

// --- İşletme onay sayfası (token-public) ---
$router->match(['GET','POST'], '/company/{token}', [CompanyController::class, 'confirmation']);

// --- Statik (SSS / kontrol listesi / KVKK - giriş isteyebilir) ---
$router->get('/faq',      [StaticController::class, 'faq']);
$router->get('/checklist',  [StaticController::class, 'checklist']);
$router->get('/kvkk',      [StaticController::class, 'kvkk']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

