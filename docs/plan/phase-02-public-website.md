# Phase 2 — Public Website

Status: in progress — public routes, controllers, repositories, search, and views dibuat; runtime test menunggu PHP/Composer.

## Tujuan

Menyediakan seluruh pengalaman baca publik berdasarkan status `published`.

## Dependensi

Phase 1.

## Task

- [x] Tambahkan router untuk `/id` dan `/en`.
- [x] Tambahkan route `/id/content/{slug}` dan `/en/content/{slug}`.
- [x] Resolve language hanya dari URL, bukan session.
- [x] Buat homepage dengan `published_at DESC, id DESC`.
- [x] Implementasikan pagination default 5 item/page dan batas maksimal 50.
- [x] Implementasikan empty page tanpa server error.
- [x] Render detail content dari Markdown bahasa aktif.
- [x] Kembalikan `404` untuk draft, archived, trashed, atau file bahasa yang hilang.
- [x] Tambahkan `/id/tag/{tag}` dan `/en/tag/{tag}` dengan pagination.
- [x] Tambahkan query `q`, `tag`, dan `page` pada listing.
- [x] Implementasikan search title ID, title EN, dan tags melalui FTS5.
- [x] Pastikan search dan tag page hanya mengembalikan `published`.
- [x] Pertahankan query parameter saat language switching.
- [x] Tambahkan template layout, homepage, detail, tag, search, dan error.

## Deliverable

Visitor dapat menjelajah portfolio ID/EN, mencari content, dan memfilter berdasarkan tag.

## Definition of Done

- [ ] Semua public route utama memiliki response yang benar.
- [ ] Draft, archived, dan trashed tidak bocor melalui listing, search, tag, atau detail.
- [ ] Search result memiliki `noindex,follow`.
- [ ] Language switching mempertahankan slug dan query parameter.
- [ ] Test routing, visibility, pagination, search, dan tag lulus.
