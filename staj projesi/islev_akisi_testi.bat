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
"%PHP%" "%ROOT%islev_akisi_testi.php"
exit /b %errorlevel%




