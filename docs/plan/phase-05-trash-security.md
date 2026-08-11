# Phase 5 — Trash, Recovery, dan Security Hardening

Status: in progress — Trash lifecycle, staging/restore, permanent delete, redirect deactivation, dan admin endpoints dibuat; runtime/security test menunggu PHP/Composer.

## Tujuan

Menyelesaikan operasi destructive yang dapat dipulihkan, lalu menutup boundary keamanan aplikasi.

## Dependensi

Phase 3 dan Phase 4.

## Task

- [x] Implementasikan move active content ke status `trashed`.
- [x] Pindahkan Markdown dan media melalui temporary staging ke `storage/trash/{trash-id}`.
- [x] Jika pemindahan gagal, rollback filesystem ke kondisi sebelum operasi.
- [x] Pastikan content trashed tidak muncul di public website dan URL-nya `404`.
- [x] Buat admin trash list.
- [x] Implementasikan restore ke status sebelum trash.
- [x] Validasi target path dan slug sebelum restore; jangan overwrite content lain.
- [x] Kembalikan `409` jika terjadi slug conflict.
- [x] Implementasikan permanent delete hanya dari Trash.
- [x] Hapus file, media milik content, relasi, content record, dan trash metadata sesuai urutan aman.
- [x] Jika filesystem delete gagal, pertahankan database record agar recovery memungkinkan.
- [x] Nonaktifkan redirect histori yang terkait permanent delete; URL lama harus `404`.
- [x] Terapkan CSRF constant-time comparison pada seluruh state-changing admin request.
- [x] Terapkan path traversal protection dan server-controlled filesystem base path.
- [x] Pastikan production error response tidak membocorkan SQL, path, stack trace, env, atau credential.
- [x] Tambahkan logging untuk auth, authorization, lifecycle, upload, trash, restore, delete, dan exception.

## Deliverable

Trash, restore, dan permanent delete aman serta dapat diuji tanpa kehilangan data akibat partial filesystem failure.

## Definition of Done

- [ ] Semua operasi trash memiliki rollback/staging behavior yang teruji.
- [ ] Permanent delete tidak dapat dilakukan dari state aktif.
- [ ] Redirect historis tetap tersimpan tetapi tidak aktif setelah permanent delete.
- [ ] Security test CSRF, authorization, XSS, traversal, MIME, dan error disclosure lulus.
