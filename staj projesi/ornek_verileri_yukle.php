<?php
// ============================================================
// ornek_verileri_yukle.php - Örnek (seed) veriler
//  1 öğretmen, 5 öğrenci, 2 işletme, 5 staj, örnek yoklama/defter/
//  sorun ve işletme onay token'ları.
// Çalıştır:  php ornek_verileri_yukle.php   (DB schema.sql'den sonra)
// ============================================================
declare(strict_types=1);

$cfg = require __DIR__ . '/app/ayarlar.php';
$dsn = sprintf('mysql:host=%s;charset=%s', $cfg['db']['host'], $cfg['db']['charset']);
$db  = new PDO($dsn, $cfg['db']['user'], $cfg['db']['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$db->exec("USE `" . $cfg['db']['name'] . "`;");

echo "[1/8] Eski veriler temizleniyor...\n";
$db->exec("SET FOREIGN_KEY_CHECKS=0");
$db->exec("DELETE FROM issues");
$db->exec("DELETE FROM daily_records");
$db->exec("DELETE FROM journal");
$db->exec("DELETE FROM company_confirmations");
$db->exec("DELETE FROM consents");
$db->exec("DELETE FROM internships");
$db->exec("DELETE FROM companies");
$db->exec("DELETE FROM users");
$db->exec("DELETE FROM faq_items");
$db->exec("DELETE FROM checklist_items");
$db->exec("SET FOREIGN_KEY_CHECKS=1");
$db->exec("ALTER TABLE users AUTO_INCREMENT = 1");
$db->exec("ALTER TABLE companies AUTO_INCREMENT = 1");
$db->exec("ALTER TABLE internships AUTO_INCREMENT = 1");

echo "[2/8] Firmalar ekleniyor...\n";
$db->prepare("INSERT INTO companies (name, contact_email) VALUES (?, ?)")->execute(['Teknoil A.Ş.', 'iletisim@teknoil.com.tr']);
$teknoilId = (int)$db->lastInsertId();
$db->prepare("INSERT INTO companies (name, contact_email) VALUES (?, ?)")->execute(['Günışığı Kafe', 'iletisim@gunisikikafe.com.tr']);
$cafeId = 2;

echo "[3/8] Öğretmen ekleniyor...\n";
$teacherHash = password_hash('123456', $cfg['auth']['hash_algo']);
$db->prepare("INSERT INTO users (email, name, role, password_hash) VALUES (?, ?, 'teacher', ?)")
   ->execute(['teacher@okul.edu', 'Ahmet Yılmaz', $teacherHash]);
$teacherId = (int)$db->lastInsertId();

echo "[4/8] Öğrenciler ekleniyor...\n";
$students = [
    ['ali.veli@stu.edu',   'Ali Veli',         '123456', 0],
    ['fatma.cty@stu.edu',  'Fatma Çetinkaya',    '123456', 0],
    ['mehmet.x@stu.edu',   'Mehmet Kaya',        '123456', 1], // 18 yaş altı
    ['ayse.b@stu.edu',     'Ayşe Yıldız',        '123456', 0],
    ['can.deniz@stu.edu',  'Can Deniz',          '123456', 0],
];
$studentIds = [];
foreach ($students as $s) {
    $h = password_hash('123456', $cfg['auth']['hash_algo']);
    $db->prepare("INSERT INTO users (email, name, role, password_hash, is_minor, parent_consent_given, parent_consent_at) VALUES (?, ?, 'student', ?, ?, ?, ?)")
       ->execute([$s[0], $s[1], $h, $s[3], $s[3] ? 1 : 0, $s[3] ? date('Y-m-d H:i:s') : null]);
    $uid = (int)$db->lastInsertId();
    $studentIds[] = $uid;
    $db->prepare("INSERT INTO consents (user_id, consent_type, given, given_at) VALUES (?, 'kvkk', 1, ?)")
       ->execute([$uid, date('Y-m-d H:i:s')]);
        if ($s[3]) {
        $db->prepare("INSERT INTO consents (user_id, consent_type, given, given_at) VALUES (?, 'parent', 1, ?)")
           ->execute([$uid, date('Y-m-d H:i:s')]);
    }
}

echo "[5/8] Stajlar ekleniyor...\n";
$internships = [
    // studentId, companyId, status, companyConfirmed
    [$studentIds[0], $teknoilId, 'active',          1],
    [$studentIds[1], $cafeId,    'active',          1],
    [$studentIds[2], $teknoilId, 'pending_company', 0], // işletme onayı bekliyor
    [$studentIds[3], $cafeId,    'active',          1],
    [$studentIds[4], $teknoilId, 'active',          1],
];
$internIds = [];
foreach ($internships as $i) {
    $db->prepare("INSERT INTO internships (student_id, company_id, teacher_id, term, started_at, ended_at, status, company_confirmed) VALUES (?, ?, ?, '2024 Güz', '2024-09-01', '2024-12-15', ?, ?)")
       ->execute([$i[0], $i[1], $teacherId, $i[2], $i[3]]);
    $internIds[] = (int)$db->lastInsertId();
}

echo "[6/8] İşletme onay token'ları ekleniyor...\n";
$confirmedAt = date('Y-m-d H:i:s', strtotime('-3 days'));
for ($k = 0; $k < 5; $k++) {
    $token = bin2hex(random_bytes(32));
    if ($k === 2) {
        // pending (bekliyor)
        $db->prepare("INSERT INTO company_confirmations (internship_id, token, expires_at, confirmed, confirmed_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY), 0, NULL)")
           ->execute([$internIds[$k], $token]);
        file_put_contents(__DIR__ . '/bekleyen_isyeri_baglantisi.txt', $token);
    } else {
        $eval = json_encode(['q1' => 5, 'q2' => 4, 'q3' => 4, 'q4' => 5], JSON_UNESCAPED_UNICODE);
        $db->prepare("INSERT INTO company_confirmations (internship_id, token, expires_at, confirmed, confirmed_at, evaluation, feedback) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY), 1, ?, ?, ?)")
           ->execute([$internIds[$k], $token, $confirmedAt, $eval, 'Staj süreci verimli ve başarılı geçti.']);
    }
}

echo "[7/8] Örnek yoklama / defter / sorun ekleniyor...\n";
$today = date('Y-m-d');
$db->prepare("INSERT INTO daily_records (internship_id, date, present, location) VALUES (?, ?, 1, 'Üsküdar')")->execute([$internIds[0], $today]);
$db->prepare("INSERT INTO journal (internship_id, date, entry) VALUES (?, ?, ?)")->execute([$internIds[0], $today, 'Bugün uygulamalar arası bağlantı üzerinde çalıştım.']);
$db->prepare("INSERT INTO daily_records (internship_id, date, present, location) VALUES (?, ?, 1, 'Kadıköy')")->execute([$internIds[3], $today]);
$db->prepare("INSERT INTO journal (internship_id, date, entry) VALUES (?, ?, ?)")->execute([$internIds[3], $today, 'Müşteri toplantısına katıldım.']);

$day = date('Y-m-d', strtotime('-2 days'));
$db->prepare("INSERT INTO daily_records (internship_id, date, present) VALUES (?, ?, 1)")->execute([$internIds[0], $day]);
$db->prepare("INSERT INTO journal (internship_id, date, entry) VALUES (?, ?, ?)")->execute([$internIds[0], $day, 'Veritabanı performansını iyileştirdim.']);
$db->prepare("INSERT INTO daily_records (internship_id, date, present) VALUES (?, ?, 1)")->execute([$internIds[2], $day]);

// Sorun bildirileri: Fatma anonim; Can kimliği belli
$db->prepare("INSERT INTO issues (internship_id, reported_by, category, description, anonymous, status) VALUES (?, ?, 'safety', ?, 1, 'new')")
   ->execute([$internIds[1], $studentIds[1], 'İş güvenliği ekipmanlarının eksik olduğu rapor edildi.']);
$db->prepare("INSERT INTO issues (internship_id, reported_by, category, description, anonymous, status) VALUES (?, ?, 'other_duty', ?, 0, 'new')")
   ->execute([$internIds[4], $studentIds[4], 'Görev dışı işlere yönlendirildim.']);

echo "[8/8] SSS ve kontrol listesi ekleniyor...\n";
$faqs = [
    ['Staj defterini ne zaman doldurmalıyım?', 'Her gün stajınızın sonunda, en geç 24 saat içinde doldurun.'],
    ['Hangi dokümanları yüklemeliyim?', 'Fotoğraf yalnızca isteğe bağlıdır; zorunlu değildir.'],
    ['Sorun bildirebilir miyim anonim olarak?', 'Evet. Sorun bildirirken "anonim gönder" kutusu işaretlenebilir.'],
    ['Değerlendirme formunu kim doldurur?', 'Stajınızı yürüten işletme yetkilisi, öğrenci bitirince onaylar.'],
];
$stmt = $db->prepare("INSERT INTO faq_items (question, answer, sort_order, active) VALUES (?, ?, ?, 1)");
foreach ($faqs as $i => $f) { $stmt->execute([$f[0], $f[1], $i]); }

$check = [
    ['Staj sözleşmesi imzalandı', 'Staj sözleşmesi imzalanmalı.'],
    ['Günlük yoklama yapıldı', 'Her gün "bugün stajdayım" girip not ekleyin.'],
    ['İş güvenliğine dikkat edin', 'Baret, eldiven ve uygun güvenlik ayakkabıları zorunludur.'],
    ['Anonim sorun bildirin', 'Sorun bildiriminizi anonim olarak da iletebilirsiniz.'],
];
$stmt = $db->prepare("INSERT INTO checklist_items (title, description, sort_order, active) VALUES (?, ?, ?, 1)");
foreach ($check as $i => $c) { $stmt->execute([$c[0], $c[1], $i]); }

echo "\n=== SEED TAMAM ===\n";
echo "Öğretmen: teacher@okul.edu / 123456\n";
echo "Öğrenciler: ali.veli@stu.edu vb. / 123456\n";
$ptoken = @file_get_contents(__DIR__ . '/bekleyen_isyeri_baglantisi.txt');
echo "İşletme onay linki (beklemede): " . $cfg['app']['base_url'] . "/company/" . $ptoken . "\n";
echo "Öğretmen link sayfası: " . $cfg['app']['base_url'] . "/teacher/company-link/" . $internIds[2] . "\n";


