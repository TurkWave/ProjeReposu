<?php
// ============================================================
// pwa_ikonlari_uret.php - PWA ikonlarını (192/512) üretir (GD gerekmez).
// PHP'de GD/Imagick yoksa da zlib varsayan PNG kodlayıcı.
// php pwa_ikonlari_uret.php
// ============================================================
declare(strict_types=1);

$outDir = __DIR__ . '/public/assets/icons';
if (!is_dir($outDir)) { mkdir($outDir, 0755, true); }

function pngChunk($fp, string $type, string $data): void {
    fwrite($fp, pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data)));
}

// 5x7 serif küçük font
$font = [
    'S' => [
        '11111','10001','10000','01110','00001','10001','11110',
    ],
    'T' => [
        '11111','00100','00100','00100','00100','00100','00100',
    ],
];

function makeIcon(int $size, string $path): void {
    global $font;
    $blue  = [20, 43, 74];
    $white = [247, 239, 217];

    // piksel haritası (blue dolgu)
    $pix = [];
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $pix[$y][$x] = $blue;
        }
    }
    // beyaz 2px kenarlık
    for ($x = 0; $x < $size; $x++) { $pix[0][$x] = $pix[1][$x] = $white; $pix[$size-1][$x] = $pix[$size-2][$x] = $white; }
    for ($y = 0; $y < $size; $y++) { $pix[$y][0] = $pix[$y][1] = $white; $pix[$y][$size-1] = $pix[$y][$size-2] = $white; }

    // "ST" çiz (merkeze, ölçeklendirilmiş)
    $letterW = 5;
    $letterH = 7;
    $gap = 2;
    $totalW = $letterW * 2 + $gap; // 5+2+5=12
    $scale = (int) floor(($size * 0.5) / $totalW);
    $drawW = $totalW * $scale;
    $drawH = $letterH * $scale;
    $startX = (int) floor(($size - $drawW) / 2);
    $startY = (int) floor(($size - $drawH) / 2);

    $cursorX = $startX;
    for ($ch = 0; $ch < 2; $ch++) {
        $letter = ($ch == 0) ? 'S' : 'T';
        $bitmap = $font[$letter];
        for ($ry = 0; $ry < $letterH; $ry++) {
            for ($rx = 0; $rx < $letterW; $rx++) {
                if ($bitmap[$ry][$rx] === '1') {
                    for ($dy = 0; $dy < $scale; $dy++) {
                        for ($dx = 0; $dx < $scale; $dx++) {
                            $yy = $startY + $ry * $scale + $dy;
                            $xx = $cursorX + $rx * $scale + $dx;
                            if ($yy >= 0 && $yy < $size && $xx >= 0 && $xx < $size) {
                                $pix[$yy][$xx] = $white;
                            }
                        }
                    }
                }
            }
        }
        if ($ch == 0) {
            $cursorX += ($letterW + $gap) * $scale;
        }
    }

    // tarama satırları + sıkıştırma
    $raw = '';
    for ($y = 0; $y < $size; $y++) {
        $raw .= "\x00"; // filter: none
        for ($x = 0; $x < $size; $x++) {
            $p = $pix[$y][$x];
            $raw .= chr($p[0]) . chr($p[1]) . chr($p[2]);
        }
    }
    $idat = gzcompress($raw);

    $fp = fopen($path, 'wb');
    fwrite($fp, "\x89PNG\r\n\x1a\n");
    pngChunk($fp, 'IHDR', pack('NNCCCCC', $size, $size, 8, 2, 0, 0, 0));
    pngChunk($fp, 'IDAT', $idat);
    pngChunk($fp, 'IEND', '');
    fclose($fp);
}

foreach ([192, 512] as $s) {
    $p = $outDir . "/icon-{$s}.png";
    makeIcon($s, $p);
    echo "OK: {$p}\n";
}
echo "İkonlar hazır.\n";


