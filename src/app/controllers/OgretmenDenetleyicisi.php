<?php
// ============================================================
// TeacherController - öğretmen akışı
// middleware: teacher
// ============================================================
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Controller;
use App\Models\Internship;
use App\Models\DailyRecord;
use App\Models\Journal;
use App\Models\Issue;
use App\Models\CompanyConfirmation;
use App\RequestMethod;

use function App\flash;
use function App\redirect;
use function App\csrf_token;

class TeacherController extends Controller
{
    public function middleware(): array
    {
        return [[\App\Middleware\MustRole::class, ['teacher']]];
    }

    /** Tek ekran özet panosu */
    public function dashboard(): void
    {
        $teacherId = Auth::id();
        $today = date('Y-m-d');

        $db = $this->db;
        $sql = "SELECT i.id, i.status, i.company_confirmed, i.teacher_comment,
                       u.name AS student_name, u.email AS student_email,
                       c.name AS company_name,
                       dr.present AS present_today, dr.date AS record_date
                FROM internships i
                JOIN users u ON u.id = i.student_id
                JOIN companies c ON c.id = i.company_id
                LEFT JOIN daily_records dr ON dr.internship_id = i.id AND dr.date = ?
                WHERE i.teacher_id = ?
                ORDER BY u.name";
        $stmt = $db->prepare($sql);
        $stmt->execute([$today, $teacherId]);
        $rows = $stmt->fetchAll();

        $missingToday = [];
        foreach ($rows as $r) {
            if (!isset($r['present_today']) || $r['present_today'] === null || $r['present_today'] == '0') {
                $missingToday[] = $r;
            }
        }
        $pendingCompany = array_filter($rows, fn($r) => $r['status'] === 'pending_company');
        $allIssues = (new Issue())->forTeacher($teacherId, 'all');
        $openIssues = array_filter($allIssues, fn($i) => in_array($i['status'], ['new', 'in_progress'], true));

        $this->view('teacher/ogretmen_paneli', [
            'internships'   => $rows,
            'missingToday'  => $missingToday,
            'pendingCompany'=> $pendingCompany,
            'openIssues'    => $openIssues,
            'today'         => $today,
            'count'         => [
                'missing' => count($missingToday),
                'pending' => count($pendingCompany),
                'issues'  => count($openIssues),
                'total'   => count($rows),
            ],
                        'csrf' => csrf_token(),
        ], 'app');
    }

    /** Öğrenci staj detayı + yorum + günlük kayıtlar */
    public function internshipView(?string $id): void
    {
        $this->checkCsrf();
        $intModel = new Internship();
        $internship = $intModel->get((int)$id);
        if (!$internship || (int)$internship['teacher_id'] !== Auth::id()) {
            $this->error('Staj kaydı bulunamadı ya da erişim yetkiniz yok.', 403);
        }

        if (RequestMethod::isPost()) {
            $comment = trim($_POST['teacher_comment'] ?? '');
            $intModel->addComment((int)$id, $comment);
            flash('success', 'Yorum kaydedildi.');
            redirect('/teacher/ogrenci_staji/' . $id);
        }

        $records = (new DailyRecord())->forInternship((int)$id);
        $journal = (new Journal())->forInternship((int)$id);
        $byDate = [];
        foreach ($records as $r) $byDate[$r['date']]['record'] = $r;
        foreach ($journal as $j) $byDate[$j['date']]['journal'] = $j;
        krsort($byDate);

        $cc = (new CompanyConfirmation())->forInternship((int)$id);

        $this->view('teacher/ogrenci_staji', [
            'internship'  => $internship,
            'entries'     => $byDate,
            'confirmToken' => $cc ? $cc['token'] : null,
            'csrf'        => csrf_token(),
            'error'       => flash('error'),
            'success'     => flash('success'),
        ], 'app');
    }

    /** Yorum POST uç noktası */
    public function comment(): void
    {
        $this->checkCsrf();
        $id = (int)($_POST['internship_id'] ?? 0);
        $comment = trim($_POST['teacher_comment'] ?? '');
        $intModel = new Internship();
        $internship = $intModel->get($id);
        if (!$internship || (int)$internship['teacher_id'] !== Auth::id()) {
            $this->error('Erişim reddedildi.', 403);
        }
                $intModel->addComment($id, $comment);
        flash('success', 'Yorum kaydedildi.');
        redirect('/teacher/ogrenci_staji/' . $id);
    }

    /** Sorun listesi (öğretmenin öğrencilerinden) */
    public function issues(): void
    {
        $filter = $_GET['status'] ?? 'open';
        $allIssues = (new Issue())->forTeacher(Auth::id(), 'all');
        if ($filter === 'open') {
            $allIssues = array_filter($allIssues, fn($i) => in_array($i['status'], ['new', 'in_progress'], true));
        }
        $this->view('teacher/sorunlar', [
            'issues'     => $allIssues,
            'filter'     => $filter,
            'categories' => Issue::CATEGORIES,
        ], 'app');
    }

    /** Sorun durumu güncelle */
    public function issueStatus(?string $id): void
    {
        $this->checkCsrf();
        $issueId = (int)$id;
        $im = new Issue();
        $issue = $im->find($issueId);
        $internship = (new Internship())->find((int)($issue['internship_id'] ?? 0));
        if (!$issue || !$internship || (int)$internship['teacher_id'] !== Auth::id()) {
            $this->error('Erişim reddedildi.', 403);
        }
        $status = $_POST['status'] ?? 'in_progress';
        if (!in_array($status, ['new', 'in_progress', 'resolved'], true)) {
            $status = 'in_progress';
        }
        $im->updateStatus($issueId, $status);
        flash('success', 'Durum güncellendi.');
        redirect('/teacher/sorunlar');
    }

    /** İşletmeye gönderilecek onay linkini göster (GET) */
    public function companyLink(?string $id): void
    {
        $intModel = new Internship();
        $internship = $intModel->get((int)$id);
        if (!$internship || (int)$internship['teacher_id'] !== Auth::id()) {
            $this->error('Staj kaydı bulunamadı ya da erişim yetkiniz yok.', 403);
        }
        $cc = (new CompanyConfirmation())->forInternship((int)$id);
        $this->view('teacher/isyeri_baglantisi', [
            'internship' => $internship,
            'confirm'    => $cc,
            'base_url'   => \App\Config::get('app.base_url'),
        ], 'app');
    }
}


