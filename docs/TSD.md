# Technical Specification Document (TSD)

**Project:** Multilingual Portfolio Website
**Document:** Technical Specification Document
**Version:** 1.0
**Status:** Implementation Contract
**Related Document:** FSD v1.0

---

# 1. Technical Objective

Sistem diimplementasikan sebagai aplikasi web Native PHP dengan:

```text
PHP 8.x
SQLite3
Apache
Bramus Router
CommonMark
HTMLPurifier
Google API Client
EasyMDE
Vanilla JS
HTML5
CSS3
```

Architecture:

```text
Browser
   ↓
Apache
   ↓
public/index.php
   ↓
Router
   ↓
Controller
   ↓
Service
   ↓
Repository
   ↓
SQLite / Filesystem
```

---

# 2. Architecture Principles

## TSD-ARCH-001 — Separation of Responsibility

Komponen memiliki tanggung jawab:

```text
Router
    ↓
Controller
    ↓
Service
    ↓
Repository
    ↓
Storage
```

Controller tidak boleh mengandung business logic kompleks.

---

## TSD-ARCH-002 — Source of Truth

Source of truth:

```text
Metadata:
SQLite

Body:
Markdown

Media:
Filesystem
```

FTS5 hanya derived index.

---

# 3. Directory Structure

```text
/
├── app/
│   ├── Controllers/
│   │   ├── PublicController.php
│   │   ├── ContentController.php
│   │   ├── SearchController.php
│   │   ├── TagController.php
│   │   └── AdminController.php
│   │
│   ├── Repositories/
│   │   ├── ContentRepository.php
│   │   ├── TagRepository.php
│   │   ├── RedirectRepository.php
│   │   └── TrashRepository.php
│   │
│   ├── Services/
│   │   ├── ContentService.php
│   │   ├── MarkdownService.php
│   │   ├── SeoService.php
│   │   ├── MediaService.php
│   │   ├── SearchService.php
│   │   ├── TagService.php
│   │   ├── TrashService.php
│   │   └── RedirectService.php
│   │
│   ├── Security/
│   │   ├── Auth.php
│   │   ├── Csrf.php
│   │   └── Authorization.php
│   │
│   └── Support/
│       ├── Validator.php
│       ├── Slug.php
│       └── Response.php
│
├── config/
│   ├── app.php
│   └── database.php
│
├── content/
│   ├── id/
│   └── en/
│
├── storage/
│   ├── media/
│   └── trash/
│
├── database/
│   └── indexer.sqlite
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
├── views/
│   ├── layouts/
│   │   └── main.php
│   ├── home.php
│   ├── content/
│   │   └── detail.php
│   ├── tag/
│   │   └── detail.php
│   ├── search.php
│   └── errors/
│
├── admin/
│   └── views/
│
├── public/
│   ├── index.php
│   ├── robots.txt
│   └── .htaccess
│
├── vendor/
│
├── .env
└── composer.json
```

Production document root harus diarahkan ke:

```text
/public
```

Private resource berada di luar document root.

---

# 4. Composer Dependencies

```json
{
    "require": {
        "php": "^8.2",
        "vlucas/phpdotenv": "^5.6",
        "google/apiclient": "^2.16",
        "league/commonmark": "^2.6",
        "ezyang/htmlpurifier": "^4.18",
        "bramus/router": "^1.6"
    }
}
```

Version aktual harus dikunci melalui `composer.lock`.

---

# 5. Database Schema

## 5.1 content

```sql
CREATE TABLE content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    slug TEXT NOT NULL UNIQUE,

    title_id TEXT NOT NULL,
    title_en TEXT NOT NULL,

    file_path_id TEXT NOT NULL,
    file_path_en TEXT NOT NULL,

    cover_image TEXT NULL,

    status TEXT NOT NULL DEFAULT 'draft',

    published_at DATETIME NULL,

    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,

    CHECK (
        status IN (
            'draft',
            'published',
            'archived'
        )
    )
);
```

---

# 6. Tags

```sql
CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    name TEXT NOT NULL,

    slug TEXT NOT NULL UNIQUE,

    created_at DATETIME NOT NULL,

    updated_at DATETIME NOT NULL
);
```

---

# 7. Content Tags

```sql
CREATE TABLE content_tags (
    content_id INTEGER NOT NULL,

    tag_id INTEGER NOT NULL,

    PRIMARY KEY (
        content_id,
        tag_id
    ),

    FOREIGN KEY (content_id)
        REFERENCES content(id)
        ON DELETE CASCADE,

    FOREIGN KEY (tag_id)
        REFERENCES tags(id)
        ON DELETE CASCADE
);
```

---

# 8. Redirects

```sql
CREATE TABLE redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    old_path TEXT NOT NULL UNIQUE,

    new_path TEXT NOT NULL,

    status_code INTEGER NOT NULL DEFAULT 301,

    created_at DATETIME NOT NULL
);
```

`status_code` untuk MVP harus bernilai:

```text
301
```

---

# 9. Trash

```sql
CREATE TABLE content_trash (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    content_id INTEGER NOT NULL,

    original_status TEXT NOT NULL,

    original_slug TEXT NOT NULL,

    trashed_at DATETIME NOT NULL,

    FOREIGN KEY (content_id)
        REFERENCES content(id)
        ON DELETE CASCADE,

    CHECK (
        original_status IN (
            'draft',
            'published',
            'archived'
        )
    )
);
```

Satu content aktif hanya boleh memiliki satu trash state.

---

# 10. FTS5 Search Index

SQLite FTS5 digunakan sebagai derived search index.

```sql
CREATE VIRTUAL TABLE content_search
USING fts5(
    title_id,
    title_en,
    tags,
    content_id UNINDEXED
);
```

Body Markdown tidak dimasukkan.

---

# 11. Database Indexes

Minimal:

```sql
CREATE INDEX idx_content_status
ON content(status);

CREATE INDEX idx_content_published_at
ON content(published_at);

CREATE INDEX idx_content_updated_at
ON content(updated_at);

CREATE INDEX idx_content_tags_tag
ON content_tags(tag_id);

CREATE INDEX idx_content_tags_content
ON content_tags(content_id);
```

---

# 12. Database Transaction Rules

SQLite transaction digunakan untuk operasi metadata.

Contoh:

```text
BEGIN
 ↓
database mutations
 ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

Filesystem tidak dianggap transactional oleh SQLite.

Operasi filesystem yang berkaitan dengan database harus menggunakan staging/trash mechanism.

---

# 13. Route Contract

## 13.1 Homepage

```http
GET /id
GET /en
```

Optional query:

```text
?page=2
?q=pos
?tag=erp
?q=pos&tag=erp&page=2
```

---

## 13.2 Content

```http
GET /id/content/{slug}
GET /en/content/{slug}
```

---

## 13.3 Tag

```http
GET /id/tag/{tag}
GET /en/tag/{tag}
```

Pagination:

```http
GET /id/tag/{tag}?page=2
```

---

## 13.4 Sitemap

```http
GET /sitemap.xml
```

---

## 13.5 Robots

```http
GET /robots.txt
```

---

# 14. Admin Route Contract

```http
GET  /admin
GET  /admin/login
POST /admin/logout

GET  /admin/content/create
POST /admin/content/create

GET  /admin/content/{id}/edit
POST /admin/content/{id}/edit

POST /admin/content/{id}/publish
POST /admin/content/{id}/archive

POST /admin/content/{id}/restore-draft
POST /admin/content/{id}/restore-published

POST /admin/content/{id}/trash

GET  /admin/trash
POST /admin/trash/{id}/restore
POST /admin/trash/{id}/delete

POST /admin/upload/cover
POST /admin/upload/inline
```

Semua endpoint mutating menggunakan:

```text
POST
```

dan CSRF token.

---

# 15. Controller Contract

## PublicController

Tanggung jawab:

* homepage;
* pagination;
* public listing;
* language context.

Tidak melakukan direct SQL.

---

## ContentController

Tanggung jawab:

* resolve slug;
* resolve language;
* load published content;
* render detail;
* 404 handling.

---

## SearchController

Tanggung jawab:

* membaca `q`;
* membaca `tag`;
* membaca `page`;
* memanggil SearchService;
* render result.

---

## TagController

Tanggung jawab:

* resolve tag;
* load published content;
* pagination;
* render tag page.

---

## AdminController

Tanggung jawab:

* admin request;
* authentication;
* validation;
* invoke service.

Business logic tetap berada pada service.

---

# 16. Service Contract

## ContentService

Method konseptual:

```php
createDraft(array $data): Content
update(int $id, array $data): Content
publish(int $id): Content
archive(int $id): Content
restoreAsDraft(int $id): Content
restoreAsPublished(int $id): Content
moveToTrash(int $id): void
```

---

## SearchService

```php
search(
    string $query,
    ?string $tag,
    int $page,
    int $perPage = 5
): SearchResult
```

Search hanya menggunakan:

```text
title_id
title_en
tags
```

---

## TagService

```php
create(array $data): Tag
update(int $id, array $data): Tag
delete(int $id): void
findBySlug(string $slug): ?Tag
```

---

## TrashService

```php
moveToTrash(int $contentId): void
restore(int $trashId): void
permanentDelete(int $trashId): void
```

---

## MarkdownService

```php
render(string $markdown): string
extractDescription(string $markdown): string
```

---

## SeoService

```php
generateMeta(Content $content, string $language): SeoMetadata
generateSchema(Content $content, string $language): array
generateHreflang(Content $content): array
```

---

## MediaService

```php
uploadCover(array $file): string
uploadInline(array $file): string
moveToTrash(string $path, string $trashId): void
restoreFromTrash(string $path, string $trashId): void
permanentDelete(string $path): void
```

---

# 17. Content Resolution Contract

Request:

```text
/id/content/norapos
```

menjadi:

```text
language = id
slug = norapos
```

Repository:

```sql
SELECT *
FROM content
WHERE slug = :slug
AND status = 'published'
LIMIT 1;
```

File:

```text
content/id/norapos.md
```

Request EN:

```text
/en/content/norapos
```

File:

```text
content/en/norapos.md
```

---

# 18. Markdown File Contract

Format file:

```text
content/
├── id/
│   └── norapos.md
└── en/
    └── norapos.md
```

Body file hanya berisi Markdown.

Metadata project tidak disimpan sebagai YAML frontmatter untuk menghindari duplikasi dengan SQLite.

---

# 19. Markdown Rendering Pipeline

```text
Markdown
   ↓
CommonMark
   ↓
HTML
   ↓
HTMLPurifier
   ↓
Semantic HTML
   ↓
Template
```

Output harus di-escape/filtered sesuai konteks.

---

# 20. SEO Description Algorithm

```text
Markdown
 ↓
Remove images
 ↓
Remove headings
 ↓
Convert links → text
 ↓
Strip Markdown syntax
 ↓
Strip HTML
 ↓
Normalize whitespace
 ↓
Trim
 ↓
Maximum 160 characters
```

Truncation harus sebisa mungkin berada pada word boundary.

---

# 21. SEO Contract

Detail project menghasilkan:

```html
<title>...</title>

<meta name="description" content="...">

<link rel="canonical" href="...">

<link
    rel="alternate"
    hreflang="id"
    href="..."
>

<link
    rel="alternate"
    hreflang="en"
    href="..."
>

<link
    rel="alternate"
    hreflang="x-default"
    href="https://domain.com/"
>

<meta property="og:title" content="...">
<meta property="og:description" content="...">
<meta property="og:image" content="...">
<meta property="og:url" content="...">
```

---

# 22. Search SEO Contract

Search URL:

```text
/id?q=pos
```

menghasilkan:

```html
<meta
    name="robots"
    content="noindex,follow"
>
```

---

# 23. Schema.org Contract

Default project:

```json
{
    "@context": "https://schema.org",
    "@type": "CreativeWork",
    "name": "...",
    "description": "...",
    "image": "...",
    "datePublished": "...",
    "dateModified": "..."
}
```

Untuk project software:

```json
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication"
}
```

Pemilihan type dilakukan berdasarkan project type yang ditentukan oleh sistem.

Jangan menghasilkan property yang datanya tidak tersedia.

---

# 24. Sitemap Contract

Endpoint:

```text
/sitemap.xml
```

Query:

```sql
SELECT slug, updated_at
FROM content
WHERE status = 'published'
ORDER BY updated_at DESC;
```

Setiap project:

```text
/id/content/{slug}
/en/content/{slug}
```

Tag page yang aktif juga dapat dimasukkan:

```text
/id/tag/{tag}
/en/tag/{tag}
```

Search URL tidak dimasukkan.

Draft tidak dimasukkan.

Archived tidak dimasukkan.

Trash tidak dimasukkan.

---

# 25. Pagination Contract

Default:

```text
PER_PAGE = 5
```

Maximum:

```text
MAX_PER_PAGE = 50
```

Public URL menggunakan:

```text
?page=N
```

Offset:

```text
offset = (page - 1) * 5
```

Pagination harus menggunakan deterministic ordering:

```sql
ORDER BY published_at DESC, id DESC
```

---

# 26. Search Contract

Search FTS5:

```text
query
 ↓
content_search
 ↓
content_id
 ↓
JOIN content
 ↓
status = published
 ↓
optional tag filtering
 ↓
ORDER
 ↓
pagination
```

Body Markdown tidak dicari.

---

# 27. Tag Query Contract

Tag filtering:

```sql
SELECT c.*
FROM content c
JOIN content_tags ct
    ON ct.content_id = c.id
JOIN tags t
    ON t.id = ct.tag_id
WHERE c.status = 'published'
AND t.slug = :tag
ORDER BY
    c.published_at DESC,
    c.id DESC
LIMIT :limit
OFFSET :offset;
```

---

# 28. Language Switching Contract

Project:

```text
/id/content/norapos
```

switch ke:

```text
/en/content/norapos
```

Tag:

```text
/id/tag/erp
```

switch ke:

```text
/en/tag/erp
```

Listing:

```text
/id?q=pos&tag=erp&page=2
```

switch ke:

```text
/en?q=pos&tag=erp&page=2
```

Query parameter dipertahankan.

---

# 29. Slug Change Contract

Ketika:

```text
norapos
```

berubah menjadi:

```text
nora-pos
```

system membuat:

```text
/id/content/norapos
→ 301
→ /id/content/nora-pos
```

dan:

```text
/en/content/norapos
→ 301
→ /en/content/nora-pos
```

Redirect disimpan pada tabel:

```text
redirects
```

---

# 30. Filesystem Contract

Private storage:

```text
content/
database/
storage/
```

harus berada di luar document root.

Public assets:

```text
assets/
```

dapat diakses HTTP.

---

# 31. Content Storage

```text
content/
├── id/
│   └── {slug}.md
└── en/
    └── {slug}.md
```

File path disimpan dalam:

```text
file_path_id
file_path_en
```

Path harus berupa relative application path.

---

# 32. Media Storage

```text
storage/
└── media/
    ├── cover/
    └── inline/
```

Nama file harus generated.

Contoh:

```text
01JXYZ...webp
```

Nama file asli tidak digunakan sebagai storage identifier.

---

# 33. Media Ownership

Setiap media yang di-upload melalui project dianggap milik project tersebut.

MVP tidak mendukung shared media ownership.

Hal ini membuat permanent delete dapat menentukan media mana yang harus dihapus.

---

# 34. Trash Filesystem

```text
storage/
└── trash/
    └── {trash-id}/
        ├── content/
        │   ├── id/
        │   └── en/
        │
        └── media/
```

---

# 35. Trash Operation

Move to trash:

```text
active resource
      ↓
temporary staging
      ↓
trash/{trash-id}
```

Jika seluruh move berhasil:

```text
staging → final trash
```

Jika gagal:

```text
rollback filesystem operation
```

---

# 36. Trash Restore

Restore:

```text
trash/{trash-id}
      ↓
validate slug
      ↓
validate target path
      ↓
move files
      ↓
restore DB state
```

Jika slug conflict:

```text
409 Conflict
```

Tidak boleh melakukan overwrite terhadap project lain.

---

# 37. Permanent Delete

Permanent deletion dilakukan dari Trash.

Urutan:

```text
validate trash
      ↓
delete files
      ↓
delete DB references
      ↓
delete content
      ↓
delete trash record
```

Jika filesystem deletion gagal:

```text
database record tidak dihapus
```

Agar recovery masih memungkinkan.

---

# 38. Upload Contract

Maximum:

```text
2 MB
```

Validation wajib:

1. file upload error;
2. file size;
3. MIME;
4. image decoding;
5. extension;
6. generated filename.

Tidak boleh menggunakan:

```php
$_FILES['name']
```

sebagai storage filename.

---

# 39. Authentication Contract

Google OAuth:

```text
Browser
 ↓
Google Authorization
 ↓
Callback
 ↓
State validation
 ↓
Token validation
 ↓
Identity validation
 ↓
Session
```

Administrator ditentukan berdasarkan konfigurasi server.

Contoh environment:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
ADMIN_EMAILS=
```

`ADMIN_EMAILS` berisi daftar identity yang diperbolehkan.

---

# 40. Session Security

Session cookie:

```text
HttpOnly
Secure
SameSite=Lax
```

Production wajib menggunakan HTTPS.

Setelah login berhasil:

```php
session_regenerate_id(true);
```

Logout harus:

* invalidate session;
* regenerate/delete session cookie.

---

# 41. CSRF Contract

Semua POST admin membutuhkan:

```text
CSRF token
```

Token:

* random;
* session-bound;
* dibandingkan menggunakan constant-time comparison;
* invalid setelah session logout.

OAuth `state` tidak menggantikan CSRF token aplikasi.

---

# 42. Authorization Contract

Authentication:

```text
Who are you?
```

Authorization:

```text
Are you allowed?
```

Setiap admin controller harus memeriksa keduanya.

Tidak boleh hanya mengandalkan:

```php
isset($_SESSION['admin'])
```

tanpa identity validation yang sesuai.

---

# 43. XSS Contract

Markdown diproses:

```text
CommonMark
 ↓
HTMLPurifier
```

HTMLPurifier harus dikonfigurasi untuk hanya mengizinkan markup yang diperlukan.

Script:

```html
<script>
```

event handler:

```html
onclick=
onerror=
onload=
```

dan executable URL scheme harus ditolak.

---

# 44. Path Traversal Contract

Input slug tidak boleh digunakan sebagai filesystem path sebelum validasi.

Wajib:

```regex
^[a-z0-9]+(?:-[a-z0-9]+)*$
```

File path harus dibentuk dari server-controlled base directory.

Tidak boleh menerima:

```text
../
..\ 
/
```

sebagai bagian path project.

---

# 45. HTTP Error Contract

## 400

Malformed request.

---

## 401

Authentication diperlukan.

---

## 403

Identity valid tetapi tidak memiliki authorization.

---

## 404

Resource tidak ditemukan atau tidak public.

Untuk draft/archived/trash, gunakan 404 agar existence private tidak bocor.

---

## 409

Conflict.

Contoh:

* duplicate slug;
* restore dengan slug conflict.

---

## 422

Validation error.

Contoh:

* title kosong;
* content kosong saat publish;
* cover tidak valid;
* slug invalid.

---

## 500

Unexpected internal error.

Production response tidak boleh menampilkan:

* stack trace;
* SQL;
* filesystem path;
* environment variable;
* credentials.

Detail error masuk server log.

---

# 46. Response Contract

Public HTML:

```http
Content-Type: text/html; charset=UTF-8
```

Sitemap:

```http
Content-Type: application/xml; charset=UTF-8
```

Robots:

```http
Content-Type: text/plain; charset=UTF-8
```

---

# 47. Caching

Public published content dapat menggunakan HTTP cache.

Draft/admin response tidak boleh menggunakan public cache.

Cache key minimal mempertimbangkan:

```text
language
slug
```

Jika slug berubah, old URL harus 301.

---

# 48. Atomic Content Update

Update Markdown:

```text
Validate
   ↓
Write temporary file
   ↓
Verify
   ↓
Atomic rename
   ↓
Update SQLite metadata
   ↓
Commit
```

Temporary file harus berada pada filesystem yang sama agar `rename()` bersifat atomic.

---

# 49. Publish Transaction

Publish:

```text
Validate content
Validate ID Markdown
Validate EN Markdown
Validate cover
Validate slug
       ↓
BEGIN SQLite
       ↓
update status
       ↓
set published_at = NOW()
       ↓
COMMIT
```

Search index kemudian diperbarui.

---

# 50. Publish Timestamp

First publish:

```text
published_at = NOW()
```

Archive:

```text
published_at tetap
```

Restore archived → draft:

```text
published_at tetap
```

Restore archived → published:

```text
published_at = NOW()
```

---

# 51. Search Index Synchronization

FTS index diperbarui ketika:

```text
create
title update
tag update
restore
publish
archive/delete
```

Body Markdown tidak menyebabkan search index update.

Jika index hilang/corrupt, index dapat direbuild dari SQLite:

```text
content
+
tags
+
content_tags
      ↓
rebuild FTS5
```

---

# 52. Tag Lifecycle

Tag dapat dihapus jika tidak digunakan.

Jika tag masih digunakan:

```text
DELETE tag
```

harus:

```text
detach content relations
```

atau ditolak berdasarkan policy implementasi.

Untuk implementation contract ini, tag yang masih digunakan **tidak boleh dihapus tanpa terlebih dahulu menghapus relasinya**.

---

# 53. SEO URL Normalization

Canonical URL harus:

* HTTPS;
* absolute;
* menggunakan configured application domain;
* tidak memiliki trailing slash pada content URL;
* menggunakan lowercase slug.

Contoh:

```text
https://example.com/id/content/norapos
```

---

# 54. Robots Contract

```text
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /database/
Disallow: /storage/
Disallow: /content/
Sitemap: https://example.com/sitemap.xml
```

Jika `content/` adalah filesystem di luar document root, directive `Disallow: /content/` tidak diperlukan.

Private storage harus diamankan di level filesystem/web server, bukan hanya robots.txt.

---

# 55. Apache Contract

Semua public request diarahkan ke:

```text
/public/index.php
```

kecuali file static yang memang tersedia secara public.

Contoh:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

RewriteRule ^ index.php [QSA,L]
```

Private directories tidak berada di document root.

---

# 56. Dependency Responsibility

| Dependency          | Responsibility            |
| ------------------- | ------------------------- |
| bramus/router       | Routing                   |
| league/commonmark   | Markdown parser           |
| ezyang/htmlpurifier | HTML sanitization         |
| google/apiclient    | Google OAuth              |
| vlucas/phpdotenv    | Environment configuration |
| EasyMDE             | Markdown editor           |
| SQLite FTS5         | Search index              |

---

# 57. Configuration Contract

`.env` minimal:

```env
APP_ENV=production
APP_URL=https://example.com
APP_TIMEZONE=Asia/Jakarta

DB_PATH=/path/to/database/indexer.sqlite

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

ADMIN_EMAILS=

UPLOAD_MAX_SIZE=2097152
DEFAULT_PER_PAGE=5
MAX_PER_PAGE=50
```

Secret tidak boleh masuk repository.

---

# 58. Logging

Server harus mencatat minimal:

* authentication failure;
* authorization failure;
* publish failure;
* trash failure;
* restore failure;
* permanent delete failure;
* upload failure;
* unexpected exception.

Log tidak boleh menyimpan:

* OAuth client secret;
* session ID;
* credential;
* access token.

---

# 59. Implementation Invariants

Implementasi wajib mempertahankan invariant berikut:

### INV-001

```text
slug UNIQUE
```

### INV-002

Published content harus mempunyai:

```text
title_id
title_en
content_id
content_en
cover_image
```

### INV-003

Public content hanya:

```text
status = published
```

### INV-004

Satu content memiliki satu global slug.

### INV-005

Search hanya mengembalikan published content.

### INV-006

Tag page hanya mengembalikan published content.

### INV-007

Trash tidak dapat diakses public.

### INV-008

Permanent delete hanya berasal dari Trash.

### INV-009

FTS5 bukan source of truth.

### INV-010

Markdown body bukan bagian dari search.

---

# 60. FSD → TSD Traceability

| FSD Requirement | Technical Implementation            |
| --------------- | ----------------------------------- |
| FSD-CNT         | ContentService + ContentRepository  |
| FSD-SLG         | Slug validator + UNIQUE constraint  |
| FSD-LNG         | Router language parameter           |
| FSD-LFC         | ContentService lifecycle            |
| FSD-TRS         | TrashService + content_trash        |
| FSD-HOM         | PublicController + pagination       |
| FSD-SRC         | SearchService + FTS5                |
| FSD-TAG         | TagService + N:M tables             |
| FSD-MED         | MediaService                        |
| FSD-SEO         | SeoService                          |
| FSD-SMP         | Sitemap generator                   |
| FSD-ROB         | robots.txt                          |
| FSD-AUTH        | Google OAuth + Auth                 |
| FSD-SEC         | CSRF + HTMLPurifier + validation    |
| FSD-DET         | ContentController + MarkdownService |

---

# 61. Minimum Test Coverage

Implementation harus memiliki test untuk minimal:

## Content

* create draft;
* update draft;
* publish valid;
* reject invalid publish;
* archive;
* restore archived → draft;
* restore archived → published;
* publish timestamp reset.

## Slug

* valid slug;
* invalid slug;
* duplicate slug;
* reserved slug;
* slug change;
* old URL 301.

## Multilingual

* ID content;
* EN content;
* language switch;
* missing language file.

## Search

* title ID match;
* title EN match;
* tag match;
* combined search + tag;
* pagination;
* no result;
* draft excluded;
* archived excluded.

## Tags

* create tag;
* tag filtering;
* tag page;
* tag pagination;
* tag language switching.

## Trash

* move to trash;
* restore;
* restore conflict;
* permanent delete;
* filesystem failure;
* database failure.

## Security

* CSRF invalid;
* unauthorized admin;
* XSS Markdown;
* path traversal;
* invalid MIME;
* oversized upload.

## SEO

* canonical;
* hreflang;
* x-default;
* meta description;
* OG image;
* Schema.org;
* sitemap exclusion;
* search noindex.

---

# 62. Definition of Done

Implementation dianggap selesai apabila:

1. seluruh FSD requirement telah diimplementasikan;
2. seluruh acceptance criteria berhasil;
3. seluruh invariant terpenuhi;
4. database migration dapat dijalankan pada environment baru;
5. filesystem structure dapat dibuat otomatis;
6. application dapat dijalankan tanpa manual modification pada source;
7. production environment tidak mengekspos private storage;
8. automated test untuk critical lifecycle berhasil;
9. SEO validation tidak menemukan canonical/hreflang conflict;
10. tidak terdapat credential dalam source repository.

---

# 63. Final Technical Contract

Struktur sistem yang dikunci:

```text
                    ┌──────────────┐
                    │   Browser    │
                    └──────┬───────┘
                           │
                           ▼
                    ┌──────────────┐
                    │    Apache    │
                    └──────┬───────┘
                           │
                           ▼
                    ┌──────────────┐
                    │    Router    │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
         Controller    Controller    Controller
              │            │            │
              └────────────┼────────────┘
                           ▼
                      Services
                           │
              ┌────────────┼─────────────┐
              ▼            ▼             ▼
         Repository    Markdown       Media
              │         Service       Service
              ▼
           SQLite
              │
       ┌──────┴──────┐
       ▼             ▼
    Metadata       FTS5
```

Storage:

```text
                    Application
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
       SQLite         Markdown        Media
          │              │              │
          │              │              │
       metadata       content/       storage/media/
          │
          ▼
       FTS5 index
```

Lifecycle:

```text
                  ┌─────────┐
                  │  DRAFT  │
                  └────┬────┘
                       │ publish
                       ▼
                ┌─────────────┐
                │  PUBLISHED  │
                └──────┬──────┘
                       │ archive
                       ▼
                ┌─────────────┐
                │  ARCHIVED   │
                └──────┬──────┘
                       │ restore
              ┌────────┴────────┐
              ▼                 ▼
           DRAFT            PUBLISHED

Any active state
       │
       │ delete
       ▼
    ┌───────┐
    │ TRASH │
    └───┬───┘
        │
   ┌────┴────┐
   ▼         ▼
RESTORE   PERMANENT
           DELETE
```

Public URL:

```text
/id
/en

/id/content/{slug}
/en/content/{slug}

/id/tag/{tag}
/en/tag/{tag}
```

Discovery:

```text
/id?q={keyword}
/id?tag={tag}
/id?q={keyword}&tag={tag}
/id?page={n}
/id?q={keyword}&tag={tag}&page={n}
```

Default pagination:

```text
5 items/page
```

Search:

```text
title_id
title_en
tags
```

**Body Markdown tidak dicari.**

---

# 64. Contract Status

Dokumen ini menjadi baseline implementasi:

```text
FSD v1.0
TSD v1.0
```

Requirement dengan ID pada FSD merupakan functional contract.

Detail implementasi pada TSD merupakan technical contract.

Jika implementasi berbeda dari TSD tetapi tetap memenuhi FSD, perubahan harus didokumentasikan sebagai **TSD revision** dan tidak boleh diam-diam mengubah behavior FSD.
