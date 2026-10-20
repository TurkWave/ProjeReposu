# Staj Takip ve Destek

A mobile-first PWA that connects internship students, their coordinator teacher and the host company. Built with PHP, PDO and MariaDB/MySQL, with session-based authentication.

## Features

- Daily attendance and internship journal for students
- Teacher dashboard with per-student internship view and comments
- Company confirmation through a single-use link, no account needed
- Issue reporting with status tracking
- Checklist, FAQ and KVKK pages
- Installable and usable offline (service worker)

There is no live chat or messaging.

## Documentation

Setup, stack, folder structure, environment variables and project conventions:

- English: [project.md](project.md)
- Türkçe: [proje.md](proje.md)


## Sample data

`ornek_verileri_yukle.php` loads one teacher, five students, two companies and five internships. All sample accounts use the password `123456`.

| Role    | Email                                  |
|---------|----------------------------------------|
| Teacher | teacher@okul.edu                       |
| Student | ali.veli@stu.edu                       |
| Student | mehmet.x@stu.edu (under 18)            |
| Student | three more students                    |

Mehmet is under 18, so his record has `is_minor=1` and `parent_consent_given=1`.

## Design notes

No university name, emblem or logo is used anywhere in the interface. All text is UTF-8 and supports Turkish characters.
