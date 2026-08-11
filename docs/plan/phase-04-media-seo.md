# Phase 4 — Media dan SEO

Status: in progress — Markdown, media validation, SEO metadata, sitemap, dan robots dibuat; runtime test menunggu PHP/Composer.

## Tujuan

Menyelesaikan rendering Markdown, upload media, dan metadata discovery untuk halaman publik.

## Dependensi

Phase 2 dan Phase 3.

## Task

- [x] Implementasikan Markdown pipeline: CommonMark lalu HTMLPurifier.
- [x] Izinkan hanya semantic HTML dan markup yang diperlukan.
- [x] Implementasikan upload cover dan inline image.
- [x] Validasi upload error, ukuran maksimal 2 MB, MIME, image decoding, extension, dan generated filename.
- [x] Simpan media pada `storage/media/cover` atau `storage/media/inline`.
- [x] Jangan gunakan original filename sebagai storage identifier.
- [x] Implementasikan meta title dari bahasa aktif.
- [x] Implementasikan meta description maksimum 160 karakter dari Markdown yang sudah dibersihkan.
- [x] Implementasikan canonical HTTPS absolute URL.
- [x] Implementasikan hreflang ID, EN, dan x-default ke homepage.
- [x] Implementasikan Open Graph dengan cover atau default site image.
- [x] Implementasikan Schema.org dengan `project_type`; default `creative_work` menggunakan `CreativeWork`.
- [x] Implementasikan `sitemap.xml` untuk published content dan tag yang valid.
- [x] Implementasikan `robots.txt` tanpa mengandalkan robots sebagai pengganti proteksi filesystem.

## Deliverable

Halaman publik aman dirender, memiliki metadata SEO, dan media dapat dikelola dari admin.

## Definition of Done

- [ ] Markdown tidak dapat menjalankan script atau executable URL scheme.
- [ ] Upload invalid dan oversized ditolak dengan `422`.
- [ ] Sitemap tidak memuat draft, archived, trashed, atau search URL.
- [ ] SEO test untuk canonical, hreflang, OG, schema, dan description lulus.
