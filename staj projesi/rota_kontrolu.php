<?php
/* rota_kontrolu.php - GET rotalarının HTTP kodlarını (varsayılan yönlendirme takibine
   bakmadan) doğrular. HEAD desteği eklendi; ama test GET ile daha güvenilir. */
$cases = [
    '/'                  => 302,
    '/login'             => 200,
    '/giris/ogrenci'     => 200,
    '/giris/ogretmen'    => 200,
    '/giris/isletme'     => 200,
    '/register'          => 200,
    '/faq'               => 200,
    '/checklist'         => 200,
    '/kvkk'              => 200,
    '/student'           => 302,
    '/teacher'           => 302,
        '/company/bogustoken'=> 404,
    '/assets/css/gorunum.css'=> 200,
];
$allOk = true;
$contentChecks = [
    '/login' => '/assets/css/gorunum.css?v=2',
    '/register' => 'id="registerForm"',
];
$base = rtrim(getenv('STAJ_BASE_URL') ?: 'http://127.0.0.1:8765', '/');
foreach ($cases as $p => $exp) {
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true,
                           CURLOPT_FOLLOWLOCATION => false,
                           CURLOPT_CONNECTTIMEOUT => 5]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $mark = ($code === $exp) ? 'OK ' : 'XX ';
    if ($mark === 'XX ') $allOk = false;
    echo $mark . " $p -> HTTP $code (beklenen $exp)\n";
    if (isset($contentChecks[$p])) {
        $contentOk = is_string($body) && strpos($body, $contentChecks[$p]) !== false;
        echo ($contentOk ? 'OK ' : 'XX ') . " $p içerik kontrolü\n";
        if (!$contentOk) $allOk = false;
    }
    curl_close($ch);
}
echo $allOk ? "SMOKE PASSED\n" : "SMOKE FAILED\n";
exit($allOk ? 0 : 1);
