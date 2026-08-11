# Phase 0 — Bootstrap Proyek

Status: in progress — skeleton selesai; `composer.lock` belum dapat dibuat karena Composer/PHP belum tersedia pada environment ini.

## Tujuan

Membuat skeleton aplikasi Native PHP yang dapat dijalankan secara lokal melalui document root `public/`.

## Dependensi

Tidak ada.

## Task

- [x] Inisialisasi `composer.json` sesuai dependency TSD.
- [ ] Kunci dependency melalui `composer.lock`.
- [x] Buat struktur folder `app/`, `config/`, `content/`, `storage/`, `database/`, `assets/`, `views/`, `admin/`, dan `public/`.
- [x] Tambahkan `.env.example`; jangan commit secret.
- [x] Buat konfigurasi aplikasi, timezone, URL, database path, pagination, dan upload limit.
- [x] Buat `public/index.php` sebagai entry point.
- [x] Konfigurasi Apache rewrite ke `public/index.php`.
- [x] Pastikan `content/`, `database/`, dan `storage/` berada di luar document root.
- [x] Tambahkan response helper dan error handler production-safe.

## Deliverable

- Aplikasi menampilkan halaman health/check sederhana.
- `composer install` dan database initialization dapat dijalankan pada environment baru.

## Definition of Done

- [ ] Request ke public entry point berhasil.
- [ ] Request invalid tidak menampilkan stack trace atau credential.
- [ ] Private directory tidak dapat diakses melalui HTTP.
- [ ] Struktur dasar sesuai TSD.
