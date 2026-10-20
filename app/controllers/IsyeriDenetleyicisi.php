<?php
// ============================================================
// CompanyController - işletme onay linki (token-public, login yok)
// /company/{token}
// ============================================================
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Controller;
use App\Models\CompanyConfirmation;
use App\Models\Internship;
use App\RequestMethod;

// Yardımcı fonksiyonlar (App\yardimcilar.php)
use function App\flash;
use function App\redirect;
use function App\csrf_token;

class CompanyController extends Controller
{
    public function middleware(): array
    {
        return []; // token ile doğrulama yapılır
    }

    const EVAL_QUESTIONS = [
        'q1' => 'Öğrencinin görevlerini titizlikle yerine getirmesi',
        'q2' => 'İş güvenliği kurallarına uyulması',
        'q3' => 'Belirtilen süre içinde görevlere katılımı',
        'q4' => 'Genel olarak staj sürecine katılımı',
    ];

    public function confirmation(string $token): void
    {
        $this->checkCsrf();
        $ccModel = new CompanyConfirmation();
        $conf = $ccModel->findByToken($token);

        if (!$conf) {
            $this->error('Geçersiz onay bağlantısı.', 404);
        }
        // Linkin süresi dolmamalı.
        if (strtotime($conf['expires_at']) < time()) {
            $this->error('Bu onay bağlantısının süresi doldu.', 419);
        }
        // Tek kullanımlık: zaten onaylanmışsa tekrar göster ama yeniden doldurmaya izin
        // vermeyelim (süreli+tek amaçlı).
        if ((int)$conf['confirmed'] === 1) {
            $this->view('company/onaylandi', ['conf' => $conf], 'company');
            return;
        }

        if (RequestMethod::isPost()) {
            $this->confirmSubmit($conf);
            return;
        }

        // GET: öğrenci/staj bilgilerini göster + form.
        $internship = (new Internship())->get((int)$conf['internship_id']);
        $this->view('company/onay_formu', [
            'conf'        => $conf,
            'internship'  => $internship,
            'questions'   => self::EVAL_QUESTIONS,
            'csrf'        => csrf_token(),
            'action_url'  => '/company/' . $conf['token'],
        ], 'company');
    }

    private function confirmSubmit(array $conf): void
    {
        // 3-4 soruluk değerlendirme: her biri 1-5
        $answers = [];
        $valid = true;
        foreach (self::EVAL_QUESTIONS as $key => $_) {
            $val = (int)($_POST[$key] ?? 0);
            if ($val < 1 || $val > 5) { $valid = false; break; }
            $answers[$key] = $val;
        }
        if (!$valid) {
            flash('error', 'Lütfen tüm değerlendirme sorularını 1-5 arasında puanlayın.');
            $this->confirmation($conf['token']);
            return;
        }
        $feedback = trim($_POST['feedback'] ?? '');
        if (mb_strlen($feedback) > 1000) $feedback = mb_substr($feedback, 0, 1000);

        $ccModel = new CompanyConfirmation();
        $ccModel->confirm((int)$conf['id'], json_encode($answers, JSON_UNESCAPED_UNICODE), $feedback);

        // internship'ı onaylandı / aktif yap.
        $intModel = new Internship();
        $intModel->confirmCompany((int)$conf['internship_id']);

        flash('success', 'Değerlendirmeniz kaydedildi. Teşekkürler!');
        redirect('/company/' . $conf['token']);
    }
}
