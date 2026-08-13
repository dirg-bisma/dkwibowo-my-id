# Phase 3 — Admin dan Content Lifecycle

Status: in progress — admin/auth, CRUD, lifecycle, tags, redirect, CSRF, dan admin UI dasar dibuat; runtime test menunggu PHP/Composer.

## Tujuan

Menyediakan akses administrator dan pengelolaan metadata/content sesuai lifecycle FSD.

## Dependensi

Phase 1 dan Phase 2.

## Task

- [x] Implementasikan Google OAuth login, callback, logout, state validation, dan identity validation.
- [x] Batasi administrator berdasarkan `ADMIN_EMAILS`.
- [x] Terapkan session cookie `HttpOnly`, `Secure`, `SameSite=Lax` dan session regeneration.
- [x] Tambahkan admin routes dan authorization check pada setiap endpoint.
- [x] Buat form create/edit project.
- [x] Implementasikan create sebagai `draft`.
- [x] Implementasikan update tanpa mengubah status dan `published_at` secara otomatis.
- [x] Implementasikan validasi publish: slug, kedua title, kedua Markdown, dan cover.
- [x] Implementasikan publish, archive, restore ke draft, dan restore ke published.
- [x] Pastikan restore archived ke published membuat `published_at` baru.
- [x] Tambahkan create/update/delete tag.
- [x] Tolak penghapusan tag yang masih memiliki relasi.
- [x] Implementasikan slug change beserta 301 redirect untuk URL ID dan EN.
- [x] Pertahankan redirect lama sebagai histori saat slug berubah lagi.
- [x] Update FTS5 saat title, tags, publish, archive, restore, atau delete berubah.
- [x] Terapkan admin shell terpisah dari public layout dengan dashboard content/tag yang mudah dipindai.
- [x] Kelompokkan form create/edit berdasarkan identity, metadata, Markdown, dan lifecycle action.

## Deliverable

Administrator dapat mengelola project dan tag dengan lifecycle yang benar.

## Definition of Done

- [ ] Endpoint mutating hanya menggunakan POST.
- [ ] Semua endpoint mutating dilindungi CSRF.
- [ ] Unauthorized identity mendapat `403`.
- [ ] Conflict slug dan restore mendapat `409`.
- [ ] Lifecycle test dan slug redirect test lulus.
