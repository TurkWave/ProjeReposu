# Staj Takip ve Destek

Stajyer öğrenci, koordinatör öğretmen ve işletme arasındaki süreci kolaylaştıran,
mobil öncelikli bir **PWA** (PHP, PDO/MySQL, oturum tabanlı kimlik). Yoklama, staj
defteri, onay akışı ve sorun bildirme akışlarını yönetir. Canlı sohbet/mesajlaşma YOK.

Arayüz lacivert ve sıcak altın tonlarıyla yenilenmiştir. Herhangi bir üniversite adı,
amblem veya logo kullanılmaz. Tüm metinler Türkçe karakterleri destekleyecek şekilde
UTF-8 kodlanır.

## Setup with Docker (recommended)

Requires Docker with Compose. The stack is a PHP 8.3 + Apache `web` container and a
MariaDB 10.11 `db` container.

```sh
docker compose up -d --build
docker compose exec web php ornek_verileri_yukle.php
```

1. The app is served at **http://127.0.0.1:8081**. Change the host port with the
   `WEB_PORT` environment variable (host port 8080 is not used by this project).
2. `sql/schema.sql` is loaded automatically the first time the `db` container starts
   with an empty `db_data` volume. It is not re-applied on later starts.
3. `ornek_verileri_yukle.php` loads the sample data. **It deletes all existing rows**
   in `staj_app` before inserting, so run it on a fresh database only.
4. Database settings come from the `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASSWORD`
   environment variables read by `app/ayarlar.php`. `docker-compose.yml` sets
   development defaults (override `DB_USER`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, e.g. via
   a `.env` file). `DB_NAME` must stay `staj_app` because `sql/schema.sql` hardcodes it.
5. Data lives in the `db_data` (database) and `uploads` (uploaded photos) volumes.
   `docker compose down` keeps them; `docker compose down -v` deletes them.

## Setup without Docker (XAMPP)

1. Without the environment variables above, `app/ayarlar.php` falls back to
   `127.0.0.1`, database `staj_app`, user `root`, empty password. Adjust the environment
   or the file if your MySQL differs. An empty `base_url` makes the app derive its
   address from the request.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel and point Apache at the
   `public` directory of the project.
3. Create the database and sample data (destructive, drops `staj_app`):
   ```sh
   mysql -u root -e "DROP DATABASE IF EXISTS staj_app;"
   mysql -u root < sql/schema.sql
   php ornek_verileri_yukle.php
   ```
4. Optional development server: `php -S 127.0.0.1:8765 -t public public/istek_yonlendirici.php`

## PWA icons

No GD needed (pure PHP + zlib):
```sh
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
├── rota_kontrolu.php               # route smoke check (cURL)
├── islev_akisi_testi.php           # headless (cURL) end-to-end test
├── Dockerfile                      # PHP 8.3 + Apache image
├── docker-compose.yml              # web + MariaDB stack
└── .dockerignore
```

> Not: kayıtta `KVKK` aydınlatma metni zorunlu; 18 yaş altı için veli onayı
> (`consent_parent`) zorunludur. Anonim sorun bildiriminde `reported_by` FK
> tutunur; öğretmen UI’de kimliği görmez.

## Test / doğrulama

`rota_kontrolu.php` and `islev_akisi_testi.php` run from the host with PHP (cURL
extension) and read the target address from `STAJ_BASE_URL` (default
`http://127.0.0.1:8765`). Against the Docker stack:

```sh
STAJ_BASE_URL=http://127.0.0.1:8081 php rota_kontrolu.php
STAJ_BASE_URL=http://127.0.0.1:8081 php islev_akisi_testi.php
```

`islev_akisi_testi.php` reads the company link token from
`bekleyen_isyeri_baglantisi.txt` in the project root. The seed script writes that file
inside the container, so copy it out first:
`docker compose cp web:/var/www/html/bekleyen_isyeri_baglantisi.txt .`

The tests do not reset the database; the issue-report test creates a temporary record
and deletes it. Syntax check for all PHP files: `php -l <file>` per file.

`islev_akisi_testi.php` beklenen çıktı:
```
PASS öğretmen ve öğrenci giriş akışları
PASS sorun bildirimi POST akışı
PASS işletme onay sayfası / daha önce kullanılmış token kontrolü
PASS olmayan rota 404 döndürüyor
FUNCTIONAL PASSED
```

`bekleyen_isyeri_baglantisi.txt` is the single-use sample company link produced by the seed script.

## Bilinen sınırlamalar / MVP dışı

- Geliştirme sunucusu `php -S` kullanır; canlı ortam için Apache/Nginx ve HTTPS gerekir.
- Fotoğraf yüklemesi `public/storage/uploads` klasörüne yazılır; klasörün PHP
  tarafından yazılabilir olması gerekir (2 MB, JPEG/PNG/WebP).
- `Model::insert/find/update/delete` CRUD’u vardır; iş akışlarının çoğu RLS
  koşulları içeren raw SQL ile çalışır.

