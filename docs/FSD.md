# Functional Specification Document (FSD)

**Project:** Multilingual Portfolio Website
**Document:** Functional Specification Document
**Version:** 1.0
**Status:** Implementation Contract
**Language:** ID / EN

---

# 1. Document Purpose

Dokumen ini mendefinisikan kebutuhan fungsional sistem website portfolio multilingual berbasis PHP Native dengan content berbasis Markdown dan metadata/index berbasis SQLite.

Dokumen berfungsi sebagai **implementation contract** antara kebutuhan bisnis/fungsional dengan implementasi teknis.

Implementasi harus memenuhi requirement yang memiliki ID pada dokumen ini.

Setiap requirement memiliki:

* Requirement ID
* Functional requirement
* Business rule
* Acceptance criteria

Perubahan terhadap requirement yang telah berstatus final harus melalui perubahan versi dokumen.

---

# 2. System Overview

Sistem merupakan website portfolio multilingual dengan dua bahasa:

* Bahasa Indonesia (`id`)
* English (`en`)

Sistem terdiri dari:

1. Public Website
2. Content Management
3. Media Management
4. Search
5. Tag Management
6. SEO Management
7. Authentication
8. Content Lifecycle
9. Trash & Recovery
10. Sitemap & Robots

Arsitektur content menggunakan:

```text
SQLite
   +
Markdown Files
   +
Filesystem Media
```

SQLite berfungsi sebagai metadata/index store.

Markdown merupakan source of truth untuk body content.

---

# 3. Actors

## 3.1 Visitor

Visitor adalah pengguna publik yang dapat:

* melihat homepage;
* melihat project;
* melakukan search;
* melakukan filtering berdasarkan tag;
* melihat halaman tag;
* berpindah bahasa.

Visitor tidak dapat:

* mengakses admin;
* melihat draft;
* melihat archived content;
* melihat trash.

---

## 3.2 Search Engine Crawler

Crawler dapat mengakses:

* halaman public;
* published project;
* published tag page;
* sitemap;
* robots.txt.

Crawler tidak dapat mengakses:

* admin;
* database;
* draft;
* archived;
* trash;
* internal storage.

---

## 3.3 Administrator

Administrator dapat:

* login melalui Google OAuth;
* membuat project;
* mengedit project;
* mengubah slug;
* menyimpan draft;
* publish project;
* archive project;
* restore archived project;
* delete project ke trash;
* restore project dari trash;
* permanent delete;
* mengelola tags;
* upload cover;
* upload inline image.

---

# 4. Global Business Rules

## FSD-GEN-001 — Supported Languages

Sistem mendukung dua bahasa:

```text
id
en
```

Bahasa harus selalu ditentukan oleh URL public.

Bahasa tidak ditentukan oleh session.

---

## FSD-GEN-002 — Global Project Identity

Setiap project memiliki satu slug global.

Contoh:

```text
/id/content/norapos
/en/content/norapos
```

mengacu pada project yang sama.

Slug tidak memiliki versi bahasa.

---

## FSD-GEN-003 — Content Source

Body project disimpan dalam file Markdown:

```text
content/id/{slug}.md
content/en/{slug}.md
```

SQLite hanya menyimpan metadata dan lokasi file.

---

## FSD-GEN-004 — Public Visibility

Hanya project dengan:

```text
status = published
```

yang boleh muncul pada public website.

---

# 5. Content Management

## FSD-CNT-001 — Project Creation

Administrator dapat membuat project baru.

Data minimal:

```text
slug
title_id
title_en
content_id
content_en
tags
cover_image
```

Project baru memiliki status:

```text
draft
```

### Acceptance Criteria

**AC-CNT-001-01**

Ketika administrator membuat project baru, sistem membuat record dengan:

```text
status = draft
```

**AC-CNT-001-02**

Project draft tidak dapat diakses melalui public URL.

**AC-CNT-001-03**

Cover image tidak wajib ketika membuat draft.

---

## FSD-CNT-002 — Project Editing

Administrator dapat mengubah:

* slug;
* title ID;
* title EN;
* Markdown ID;
* Markdown EN;
* cover;
* tags.

Setiap perubahan memperbarui:

```text
updated_at
```

### Acceptance Criteria

**AC-CNT-002-01**

Perubahan data berhasil disimpan tanpa mengubah status secara otomatis.

**AC-CNT-002-02**

Perubahan body Markdown tidak mengubah `published_at`.

**AC-CNT-002-03**

Perubahan title atau tags memperbarui search index.

---

# 6. Slug

## FSD-SLG-001 — Slug Format

Slug harus memenuhi:

```regex
^[a-z0-9]+(?:-[a-z0-9]+)*$
```

Contoh valid:

```text
norapos
nora-pos
erp-system
pos-retail-2026
```

Contoh invalid:

```text
NoraPOS
nora_pos
-norapos
norapos-
nora--pos
```

---

## FSD-SLG-002 — Slug Uniqueness

Slug harus unik secara global.

### Acceptance Criteria

Jika slug:

```text
norapos
```

sudah digunakan, project lain tidak dapat menggunakan slug tersebut.

Sistem harus mengembalikan validation/conflict error.

---

## FSD-SLG-003 — Reserved Slug

Slug berikut tidak boleh digunakan sebagai project slug:

```text
admin
assets
database
content
sitemap
robots
api
tag
```

---

# 7. Multilingual Content

## FSD-LNG-001 — Language Content

Setiap project memiliki:

```text
title_id
title_en
content_id
content_en
```

---

## FSD-LNG-002 — Public Language URL

Bahasa ID:

```text
/id/content/{slug}
```

Bahasa EN:

```text
/en/content/{slug}
```

---

## FSD-LNG-003 — Language Resolution

Untuk:

```text
/id/content/norapos
```

sistem membaca:

```text
content/id/norapos.md
```

Untuk:

```text
/en/content/norapos
```

sistem membaca:

```text
content/en/norapos.md
```

### Acceptance Criteria

**AC-LNG-003-01**

Mengakses URL ID selalu menghasilkan content ID.

**AC-LNG-003-02**

Mengakses URL EN selalu menghasilkan content EN.

**AC-LNG-003-03**

Session tidak boleh menentukan bahasa halaman.

---

# 8. Content Lifecycle

Status:

```text
draft
published
archived
```

Lifecycle:

```text
                 publish
                    ↓
                 PUBLISHED
                ↙          ↘
           archive       delete
             ↓              ↓
          ARCHIVED        TRASH
             ↓              ↓
          restore        restore
             ↓              ↓
          DRAFT          previous status
                            ↓
                     permanent delete
```

---

## FSD-LFC-001 — Draft

Draft:

* tidak muncul di homepage;
* tidak muncul di search;
* tidak muncul di tag;
* tidak masuk sitemap;
* tidak dapat diakses public.

Cover tidak wajib.

---

## FSD-LFC-002 — Publish

Project dapat dipublish hanya jika:

```text
slug tersedia
title_id tersedia
title_en tersedia
content_id tersedia
content_en tersedia
cover_image tersedia
```

### Acceptance Criteria

Jika salah satu requirement tidak terpenuhi, publish ditolak.

Jika seluruh requirement terpenuhi:

```text
status = published
published_at = current timestamp
```

---

## FSD-LFC-003 — Archive

Published project dapat diubah menjadi:

```text
archived
```

Project archived tidak:

* tampil public;
* muncul search;
* muncul tag;
* masuk sitemap.

`published_at` sebelumnya tetap disimpan.

---

## FSD-LFC-004 — Restore Archived

Archived dapat dikembalikan menjadi:

```text
draft
```

atau:

```text
published
```

Jika:

```text
archived → draft
```

`published_at` tidak diubah.

Jika:

```text
archived → published
```

maka:

```text
published_at = current timestamp
```

### Acceptance Criteria

Restore ke published hanya berhasil jika seluruh publish requirement terpenuhi.

---

# 9. Trash & Recovery

## FSD-TRS-001 — Move to Trash

Delete tidak langsung menghapus project.

Flow:

```text
Active Content
      ↓
    Trash
```

Content di trash tidak muncul pada public website.

---

## FSD-TRS-002 — Trash Visibility

Content di trash:

* tidak muncul homepage;
* tidak muncul search;
* tidak muncul tag;
* tidak masuk sitemap;
* public URL menghasilkan 404.

---

## FSD-TRS-003 — Restore Trash

Administrator dapat restore content dari trash.

Restore mengembalikan status sebelum content dipindahkan ke trash.

Contoh:

```text
published → trash → published
draft → trash → draft
archived → trash → archived
```

---

## FSD-TRS-004 — Permanent Delete

Permanent delete hanya dapat dilakukan dari Trash.

Permanent delete menghapus:

* database record;
* Markdown files;
* cover;
* inline media milik content;
* redirect yang terkait;
* trash metadata.

Permanent delete tidak dapat di-undo.

---

# 10. Public Homepage

## FSD-HOM-001 — Published Listing

Homepage hanya menampilkan:

```text
status = published
```

Default sorting:

```text
published_at DESC
```

---

## FSD-HOM-002 — Pagination

Default:

```text
5 item/page
```

Contoh:

```text
/id
/id?page=2
```

dan:

```text
/en
/en?page=2
```

---

## FSD-HOM-003 — Empty Page

Jika page berada di luar jumlah halaman yang tersedia, sistem menampilkan halaman valid dengan empty state atau mengarahkan ke page terakhir sesuai implementasi UI.

Tidak boleh menghasilkan server error.

---

# 11. Search

## FSD-SRC-001 — Search

Visitor dapat melakukan search menggunakan:

```text
?q={keyword}
```

Contoh:

```text
/id?q=pos
```

---

## FSD-SRC-002 — Search Scope

Search hanya mencakup:

* `title_id`
* `title_en`
* tags

Search tidak mencakup body Markdown.

---

## FSD-SRC-003 — Search Visibility

Search hanya mengembalikan:

```text
status = published
```

---

## FSD-SRC-004 — Search Pagination

Search mendukung:

```text
/id?q=pos&page=2
```

Default:

```text
5 item/page
```

---

## FSD-SRC-005 — Search + Tag

Search dapat dikombinasikan dengan tag:

```text
/id?q=pos&tag=erp
```

Artinya:

```text
keyword match
AND
tag match
AND
published
```

---

## FSD-SRC-006 — Search SEO

Search result page tidak diindex oleh search engine.

Sistem memberikan:

```html
<meta name="robots" content="noindex,follow">
```

untuk URL search.

---

# 12. Tags

## FSD-TAG-001 — Tag

Project dapat memiliki multiple tags.

Satu tag dapat digunakan oleh multiple project.

Relasi:

```text
Project N : M Tag
```

---

## FSD-TAG-002 — Tag Slug

Tag memiliki:

```text
name
slug
```

Tag slug harus unique.

---

## FSD-TAG-003 — Tag Filtering

Filtering:

```text
/id?tag=erp
```

Hanya published project dengan tag tersebut yang ditampilkan.

---

## FSD-TAG-004 — Tag Page

Setiap tag memiliki halaman:

```text
/id/tag/erp
/en/tag/erp
```

Tag page mendukung pagination:

```text
/id/tag/erp?page=2
```

---

## FSD-TAG-005 — Tag SEO

Tag page merupakan halaman public yang dapat diindex.

Tag page memiliki:

* title;
* description;
* canonical;
* hreflang;
* Open Graph.

---

# 13. Public Project Detail

## FSD-DET-001 — Project Detail

Format:

```text
/id/content/{slug}
/en/content/{slug}
```

Hanya published project yang dapat diakses.

---

## FSD-DET-002 — Markdown Rendering

Markdown dikonversi menjadi semantic HTML.

Minimal menggunakan:

```html
<article>
<header>
<section>
```

HTML hasil rendering harus melalui sanitizer.

---

# 14. Media

## FSD-MED-001 — Cover Image

Cover image:

* tidak wajib pada draft;
* wajib pada published;
* digunakan sebagai Open Graph image.

---

## FSD-MED-002 — Inline Image

Administrator dapat upload inline image melalui Markdown editor.

---

## FSD-MED-003 — Upload Validation

Maximum:

```text
2 MB
```

File harus:

* valid image;
* MIME tervalidasi;
* dapat didecode sebagai image;
* memiliki extension yang diperbolehkan.

---

# 15. SEO

## FSD-SEO-001 — Meta Title

Meta title dihasilkan dari title bahasa aktif.

---

## FSD-SEO-002 — Meta Description

Description dibuat otomatis dari Markdown.

Proses:

```text
Markdown
↓
remove syntax
↓
remove images
↓
remove headings
↓
strip HTML
↓
normalize whitespace
↓
truncate
```

Target maksimum:

```text
160 characters
```

---

## FSD-SEO-003 — Canonical

Setiap halaman public memiliki canonical URL sendiri.

---

## FSD-SEO-004 — Hreflang

Project:

```text
hreflang=id
hreflang=en
hreflang=x-default
```

`x-default` diarahkan ke homepage.

---

## FSD-SEO-005 — Open Graph

OG image menggunakan cover image.

Jika cover tidak tersedia pada kondisi defensive fallback, sistem menggunakan default site image.

Published project secara normal selalu memiliki cover.

---

## FSD-SEO-006 — Schema.org

Project detail memiliki structured data Schema.org.

Default type:

```text
CreativeWork
```

Project yang memenuhi business rule software dapat menggunakan:

```text
SoftwareApplication
```

Structured data hanya menggunakan data yang benar-benar tersedia.

---

# 16. Sitemap

## FSD-SMP-001

Sitemap tersedia pada:

```text
/sitemap.xml
```

Hanya published content yang dimasukkan.

Setiap project menghasilkan:

```text
/id/content/{slug}
/en/content/{slug}
```

Tag page yang valid juga dapat dimasukkan jika sistem memperlakukan tag sebagai indexable resource.

---

# 17. Robots

## FSD-ROB-001

Robots tersedia:

```text
/robots.txt
```

Robots tidak boleh mengizinkan crawler mengakses administrative/private resource.

---

# 18. Authentication

## FSD-AUTH-001

Administrator login melalui Google OAuth 2.0.

---

## FSD-AUTH-002

Setelah authentication berhasil, sistem hanya mengizinkan Google identity yang telah dikonfigurasi sebagai administrator.

---

## FSD-AUTH-003

OAuth callback harus memvalidasi:

* state;
* token;
* issuer;
* audience;
* expiry;
* email;
* email verification.

---

# 19. Security

## FSD-SEC-001

Seluruh admin state-changing request menggunakan CSRF token.

---

## FSD-SEC-002

Markdown HTML disanitasi menggunakan HTMLPurifier.

---

## FSD-SEC-003

Slug divalidasi untuk mencegah traversal.

---

## FSD-SEC-004

Upload harus melakukan MIME validation dan image decoding.

---

## FSD-SEC-005

Database dan source Markdown tidak boleh dapat diakses langsung melalui HTTP.

---

# 20. Error Behavior

| Kondisi                 |             Response |
| ----------------------- | -------------------: |
| Valid published content |                  200 |
| Tidak ditemukan         |                  404 |
| Draft public access     |                  404 |
| Archived public access  |                  404 |
| Trash public access     |                  404 |
| Unauthenticated admin   | 401 / redirect login |
| Unauthorized admin      |                  403 |
| Invalid input           |                  422 |
| Duplicate slug          |                  409 |
| Restore slug conflict   |                  409 |
| Invalid HTTP request    |                  400 |
| Unexpected server error |                  500 |
| Old slug                |                  301 |

---

# 21. Acceptance Criteria Summary

Sistem dianggap memenuhi FSD apabila:

* [ ] ID dan EN menggunakan satu global slug.
* [ ] URL project menggunakan `/id/content/{slug}` dan `/en/content/{slug}`.
* [ ] Bahasa tidak bergantung pada session.
* [ ] Draft tidak public.
* [ ] Published membutuhkan cover.
* [ ] Archived tidak public.
* [ ] Archived dapat restore ke draft/published.
* [ ] Restore archived → published membuat `published_at` baru.
* [ ] Delete masuk Trash.
* [ ] Trash dapat direstore.
* [ ] Permanent delete hanya dari Trash.
* [ ] Homepage menggunakan pagination.
* [ ] Default pagination adalah 5 item.
* [ ] Search menggunakan `q`.
* [ ] Search hanya title + tags.
* [ ] Search tidak mencari body Markdown.
* [ ] Tag filtering tersedia.
* [ ] Tag memiliki halaman sendiri.
* [ ] Search result menggunakan `noindex`.
* [ ] Tag page dapat diindex.
* [ ] Canonical tersedia.
* [ ] Hreflang tersedia.
* [ ] x-default mengarah ke homepage.
* [ ] OG image menggunakan cover.
* [ ] Schema.org tersedia.
* [ ] Sitemap hanya memasukkan published resources.
* [ ] Google OAuth digunakan untuk admin.
* [ ] CSRF diterapkan.
* [ ] Markdown disanitasi.
* [ ] Upload dibatasi 2 MB.
* [ ] Database dan private storage tidak dapat diakses langsung.
