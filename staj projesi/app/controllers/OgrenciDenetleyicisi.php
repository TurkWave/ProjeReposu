<?php
// ============================================================
// StudentController - öğrenci akışı
// middleware: student
// ============================================================
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Controller;
use App\Models\Internship;
use App\Models\DailyRecord;
use App\Models\Journal;
use App\Models\Issue;
use App\Models\FaqItem;
use App\Models\ChecklistItem;
use App\RequestMethod;

// Yardımcı fonksiyonlar (App\yardimcilar.php)
use function App\flash;
use function App\redirect;
use function App\csrf_token;

class StudentController extends Controller
{
    public function middleware(): array
    {
        return [[\App\Middleware\MustRole::class, ['student']]];
    }

    private function myInternship(): ?array
    {
        $inModel = new Internship();
        return $inModel->forStudent(Auth::id());
    }

    /** Öğrenci ana sayfası */
    public function dashboard(): void
    {
        $internship = $this->myInternship();
        if (!$internship) {
            $this->view('student/staj_yok', ['msg' => 'Staj kaydınız bulunmamaktadır.'], 'app');
            return;
        }
        $today = date('Y-m-d');
        $iid   = (int)$internship['id'];
        $dr = (new DailyRecord())->findByInternshipAndDate($iid, $today);
        $jr = (new Journal())->findByInternshipAndDate($iid, $today);

        $this->view('student/ana_sayfa', [
            'internship' => $internship,
            'todayRecord'  => $dr,
            'todayJournal' => $jr,
            'today'   => $today,
            'csrf'    => csrf_token(),
            'error'   => flash('error'),
            'success' => flash('success'),
        ], 'app');
    }

    /** Günlük yoklama + defter kaydı (tek form, ~1 dk). */
    public function dailyEntry(): void
    {
        $internship = $this->myInternship();
        if (!$internship) { redirect('/student'); }
        $this->checkCsrf();
        $today = date('Y-m-d');
        $iid   = (int)$internship['id'];

        if (RequestMethod::isPost()) {
            $present  = isset($_POST['present']) ? 1 : 0;
            $location = trim($_POST['location'] ?? '');
            $location = $location !== '' ? $location : null;
            $note     = trim($_POST['note'] ?? '');
            $note     = $note !== '' ? $note : null;

            $db = $this->db;
            try {
                $db->beginTransaction();
                $existing = (new DailyRecord())->findByInternshipAndDate($iid, $today);
                if ($existing) {
                    $db->prepare('UPDATE daily_records SET present = ?, location = ?, updated_at = NOW() WHERE id = ?')
                       ->execute([$present, $location, $existing['id']]);
                } else {
                    $db->prepare('INSERT INTO daily_records (internship_id,date,present,location) VALUES (?,?,?,?)')
                       ->execute([$iid, $today, $present, $location]);
                }
                $photoUrl = null;
                if (!empty($_FILES['photo']['name'])) {
                    $up = $this->uploadPhoto($_FILES['photo']);
                    if ($up !== false) $photoUrl = $up;
                }
                $existingJ = (new Journal())->findByInternshipAndDate($iid, $today);
                if ($existingJ) {
                    $db->prepare('UPDATE journal SET entry = ?, photo_url = ?, updated_at = NOW() WHERE id = ?')
                       ->execute([$note, $photoUrl ?? $existingJ['photo_url'], $existingJ['id']]);
                } else {
                    $db->prepare('INSERT INTO journal (internship_id,date,entry,photo_url) VALUES (?,?,?,?)')
                       ->execute([$iid, $today, $note, $photoUrl]);
                }
                $db->commit();
                flash('success', 'Kaydınız güncellendi.');
            } catch (\Throwable $e) {
                $db->rollBack();
                flash('error', 'Kaydedilirken bir hata oluştu: ' . $e->getMessage());
            }
            redirect('/student');
        }

        $dr = (new DailyRecord())->findByInternshipAndDate($iid, $today);
        $jr = (new Journal())->findByInternshipAndDate($iid, $today);
        $this->view('student/gunluk_kaydi', [
            'internship' => $internship,
            'record'    => $dr,
            'journal'   => $jr,
            'today'     => $today,
            'csrf'      => csrf_token(),
            'error'     => flash('error'),
        ], 'app');
    }

    private function uploadPhoto(array $file)
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return false;
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        $mime  = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) finfo_close($finfo);
        $allowed = Config::get('upload.allowed');
        if (!in_array($mime, $allowed, true)) return false;
        if ($file['size'] > Config::get('upload.max_kb') * 1024) return false;
        $ext = match ($mime) {
            'image/jpeg' => '.jpg', 'image/png'  => '.png',
            'image/webp' => '.webp', default => '.jpg',
        };
        $fname = 'photo_' . time() . '_' . bin2hex(random_bytes(6)) . $ext;
        $dir   = Config::get('upload.dir');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dest  = $dir . DIRECTORY_SEPARATOR . $fname;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
                return Config::get('upload.base_url') . '/' . $fname;
    }

    /** Geçmiş günlük kayıtları (defter) */
    public function dailyHistory(): void
    {
        $internship = $this->myInternship();
        if (!$internship) { redirect('/student'); }
        $records = (new DailyRecord())->forInternship((int)$internship['id']);
        $journal = (new Journal())->forInternship((int)$internship['id']);
        $byDate = [];
        foreach ($records as $r) $byDate[$r['date']]['record'] = $r;
        foreach ($journal as $j) $byDate[$j['date']]['journal'] = $j;
        krsort($byDate);
        $this->view('student/gunluk_gecmisi', [
            'internship' => $internship,
            'entries' => $byDate,
        ], 'app');
    }

    /** Sorun listesi / oluşturma */
    public function issues(): void
    {
        $internship = $this->myInternship();
        if (!$internship) { redirect('/student'); }
        $this->checkCsrf();

        if (RequestMethod::isPost()) {
            $category = $_POST['category'] ?? 'other';
            $desc     = trim($_POST['description'] ?? '');
            $anon     = isset($_POST['anonymous']) ? 1 : 0;
            if ($desc === '') {
                flash('error', 'Açıklama boş bırakılamaz.');
                redirect('/student/sorunlar');
            }
            (new Issue())->insert([
                'internship_id' => (int)$internship['id'],
                'reported_by'   => Auth::id(),
                'category'      => $category,
                'description'   => $desc,
                'anonymous'     => $anon,
            ]);
            flash('success', 'Sorun bildiriniz gönderildi. Öğretmeniniz inceleyecektir.');
            redirect('/student/sorunlar');
        }

        $list = (new Issue())->forInternship((int)$internship['id']);
                $this->view('student/sorunlar', [
            'internship' => $internship,
            'issues' => $list,
            'categories' => Issue::CATEGORIES,
            'csrf' => csrf_token(),
        ], 'app');
    }

    /** Tek bir sorun: öğrenci gördüğünde detay (MVP: görüntüle + sil). */
    public function issueEdit(?string $id): void
    {
        $internship = $this->myInternship();
        if (!$internship) { redirect('/student'); }
        $iid = (int)$internship['id'];

        if (RequestMethod::isPost() && ($_POST['_action'] ?? '') === 'delete') {
            $this->checkCsrf();
            $issue = (new Issue())->find((int)$id);
            if (!$issue || (int)$issue['internship_id'] !== $iid || (int)$issue['reported_by'] !== Auth::id()) {
                $this->error('Bu bildirim size ait değil.', 403);
            }
            (new Issue())->delete((int)$id);
            flash('success', 'Bildirim silindi.');
            redirect('/student/sorunlar');
        }

        $issue = (new Issue())->find((int)$id);
        if (!$issue || (int)$issue['internship_id'] !== $iid || (int)$issue['reported_by'] !== Auth::id()) {
            $this->error('Bu bildirim size ait değil.', 403);
        }
        $this->view('student/sorun_detayi', [
            'internship' => $internship,
            'issue' => $issue,
            'categories' => Issue::CATEGORIES,
            'csrf' => csrf_token(),
        ], 'app');
    }

    public function checklist(): void
    {
        $items = (new ChecklistItem())->active();
        $this->view('student/kontrol_listesi', ['items' => $items], 'app');
    }

    public function faq(): void
    {
        $items = (new FaqItem())->active();
        $this->view('student/sss', ['items' => $items], 'app');
    }

    public function internship(): void
    {
        $internship = $this->myInternship();
        if (!$internship) {
            $this->view('student/staj_yok', ['msg' => 'Staj kaydınız bulunmamaktadır.'], 'app');
            return;
        }
        $this->view('student/staj_bilgileri', ['internship' => $internship], 'app');
    }
}

