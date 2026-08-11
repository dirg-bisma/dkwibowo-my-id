# Audit Definition of Done — Phase 1 sampai Phase 5

Tanggal audit: 2026-08-10

## Kesimpulan

Phase 1–5 belum dapat diterima sebagai selesai penuh. Implementasi utama tersedia, tetapi bukti automated test untuk critical path dan security/SEO belum lengkap. Karena kontrak TSD mensyaratkan seluruh acceptance criteria dan test critical lifecycle, status final masih **not accepted**.

## Ringkasan status

| Phase | Status DoD | Bukti yang tersedia | Gap penerimaan |
|---|---|---|---|
| Phase 1 | Partial | Migration Docker, schema constraint, slug smoke test, `project_type`, status `trashed` | Belum ada test repository dan FTS rebuild yang berdiri sendiri |
| Phase 2 | Partial | Route public, visibility query, pagination/search/tag implementation, HTTP smoke dasar | Belum ada fixture published/draft/archived/trashed untuk menguji visibility, search, tag, dan language switching |
| Phase 3 | Partial | OAuth boundary, admin authorization, POST mutation, lifecycle implementation | OAuth, CSRF, lifecycle, conflict, dan redirect belum diuji otomatis; logout GET telah diperbaiki menjadi POST + CSRF |
| Phase 4 | Partial | CommonMark + HTMLPurifier, media validation, SEO service, sitemap/robots | Belum ada test XSS/scheme, upload 422, SEO metadata, populated sitemap, dan canonical/hreflang |
| Phase 5 | Partial | Staging/restore/permanent-delete implementation dan path guards | Belum ada failure-injection test untuk rollback, delete failure, traversal, MIME, dan error disclosure |

## Verifikasi yang sudah dilakukan

- Docker image berhasil dibangun dengan `composer.lock`.
- Migration dan filesystem initialization berhasil pada container baru.
- `tests/smoke.php` lulus.
- PHP lint seluruh source lulus.
- Composer validation lulus.
- HTTP smoke check: endpoint dasar `200`, admin `302`, route/resource privat `404`.
- Apache virtual host aktif menggunakan `/var/www/html/public` sebagai document root.

## Syarat sebelum status diterima

1. Tambahkan automated test repository/validator/FTS untuk Phase 1.
2. Tambahkan fixture test untuk public visibility, pagination, search, tag, dan language switching.
3. Tambahkan test auth/CSRF/authorization dan lifecycle lengkap sampai redirect history.
4. Tambahkan test Markdown sanitization, upload invalid/oversized, dan seluruh SEO output.
5. Tambahkan failure-injection test trash/restore/permanent delete serta error disclosure.
6. Jalankan acceptance checklist FSD dan tandai hanya requirement yang memiliki bukti test.

