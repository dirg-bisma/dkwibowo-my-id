# Development Plan

Rencana implementasi Multilingual Portfolio Website berdasarkan:

- `docs/FSD.md` sebagai functional contract.
- `docs/TSD.md` sebagai technical contract.

## Urutan Phase

| Phase | Fokus | Hasil utama |
|---|---|---|
| 0 | Bootstrap proyek | Aplikasi PHP dapat dijalankan dengan struktur dasar |
| 1 | Storage dan domain core | Database, migration, repository, validasi domain |
| 2 | Public website | Homepage, detail content, tag, search, language routing |
| 3 | Admin dan content lifecycle | OAuth, CRUD, draft, publish, archive, slug redirect |
| 4 | Media dan SEO | Upload aman, Markdown rendering, metadata, schema, sitemap |
| 5 | Trash, recovery, dan hardening | Trash, restore, permanent delete, security boundary |
| 6 | Testing dan production readiness | Automated test, QA, deployment checklist |

Phase berikutnya hanya dimulai setelah Definition of Done phase sebelumnya terpenuhi.

## Keputusan yang Sudah Dikunci

- Status content: `draft`, `published`, `archived`, `trashed`.
- `project_type` ditambahkan ke tabel `content`; default: `creative_work`.
- File Markdown bahasa yang diminta tetapi tidak tersedia menghasilkan `404`.
- Tag yang masih memiliki relasi tidak dapat dihapus.
- Redirect slug lama dipertahankan sebagai histori.
- Redirect histori yang terkait permanent delete dibuat nonaktif; URL lama menghasilkan `404`.
- Operasi filesystem yang gagal di-rollback melalui staging.
- Hanya `published` yang dapat terlihat publik, dicari, muncul di tag page, dan masuk sitemap.

## Aturan Eksekusi

1. Selesaikan schema dan migration sebelum membuat controller yang bergantung padanya.
2. Business rule berada di service; controller hanya menangani request dan response.
3. Setiap perubahan lifecycle harus memiliki test sebelum phase dianggap selesai.
4. Private storage tetap di luar document root.
5. Jangan menambah dependency di luar TSD tanpa revisi TSD.

## Daftar Dokumen Phase

- [Phase 0 — Bootstrap](./phase-00-bootstrap.md)
- [Phase 1 — Storage dan Domain Core](./phase-01-storage-domain.md)
- [Phase 2 — Public Website](./phase-02-public-website.md)
- [Phase 3 — Admin dan Content Lifecycle](./phase-03-admin-content.md)
- [Phase 4 — Media dan SEO](./phase-04-media-seo.md)
- [Phase 5 — Trash, Recovery, dan Security](./phase-05-trash-security.md)
- [Phase 6 — Testing dan Production](./phase-06-testing-production.md)

