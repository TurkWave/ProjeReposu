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
start "Staj Takip" /min "%PHP%" -S 127.0.0.1:8765 -t "%ROOT%public" "%ROOT%public\istek_yonlendirici.php"
timeout /t 2 >nul
curl -s -o nul -w "HTTP %%{http_code} - http://127.0.0.1:8765/\n" http://127.0.0.1:8765/
