@echo off
setlocal
set "ROOT=%~dp0"
set "MYSQL=C:\xampp\mysql\bin\mysql.exe"
set "PHP=C:\xampp\php\php.exe"
if not exist "%MYSQL%" (
    where mysql >nul 2>&1
    if errorlevel 1 (
        echo MySQL istemcisi bulunamadi. XAMPP yolunu veya PATH'i kontrol edin.
        exit /b 1
    )
    set "MYSQL=mysql"
)
if not exist "%PHP%" (
    where php >nul 2>&1
    if errorlevel 1 (
        echo PHP bulunamadi. XAMPP yolunu veya PATH'i kontrol edin.
        exit /b 1
    )
    set "PHP=php"
)

echo UYARI: staj_app veritabanindaki tum kayitlar silinip yeniden olusturulacak.
choice /c EH /n /m "Devam etmek istiyor musunuz? (E/H) "
if errorlevel 2 exit /b 0

"%MYSQL%" -u root -e "DROP DATABASE IF EXISTS staj_app;" || exit /b 1
"%MYSQL%" -u root < "%ROOT%sql\schema.sql" || exit /b 1
"%PHP%" "%ROOT%ornek_verileri_yukle.php" || exit /b 1
echo Veritabani ve ornek veriler hazir.
exit /b 0
