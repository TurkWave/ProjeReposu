@echo off
setlocal
set "ROOT=%~dp0"
set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" (
    where php >nul 2>&1
    if errorlevel 1 (
        echo PHP bulunamadi. XAMPP kurulumunu veya PATH'i kontrol edin.
        exit /b 1
    )
    set "PHP=php"
)
curl -fsS -o nul http://127.0.0.1:8080/login >nul 2>&1
if errorlevel 1 (
    echo XAMPP Apache yanit vermiyor. XAMPP Control Panel'den Apache'yi baslatin.
    exit /b 1
)
set "STAJ_BASE_URL=http://127.0.0.1:8080"
echo === XAMPP Apache/MySQL smoke ===
"%PHP%" "%ROOT%rota_kontrolu.php" || exit /b 1
echo === XAMPP Apache/MySQL functional ===
"%PHP%" "%ROOT%islev_akisi_testi.php" || exit /b 1
echo === XAMPP VERIFY PASSED ===
exit /b 0
