# Project.md

- **Usage:** on a small or personal project, fill in only the `CORE` section.
- As the project grows and the codebase becomes complex, open and fill in the relevant `EXTENDED` section.
- When a team, prod or real users come into play, open and fill in the `ADVANCED` section.
- **Do not delete** the sections you don't fill out. Leave them blank, because that shows when you will need them.

---

## 📌 Project Summary
Staj Takip ve Destek is a mobile-first web app that connects internship students, their coordinator teacher and the host company. Students log daily attendance and a journal, report problems and read the checklist and FAQ. Teachers review each student's internship, comment on it and manage reported issues. Companies confirm an internship through a single-use link, without an account. It is written in plain PHP on MariaDB/MySQL and ships as a PWA, with a Docker setup for local runs.

---

## 🟢 CORE (fill in every project)

## Project Description
- Project name: Staj Takip ve Destek (`name` in `app/ayarlar.php`: "Staj Takip")
- What it does (1-2 sentences): Tracks internships through daily attendance, a student journal, company confirmation and issue reports. Roles are student, teacher and company (token access). There is no live chat.
- Who it is for: Internship students, their coordinator teachers and the host companies (the UI is in Turkish).

## Tech Stack
- Backend: PHP 8.3 (`Dockerfile`: `php:8.3-apache`), custom MVC in `app/` with a hand-written router and manual autoloader, no Composer
- Frontend: server-rendered PHP views in `app/views/`, vanilla JS (`public/assets/js/arayuz_etkilesimi.js`), CSS (`public/assets/css/gorunum.css`), PWA (`public/manifest.json`, `public/cevrimdisi_hizmeti.js`)
- Database: MariaDB 10.11 in Docker (`docker-compose.yml`), schema in `sql/schema.sql`
- ORM: none. PDO with prepared statements (`app/Veritabani.php`) and a thin CRUD base class (`app/TemelModel.php`)

## Folder Structure
```txt
app/
├─ baslangic.php          # bootstrap: config, session, autoloader, DB init
├─ ayarlar.php            # config (reads DB_* env vars)
├─ Veritabani.php         # PDO setup
├─ Kimlik.php             # session auth and roles
├─ Yonlendirici.php       # router
├─ Denetleyici.php        # base controller (view, CSRF, error)
├─ TemelModel.php         # base model
├─ yardimcilar.php        # global helpers and Config
├─ controllers/           # Giris, Ogrenci, Ogretmen, Isyeri, GenelSayfa
├─ middleware/            # GirisGerekli, RolGerekli
├─ models/                # Kullanici, Isyeri, Staj, GunlukYoklama, StajDefteri,
│                         # IsyeriOnayi, Sorun, SssMaddesi, KontrolMaddesi
└─ views/                 # layouts, auth, student, teacher, company, static
public/
├─ index.php              # front controller with all routes
├─ istek_yonlendirici.php # router script for php -S
├─ manifest.json
├─ cevrimdisi_hizmeti.js  # service worker
└─ assets/                # css, js, icons
sql/
└─ schema.sql
ornek_verileri_yukle.php  # seed script (deletes existing rows)
pwa_ikonlari_uret.php     # PWA icon generator
rota_kontrolu.php         # route smoke check (cURL)
islev_akisi_testi.php     # end-to-end check (cURL)
Dockerfile
docker-compose.yml
```

## Installation

```bash
# Docker (host port 8081 by default, override with WEB_PORT)
docker compose up -d --build
docker compose exec web php ornek_verileri_yukle.php   # seed data, deletes all rows in staj_app

# Without Docker (XAMPP): schema, seed data, then dev server
mysql -u root < sql/schema.sql
php ornek_verileri_yukle.php
php -S 127.0.0.1:8765 -t public public/istek_yonlendirici.php

# Regenerate PWA icons (pure PHP, no GD)
php pwa_ikonlari_uret.php
```

## Environment Variables

```env
# read by app/ayarlar.php and docker-compose.yml
DB_HOST=
DB_NAME=
DB_USER=
DB_PASSWORD=
DB_ROOT_PASSWORD=
WEB_PORT=
# read by rota_kontrolu.php and islev_akisi_testi.php
STAJ_BASE_URL=
```

## Coding Rules

### Allowed

* Database access through PDO prepared statements (`app/Veritabani.php` sets `ATTR_EMULATE_PREPARES => false` and `ERRMODE_EXCEPTION`).
* Escape output with `e()` from `app/yardimcilar.php`.
* New classes need an entry in the `$files` map of the autoloader in `app/baslangic.php`.
* `declare(strict_types=1)` at the top of PHP files.

### Forbidden

* Publishing a container on host port 8080: reserved for the user's local LLM server.

---

## 🟡 EXTENDED (open as the project grows)

## Architectural Decisions

> Write a one-sentence reason next to each choice. You will need it in 6 months.

* Plain PHP MVC with a custom router and autoloader: `app/baslangic.php` states "composer yok", so the project has no dependency manager.
* Single front controller: `public/index.php` registers every route, and Apache uses `FallbackResource /index.php` (`Dockerfile`).
* Company access by single-use token instead of an account: `app/ayarlar.php` sets `company.token_bytes` 32 and `ttl_days` 14, and `/company/{token}` is public.
* Consent stored in the database: `sql/schema.sql` has a `consents` table and `users.is_minor` / `parent_consent_given` columns.

## Fine Tuning

* Realtime: none (no live chat)
* Cache: service worker for offline use (`public/cevrimdisi_hizmeti.js`)
* Queue:
* Container: Docker, `web` (PHP 8.3 + Apache) and `db` (MariaDB 10.11) services in `docker-compose.yml`
* Auth: session based. `app/Kimlik.php` uses `password_verify`, `password_hash` and `session_regenerate_id(true)`. Roles are enforced by `app/middleware/RolGerekli.php`. Company users use a token.
* Storage: local disk, `public/storage/uploads` (2 MB, JPEG/PNG/WebP, `upload` in `app/ayarlar.php`), mounted as the `uploads` volume

## API Contract

* Style: server-rendered pages with form posts. `Controller::json()` exists in `app/Denetleyici.php`, no REST API is defined in `public/index.php`.
* Versioning:
* Response format:

## Database / Migration Strategy

* Migration management: none. `sql/schema.sql` is loaded once by the MariaDB image on an empty `db_data` volume (`docker-compose.yml`).
* Naming convention: snake_case English table and column names (`users`, `daily_records`, `company_confirmations`, `password_hash`)

## State Management (Frontend)

* Global state tool: none
* Server state / cache tool: PHP session on the server

## Error Handling Strategy

* Global error handling approach: `Controller::error()` in `app/Denetleyici.php` prints an HTML error page and exits. `app/Veritabani.php` catches `PDOException` on connect and shows a 500 page, with the message only when `app.debug` is true. `RolGerekli` returns a 403 page.
* Custom exception structure: none

## Dependencies

* Dependencies: no Composer or package manager files. Runtime needs the `pdo_mysql` extension (`Dockerfile`). The test scripts need the PHP cURL extension.

---

## 🔴 ADVANCED (team / prod level)

## Debug & Observability

* Monitoring:
* Logging:
* Testing: two cURL scripts run against a live server, `rota_kontrolu.php` (routes) and `islev_akisi_testi.php` (login, issue report, company token, 404). No unit test framework.

## Security

* Rate limiting:
* CORS policy:
* Input validation: manual checks in controllers and models. CSRF token required on every POST (`Controller::checkCsrf()`, `csrf_check()`). Session cookie is `httponly` with `SameSite=Lax`.

## CI/CD

* Pipeline tool:
* Deploy trigger:

## Environments

* dev / staging / prod differences:

## Cloud

* Provider:
* Services:

## Versioning / Release

* Semantic versioning:
* Changelog management:

## Team Conventions

* Commit format:
* Branch naming:
* PR template / code review rules:

## Coding Standards

* Formatter:
* Linter:
* Import order:

---
