# Phase 1 — Storage dan Domain Core

Status: in progress — implementation dibuat; migration dan test runtime menunggu PHP/SQLite extension tersedia.

## Tujuan

Membangun source of truth dan aturan domain sebelum fitur HTTP dibuat.

## Dependensi

Phase 0.

## Task

- [x] Buat migration SQLite untuk `content`, `tags`, `content_tags`, `redirects`, `content_trash`, dan FTS5.
- [x] Tambahkan `project_type TEXT NOT NULL DEFAULT 'creative_work'` pada `content`.
- [x] Tambahkan status `trashed` pada constraint status content.
- [x] Pastikan `slug` dan tag `slug` unique.
- [x] Tambahkan unique constraint agar satu content hanya memiliki satu trash state aktif.
- [x] Tambahkan foreign key, cascade relation, dan index sesuai TSD.
- [x] Buat database connection dan migration runner.
- [x] Buat entity/value representation minimal untuk Content, Tag, dan Trash.
- [x] Buat `Slug` validator untuk format dan reserved slug.
- [x] Buat `ContentRepository`, `TagRepository`, `RedirectRepository`, dan `TrashRepository`.
- [x] Buat `MarkdownService` untuk membaca file berdasarkan language dan slug.
- [x] File bahasa yang hilang harus menghasilkan kondisi yang dapat dipetakan ke `404`.
- [x] Buat mekanisme rebuild FTS5 dari SQLite.

## Deliverable

- Database baru dapat dibuat dari nol.
- Repository dapat menyimpan, membaca, dan mengubah metadata tanpa direct SQL di controller.

## Definition of Done

- [ ] Migration berjalan pada database kosong.
- [ ] Constraint mencegah duplicate slug.
- [ ] Content trashed tidak dapat dianggap sebagai content publik.
- [ ] FTS5 dapat dihapus dan dibangun ulang dari source of truth.
- [ ] Test repository dan validator lulus.
