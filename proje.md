# Proje.md

- **Kullanım:** Küçük veya kişisel projelerde yalnızca `CORE` bölümünü doldurun.
- Proje büyüdükçe ve kod tabanı karmaşıklaştıkça ilgili `EXTENDED` bölümünü açıp doldurun.
- Ekip, canlı ortam veya gerçek kullanıcılar devreye girdiğinde `ADVANCED` bölümünü açıp doldurun.
- Doldurmadığınız bölümleri **silmeyin**. Boş bırakın, çünkü ne zaman gerekeceğini bu gösterir.

---

## 📌 Proje Özeti
Staj Takip ve Destek, stajyer öğrenciler, koordinatör öğretmen ve staj yapılan işletme arasında bağlantı kuran, mobil öncelikli bir web uygulamasıdır. Öğrenciler günlük yoklama ve staj defteri girer, sorun bildirir, kontrol listesi ile SSS sayfasını okur. Öğretmenler her öğrencinin stajını inceler, yorum yazar ve bildirilen sorunları yönetir. İşletmeler hesap açmadan, tek kullanımlık bir bağlantıyla stajı onaylar. Uygulama sade PHP ile MariaDB/MySQL üzerinde yazılmıştır, PWA olarak sunulur ve yerel çalıştırma için Docker kurulumu içerir.

---

## 🟢 CORE (her projede doldurun)

## Proje Tanımı
- Proje adı: Staj Takip ve Destek (`app/ayarlar.php` içindeki `name`: "Staj Takip")
- Ne yapar (1-2 cümle): Günlük yoklama, öğrenci defteri, işletme onayı ve sorun bildirimleriyle stajları takip eder. Roller öğrenci, öğretmen ve işletme (token ile erişim). Canlı sohbet yoktur.
- Kimler için: Staj yapan öğrenciler, koordinatör öğretmenleri ve staj yapılan işletmeler (arayüz Türkçedir).

## Teknoloji Yığını
- Backend: PHP 8.3 (`Dockerfile`: `php:8.3-apache`), `app/` içinde elle yazılmış MVC, özel yönlendirici ve manuel autoloader, Composer yok
- Frontend: `app/views/` içinde sunucu tarafında üretilen PHP görünümleri, saf JS (`public/assets/js/arayuz_etkilesimi.js`), CSS (`public/assets/css/gorunum.css`), PWA (`public/manifest.json`, `public/cevrimdisi_hizmeti.js`)
- Veritabanı: Docker'da MariaDB 10.11 (`docker-compose.yml`), şema `sql/schema.sql` içinde
- ORM: yok. Hazır ifadelerle PDO (`app/Veritabani.php`) ve ince bir CRUD taban sınıfı (`app/TemelModel.php`)

## Klasör Yapısı
```txt
app/
├─ baslangic.php          # açılış: ayarlar, oturum, autoloader, veritabanı
├─ ayarlar.php            # ayarlar (DB_* ortam değişkenlerini okur)
├─ Veritabani.php         # PDO kurulumu
├─ Kimlik.php             # oturum tabanlı kimlik ve roller
├─ Yonlendirici.php       # yönlendirici
├─ Denetleyici.php        # temel denetleyici (görünüm, CSRF, hata)
├─ TemelModel.php         # temel model
├─ yardimcilar.php        # global yardımcılar ve Config
├─ controllers/           # Giris, Ogrenci, Ogretmen, Isyeri, GenelSayfa
├─ middleware/            # GirisGerekli, RolGerekli
├─ models/                # Kullanici, Isyeri, Staj, GunlukYoklama, StajDefteri,
│                         # IsyeriOnayi, Sorun, SssMaddesi, KontrolMaddesi
└─ views/                 # layouts, auth, student, teacher, company, static
public/
├─ index.php              # tüm rotaların tanımlı olduğu front controller
├─ istek_yonlendirici.php # php -S için yönlendirici betiği
├─ manifest.json
├─ cevrimdisi_hizmeti.js  # service worker
└─ assets/                # css, js, ikonlar
sql/
└─ schema.sql
ornek_verileri_yukle.php  # örnek veri betiği (mevcut satırları siler)
pwa_ikonlari_uret.php     # PWA ikon üretici
rota_kontrolu.php         # rota duman testi (cURL)
islev_akisi_testi.php     # uçtan uca kontrol (cURL)
Dockerfile
docker-compose.yml
```

## Kurulum

```bash
# Docker (varsayılan host portu 8081, WEB_PORT ile değiştirilir)
docker compose up -d --build
docker compose exec web php ornek_verileri_yukle.php   # örnek veri, staj_app içindeki tüm satırları siler

# Docker olmadan (XAMPP): şema, örnek veri, geliştirme sunucusu
mysql -u root < sql/schema.sql
php ornek_verileri_yukle.php
php -S 127.0.0.1:8765 -t public public/istek_yonlendirici.php

# PWA ikonlarını yeniden üret (saf PHP, GD gerekmez)
php pwa_ikonlari_uret.php
```

## Ortam Değişkenleri

```env
# app/ayarlar.php ve docker-compose.yml tarafından okunur
DB_HOST=
DB_NAME=
DB_USER=
DB_PASSWORD=
DB_ROOT_PASSWORD=
WEB_PORT=
# rota_kontrolu.php ve islev_akisi_testi.php tarafından okunur
STAJ_BASE_URL=
```

## Kodlama Kuralları

### İzin Verilenler

* Veritabanı erişimi PDO hazır ifadeleriyle yapılır (`app/Veritabani.php`, `ATTR_EMULATE_PREPARES => false` ve `ERRMODE_EXCEPTION` ayarlar).
* Çıktı, `app/yardimcilar.php` içindeki `e()` ile kaçışlanır.
* Yeni sınıflar için `app/baslangic.php` içindeki autoloader'ın `$files` haritasına kayıt eklenir.
* PHP dosyalarının başında `declare(strict_types=1)` kullanılır.

### Yasaklar

* Konteyneri host 8080 portunda yayınlamak: bu port kullanıcının yerel LLM sunucusuna ayrılmıştır.

---

## 🟡 EXTENDED (proje büyüdükçe açın)

## Mimari Kararlar

> Her tercihin yanına tek cümlelik bir gerekçe yazın. 6 ay sonra ihtiyacınız olacak.

* Özel yönlendirici ve autoloader ile sade PHP MVC: `app/baslangic.php` "composer yok" diyor, projede bağımlılık yöneticisi bulunmuyor.
* Tek front controller: `public/index.php` tüm rotaları tanımlar, Apache `FallbackResource /index.php` kullanır (`Dockerfile`).
* Hesap yerine tek kullanımlık token ile işletme erişimi: `app/ayarlar.php` içinde `company.token_bytes` 32 ve `ttl_days` 14, `/company/{token}` herkese açıktır.
* Rıza kaydı veritabanında tutulur: `sql/schema.sql` içinde `consents` tablosu ve `users.is_minor` / `parent_consent_given` sütunları vardır.

## İnce Ayarlar

* Gerçek zamanlı: yok (canlı sohbet yok)
* Önbellek: çevrimdışı kullanım için service worker (`public/cevrimdisi_hizmeti.js`)
* Kuyruk:
* Konteyner: Docker, `docker-compose.yml` içinde `web` (PHP 8.3 + Apache) ve `db` (MariaDB 10.11) servisleri
* Kimlik doğrulama: oturum tabanlı. `app/Kimlik.php` `password_verify`, `password_hash` ve `session_regenerate_id(true)` kullanır. Roller `app/middleware/RolGerekli.php` ile denetlenir. İşletme kullanıcıları token kullanır.
* Depolama: yerel disk, `public/storage/uploads` (2 MB, JPEG/PNG/WebP, `app/ayarlar.php` içindeki `upload`), `uploads` volume'u olarak bağlanır

## API Sözleşmesi

* Stil: formlarla sunucu tarafında üretilen sayfalar. `app/Denetleyici.php` içinde `Controller::json()` var, `public/index.php` içinde REST API tanımlı değil.
* Sürümleme:
* Yanıt biçimi:

## Veritabanı / Migrasyon Stratejisi

* Migrasyon yönetimi: yok. `sql/schema.sql`, MariaDB imajı tarafından boş `db_data` volume'unda bir kez yüklenir (`docker-compose.yml`).
* İsimlendirme kuralı: snake_case İngilizce tablo ve sütun adları (`users`, `daily_records`, `company_confirmations`, `password_hash`)

## Durum Yönetimi (Frontend)

* Global durum aracı: yok
* Sunucu durumu / önbellek aracı: sunucuda PHP oturumu

## Hata Yönetimi Stratejisi

* Global hata yönetimi yaklaşımı: `app/Denetleyici.php` içindeki `Controller::error()` HTML hata sayfası yazar ve çıkar. `app/Veritabani.php` bağlantıda `PDOException` yakalar ve 500 sayfası gösterir, mesajı yalnızca `app.debug` true iken ekler. `RolGerekli` 403 sayfası döndürür.
* Özel istisna yapısı: yok

## Bağımlılıklar

* Bağımlılıklar: Composer veya paket yöneticisi dosyası yok. Çalışma zamanında `pdo_mysql` eklentisi gerekir (`Dockerfile`). Test betikleri PHP cURL eklentisini ister.

---

## 🔴 ADVANCED (ekip / canlı ortam seviyesi)

## Hata Ayıklama ve Gözlemlenebilirlik

* İzleme:
* Loglama:
* Test: canlı sunucuya karşı çalışan iki cURL betiği, `rota_kontrolu.php` (rotalar) ve `islev_akisi_testi.php` (giriş, sorun bildirimi, işletme token'ı, 404). Birim test çatısı yok.

## Güvenlik

* İstek sınırlama:
* CORS politikası:
* Girdi doğrulama: denetleyicilerde ve modellerde elle yapılan kontroller. Her POST için CSRF token zorunludur (`Controller::checkCsrf()`, `csrf_check()`). Oturum çerezi `httponly` ve `SameSite=Lax` ayarlıdır.

## CI/CD

* Pipeline aracı:
* Dağıtım tetikleyicisi:

## Ortamlar

* dev / staging / prod farkları:

## Bulut

* Sağlayıcı:
* Servisler:

## Sürümleme / Yayın

* Anlamsal sürümleme:
* Değişiklik günlüğü yönetimi:

## Ekip Kuralları

* Commit biçimi:
* Dal isimlendirme:
* PR şablonu / kod inceleme kuralları:

## Kodlama Standartları

* Biçimlendirici:
* Linter:
* Import sırası:

---
