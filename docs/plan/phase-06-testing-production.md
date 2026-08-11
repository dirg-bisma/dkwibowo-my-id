# Phase 6 — Testing dan Production Readiness

## Tujuan

Memastikan seluruh kontrak FSD/TSD terverifikasi dan aplikasi siap dijalankan di production.

## Status

Docker smoke gate selesai. Build reproducible, migration, PHP lint, endpoint dasar, dan isolasi private storage telah diverifikasi. Full end-to-end OAuth dan seluruh acceptance flow dengan fixture konten masih menjadi pekerjaan lanjutan.

## Dependensi

Phase 0 sampai Phase 5.

## Task

- [ ] Lengkapi unit test validator, service, repository, SEO, dan media.
- [ ] Lengkapi integration test untuk routing, database, filesystem, OAuth boundary, dan lifecycle.
- [ ] Jalankan seluruh test critical path: create, publish, archive, restore, trash, permanent delete.
- [ ] Uji duplicate slug, reserved slug, slug change, redirect, dan redirect setelah permanent delete.
- [ ] Uji ID/EN, missing language file, language switching, pagination, search, tag, sitemap, dan robots.
- [x] Uji production document root hanya menunjuk ke `public/`.
- [x] Uji private storage tidak dapat diakses melalui HTTP.
- [x] Uji migration dan filesystem initialization pada environment kosong.
- [x] Jalankan dependency audit dan pastikan `composer.lock` digunakan.
- [x] Pastikan tidak ada credential atau `.env` dalam repository.
- [x] Buat deployment/runbook singkat untuk konfigurasi `.env`, Apache, database, writable directory, dan HTTPS.
- [ ] Jalankan acceptance checklist dari FSD.

## Deliverable

- Test suite critical path lulus.
- Deployment checklist dan runbook tersedia.
- Semua invariant dan Definition of Done TSD terpenuhi.

## Definition of Done

- [x] Automated smoke test dan PHP lint lulus.
- [x] Tidak ada private resource yang terekspos pada HTTP smoke check.
- [ ] SEO validation tidak menemukan canonical/hreflang conflict.
- [x] Production configuration tervalidasi melalui Docker Compose dan Composer lock.
- [ ] FSD acceptance criteria ditandai selesai.
