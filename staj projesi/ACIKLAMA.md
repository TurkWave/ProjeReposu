# Staj Takip ve Destek

Stajyer öğrenci, koordinatör öğretmen ve işletme arasındaki süreci kolaylaştıran,
mobil öncelikli bir **PWA** (PHP, PDO/MySQL, oturum tabanlı kimlik). Yoklama, staj
defteri, onay akışı ve sorun bildirme akışlarını yönetir. Canlı sohbet/mesajlaşma YOK.

Arayüz lacivert ve sıcak altın tonlarıyla yenilenmiştir. Herhangi bir üniversite adı,
amblem veya logo kullanılmaz. Tüm metinler Türkçe karakterleri destekleyecek şekilde
UTF-8 kodlanır.

## Kurulum (Windows / XAMPP varsayımıyla)

1. **`app/ayarlar.php`** içindeki veritabanı kullanıcı adı ve parolasını kontrol edin:
   ```php
   'db'  => ['host'=>'127.0.0.1','name'=>'staj_app','user'=>'root','password'=>'','charset'=>'utf8mb4'],
   'app' => ['base_url'=>'','debug'=>true],
   ```
   `base_url` boşken uygulama adresini Apache isteğinden otomatik belirler.
2. XAMPP Control Panel'den **Apache** ve **MySQL** servislerini başlatın. Bu bilgisayarda
   Apache, `C:\xampp\apache\conf\extra\httpd-stajtakip.conf` ile proje klasörünün
   `public` dizinine yönlendirilmiş ve yalnızca bu bilgisayardan erişilecek şekilde ayarlanmıştır.
3. Tarayıcıda uygulamayı açın: **http://127.0.0.1:8080**
4. İlk kurulumda veritabanı yoksa **veritabanı + örnek veriler**:
   ```bat
   veritabaniyi_kur.bat
   ```
   Betik çalıştırmadan önce onay ister. `staj_app` veritabanındaki mevcut tüm
   kayıtları silip örnek verileri yeniden kurar.
5. Geliştirme sunucusu (isteğe bağlı alternatif): `yerel_sunucuyu_baslat.bat` ile
   `http://127.0.0.1:8765` adresinde çalıştırılabilir.
6. **PWA ikonları** (GD yok, saf PHP + zlib):
   ```bat
   php pwa_ikonlari_uret.php
   ```

## Örnek (seed) veriler

| Rol        | E-posta                | Şifre   |
|------------|------------------------|---------|
| Öğretmen   | teacher@okul.edu       | 123456  |
| Öğrenci    | ali.veli@stu.edu       | 123456  |
|            | mehmet.x@stu.edu (18-) | 123456  |
|            | (diğer 3 öğrenci)      | 123456  |

- Mehmet 18 yaş altı → `is_minor=1`, `parent_consent_given=1`.
- Örnek işletme onay bağlantısı `bekleyen_isyeri_baglantisi.txt` içindedir. Uygulama
  bağlantının başlangıç adresini çalıştığı sunucuya göre oluşturur.

## Akışlar / Rotalar

| Akış | URL | Kim? |
|------|-----|------|
| Giriş türünü seç | `/login` | herkese |
| Öğrenci girişi | `/giris/ogrenci` | öğrenci |
| Öğretmen girişi | `/giris/ogretmen` | öğretmen |
| İşyeri bağlantısı | `/giris/isletme` | token sahibi |
| Kayıt / çıkış | `/register` `/logout` | herkese |
| Öğrenci ana | `/student` | öğrenci |
| Günlük yoklama + defter | `/student/daily` | öğrenci |
| Defter geçmişi | `/student/daily/history` | öğrenci |
| Sorun bildir | `/student/sorunlar` | öğrenci |
| Sorun detay | `/student/issue/{id}` | öğrenci |
| Kontrol listesi | `/student/kontrol_listesi` | öğrenci |
| SSS | `/student/sss` | öğrenci |
| Staj bilgileri | `/student/staj_bilgileri` | öğrenci |
| Öğretmen paneli | `/teacher` | öğretmen |
| Öğrenci stajı (yorum+kayıt) | `/teacher/ogrenci_staji/{id}` | öğretmen |
| Yorum kaydet | `POST /teacher/comment` | öğretmen |
| Sorun listesi | `/teacher/sorunlar` | öğretmen |
| Sorun durum güncelle | `POST /teacher/issue/{id}/status` | öğretmen |
| İşletme linki göster | `/teacher/company-link/{id}` | öğretmen |
| İşletme onay | `/company/{token}` | işletme (token) |
| SSS (public) | `/faq` | herkese |
| Kontrol (public) | `/checklist` | herkese |
| KVKK | `/kvkk` | herkese |

## Dosya yapısı

```
staj projesi/
├── public/                 # Web kök dizini (document root)
│   ├── index.php           # Front controller + router
│   ├── istek_yonlendirici.php          # php -S için router
│   ├── manifest.json       # PWA manifest
│   ├── cevrimdisi_hizmeti.js               # Service worker (offline)
│   └── assets/
│       ├── css/gorunum.css
│       └── js/arayuz_etkilesimi.js       # install / csrf-logout / sw register
├── app/
│   ├── baslangic.php       # autoload + session + db
│   ├── ayarlar.php          # DB, app, session, upload, auth
│   ├── Veritabani.php        # PDO (ERRMODE_EXCEPTION, prepared)
│   ├── Kimlik.php            # session auth + role
│   ├── Yonlendirici.php          # {param} destekli minik router
│   ├── Denetleyici.php      # görünüm, yönlendirme ve CSRF kontrolü
│   ├── TemelModel.php           # temel CRUD model
│   ├── yardimcilar.php         # e(), csrf_*, redirect(), flash(), gun_adi()
│   ├── middleware/         # MustLogin, MustRole
│   ├── controllers/        # Auth, Student, Teacher, Company, Static
│   ├── models/             # User, Company, Internship, DailyRecord,
│   │                       #   Journal, CompanyConfirmation, Issue,
│   │                       #   FaqItem, ChecklistItem
│   └── views/              # layouts/{app,auth,company}, auth/*,
│                           #   student/*, teacher/*, company/*, static/*
├── sql/schema.sql          # şema (FK + UNIQUE constraint’ları)
├── ornek_verileri_yukle.php                # 1 öğretmen, 5 öğrenci, 2 işletme, 5 staj
├── pwa_ikonlari_uret.php               # GD’sız PWA ikon (192/512), zlib
├── veritabaniyi_kur.bat            # Reset DB + schema + seed
├── veritabaniyi_kontrol.bat           # DB kontrol sorguları
├── php_denetimi.bat                # tüm PHP dosyaları için php -l
├── yerel_sunucuyu_baslat.bat        # php -S dev sunucusunu başlatır
├── xampp_dogrulamasi.bat        # XAMPP Apache üzerinde smoke + akış testleri
├── islev_akisi_testi.php / .bat    # headless (cURL) uçtan uca test
└── ACIKLAMA.md
```

> Not: kayıtta `KVKK` aydınlatma metni zorunlu; 18 yaş altı için veli onayı
> (`consent_parent`) zorunludur. Anonim sorun bildiriminde `reported_by` FK
> tutunur; öğretmen UI’de kimliği görmez.

## Test / doğrulama

```bat
php_denetimi.bat
yerel_sunucuyu_baslat.bat
tum_dogrulamalari_calistir.bat
```

`tum_dogrulamalari_calistir.bat` geliştirme sunucusunu, `xampp_dogrulamasi.bat` ise Apache'yi test eder.
İkisi de PHP sözdizimini, ikon üretimini, 10 temel rotayı ve öğretmen/öğrenci/
işletme akışlarını doğrular. Mevcut `bekleyen_isyeri_baglantisi.txt` tek kullanımlık token'ı
daha önce tamamlanmışsa işletme gönderimini tekrar etmeden tamamlanmış onayı doğrular.
Test, veritabanını sıfırlamaz; sorun bildirimi testi geçici kayıt oluşturup siler.

`islev_akisi_testi.php` beklenen çıktı:
```
PASS öğretmen ve öğrenci giriş akışları
PASS sorun bildirimi POST akışı
PASS işletme onay sayfası / daha önce kullanılmış token kontrolü
PASS olmayan rota 404 döndürüyor
FUNCTIONAL PASSED
```

Doğrulama (veritabaniyi_kontrol.bat):
- Fonksiyon testi geçici bir sorun kaydı oluşturur ve uygulama üzerinden siler.
- `bekleyen_isyeri_baglantisi.txt` seed sırasında üretilen tek kullanımlık örnek işletme linkidir.

## Bilinen sınırlamalar / MVP dışı

- Geliştirme sunucusu `php -S` kullanır; canlı ortam için Apache/Nginx ve HTTPS gerekir.
- Fotoğraf yüklemesi `public/storage/uploads` klasörüne yazılır; klasörün PHP
  tarafından yazılabilir olması gerekir (2 MB, JPEG/PNG/WebP).
- `Model::insert/find/update/delete` CRUD’u vardır; iş akışlarının çoğu RLS
  koşulları içeren raw SQL ile çalışır.

