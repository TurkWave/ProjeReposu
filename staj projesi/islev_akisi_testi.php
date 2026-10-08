<?php
/**
 * islev_akisi_testi.php - Headless functional test (cURL ext).
 * php islev_akisi_testi.php
 */
if (!extension_loaded('curl')) { fwrite(STDERR, "Need PHP curl ext.\n"); exit(1); }
$BASE = rtrim(getenv('STAJ_BASE_URL') ?: 'http://127.0.0.1:8765', '/');
$failures = [];

function check(bool $condition, string $label): void {
    global $failures;
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . "\n";
    if (!$condition) $failures[] = $label;
}

function c($opts, $cookieFile) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER         => false,
    ]);
    curl_setopt_array($ch, $opts);
    $out = curl_exec($ch);
    if ($out === false) { fwrite(STDERR, "curl err: ".curl_error($ch)."\n"); }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $out ?: ''];
}

function newCookieJar(): string {
    $f = tempnam(sys_get_temp_dir(), 'staj_');
    return $f;
}

function getCsrf(string $html): string {
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m)) return $m[1];
    return '';
}

function login(string $email, string $pwd, string $role): array {
    $jar = newCookieJar();
    $loginPath = $role === 'teacher' ? '/giris/ogretmen' : '/giris/ogrenci';
    // 1) GET form -> establish session, capture csrf
    c([CURLOPT_URL => $GLOBALS['BASE'].$loginPath], $jar);
    [$_, $body] = c([CURLOPT_URL => $GLOBALS['BASE'].$loginPath], $jar);
    $token = getCsrf($body);
    // 2) POST login (follows redirect to dashboard)
    [$code, $postBody] = c([
        CURLOPT_URL        => $GLOBALS['BASE'].$loginPath,
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => http_build_query(['csrf_token'=>$token,'email'=>$email,'password'=>$pwd]),
    ], $jar);
    return [$code, $postBody, $jar];
}

echo "=== [A] OGRETMEN giris + dashboard ===\n";
[$code, $html, $jarT] = login('teacher@okul.edu', '123456', 'teacher');
echo "login POST -> HTTP $code\n";
// dashboard after redirect (followed)
check($code === 200 && strpos($html, 'Toplam Öğrenci') !== false, 'öğretmen girişi ve özet paneli');
check(strpos($html, 'Ali Veli') !== false, 'öğrenci listesi görünür');
[$dCode, $dash] = c([CURLOPT_URL => $GLOBALS['BASE'].'/teacher'], $jarT);
echo "/teacher -> HTTP $dCode\n";
check($dCode === 200 && strpos($dash, 'Yoklama girişi beklenenler') !== false, 'öğretmen panel rotası');
$wrongRole = login('ali.veli@stu.edu', '123456', 'teacher');
check(strpos($wrongRole[1], 'bu hesap seçilen giriş türüne ait değil') !== false, 'öğrenci hesabı öğretmen girişinden reddediliyor');

echo "\n=== [B] OGRENCI giris + gunluk form ===\n";
[$sCode, $shtml, $jarS] = login('ali.veli@stu.edu', '123456', 'student');
echo "student login -> HTTP $sCode\n";
check($sCode === 200 && strpos($shtml, 'Merhaba, Ali Veli') !== false, 'öğrenci girişi ve paneli');

echo "\n=== [C] Sorun bildirme (student) ===\n";
[$issCode, $issHtml] = c([CURLOPT_URL => $GLOBALS['BASE'].'/student/sorunlar'], $jarS);
$token = getCsrf($issHtml);
// need fresh form again for csrf after a GET
[$issCode2, $issHtml2] = c([CURLOPT_URL => $GLOBALS['BASE'].'/student/sorunlar'], $jarS);
$token = getCsrf($issHtml2);
[$postCode, $postHtml] = c([
    CURLOPT_URL        => $GLOBALS['BASE'].'/student/sorunlar',
    CURLOPT_POST       => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token'=>$token, 'category'=>'safety','description'=>'Test sorunu - is güvenligi.','anonymous'=>'1'
    ]),
], $jarS);
echo "issue POST -> HTTP $postCode\n";
check($postCode === 200 && strpos($postHtml, 'Sorun bildiriniz gönderildi') !== false, 'sorun bildirimi POST akışı');
$testIssueId = null;
if (preg_match_all('/href="\/student\/issue\/(\d+)"/', $postHtml, $issueLinks)) {
    foreach (array_unique($issueLinks[1]) as $candidateId) {
        [$detailCode, $detailHtml] = c([CURLOPT_URL => $GLOBALS['BASE'].'/student/issue/'.$candidateId], $jarS);
        if ($detailCode === 200 && strpos($detailHtml, 'Test sorunu - is güvenligi.') !== false) {
            $testIssueId = (int)$candidateId;
            $deleteToken = getCsrf($detailHtml);
            [$deleteCode, $deleteHtml] = c([
                CURLOPT_URL => $GLOBALS['BASE'].'/student/issue/'.$testIssueId,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['csrf_token'=>$deleteToken,'_action'=>'delete']),
            ], $jarS);
            check($deleteCode === 200 && strpos($deleteHtml, 'Bildirim silindi') !== false, 'test sorun kaydının temizlenmesi');
            break;
        }
    }
}
if ($testIssueId === null) check(false, 'test sorun kaydının temizlenmesi için kayıt bulunamadı');

echo "\n=== [D] ISLETME onay linki ===\n";
$tok = trim((string)@file_get_contents(__DIR__ . '/bekleyen_isyeri_baglantisi.txt'));
echo "pending token: " . substr($tok,0,16) . "...\n";
$cJar = newCookieJar();
[$cCode, $cBody] = c([CURLOPT_URL => $GLOBALS['BASE'].'/company/'.$tok], $cJar);
echo "company GET -> HTTP $cCode\n";
check($cCode === 200 && (strpos($cBody, 'Değerlendirme') !== false || strpos($cBody, 'Onay Tamamlandı') !== false), 'işletme onay linki erişimi');

if (strpos($cBody, 'Onay Tamamlandı') !== false) {
    echo "Token daha önce kullanıldığı için tek kullanımlık onay gönderimi atlandı.\n";
} elseif (strpos($cBody, 'Değerlendirme') !== false) {

// submit company evaluation (SAME jar to keep session+csrf)
$t2 = getCsrf($cBody);
echo "extracted csrf token len=".strlen($t2)."\n";
[$ccPost, $ccBody] = c([
    CURLOPT_URL        => $GLOBALS['BASE'].'/company/'.$tok,
    CURLOPT_POST       => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token'=>$t2,'q1'=>5,'q2'=>4,'q3'=>5,'q4'=>4,'feedback'=>'Iyi stajydi.'
    ]),
], $cJar);
echo "company POST -> HTTP $ccPost\n";
check($ccPost === 200 && strpos($ccBody, 'Onay Tamamlandı') !== false, 'işletme değerlendirmesi ve onay gönderimi');
}

echo "\n=== [E] 404 ===\n";
[$eCode, $_] = c([CURLOPT_URL => $GLOBALS['BASE'].'/yok-sayfa'], newCookieJar());
echo "404 -> HTTP $eCode (404 expected)\n";
check($eCode === 404, 'olmayan rota 404 döndürüyor');

echo empty($failures) ? "\nFUNCTIONAL PASSED\n" : "\nFUNCTIONAL FAILED: " . implode(', ', $failures) . "\n";
exit(empty($failures) ? 0 : 1);
