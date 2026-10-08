@echo off
setlocal
set "ROOT=%~dp0"
set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" (
    where php >nul 2>&1
    if errorlevel 1 (
        echo PHP bulunamadi. PATH'i veya XAMPP kurulumunu kontrol edin.
        exit /b 1
    )
    set "PHP=php"
)

echo === PHP lint ===
call "%ROOT%php_denetimi.bat" || exit /b 1

echo === PWA icons ===
"%PHP%" "%ROOT%pwa_ikonlari_uret.php" || exit /b 1

echo === Development server ===
curl -fsS -o nul http://127.0.0.1:8765/login
if errorlevel 1 (
    start "Staj Takip test sunucusu" /min "%PHP%" -S 127.0.0.1:8765 -t "%ROOT%public" "%ROOT%public\istek_yonlendirici.php"
    timeout /t 2 >nul
)

echo === Smoke ===
"%PHP%" "%ROOT%rota_kontrolu.php" || exit /b 1

echo === Functional flow ===
"%PHP%" "%ROOT%islev_akisi_testi.php" || exit /b 1

echo === ALL VERIFY PASSED ===
exit /b 0
