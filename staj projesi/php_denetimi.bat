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
for /r "%ROOT%" %%F in (*.php) do (
    "%PHP%" -l "%%F" || exit /b 1
)
echo LINT PASSED
exit /b 0
