# dkwibowo

Website portfolio multilingual berbasis PHP Native dengan arsitektur SQLite + Markdown + Filesystem.

## Deskripsi

Website portfolio profesional dengan dukungan dua bahasa (Indonesia dan English) yang menggunakan pendekatan hybrid storage:

- **SQLite** untuk metadata dan indexing
- **Markdown** sebagai source of truth untuk konten
- **Filesystem** untuk media storage

Design mengadopsi konsep **dark technical editorial portfolio** dengan interface profesional, aksen biru elektrik, dan tipografi yang kuat.

## Teknologi

### Backend
- PHP 8.2+
- SQLite3 dengan FTS5 untuk full-text search
- Apache Web Server

### Libraries
- [Bramus Router](https://github.com/bramus/router) - Routing
- [League CommonMark](https://commonmark.thephpleague.com/) - Markdown parsing
- [HTML Purifier](http://htmlpurifier.org/) - HTML sanitization
- [Google API Client](https://github.com/googleapis/google-api-php-client) - OAuth authentication
- [phpdotenv](https://github.com/vlucas/phpdotenv) - Environment configuration

### Frontend
- Vanilla JavaScript
- HTML5 & CSS3
- EasyMDE (Markdown editor untuk admin)

### Infrastructure
- Docker & Docker Compose
- Apache

## Arsitektur

```
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

### Struktur Direktori

```
/
├── app/
│   ├── Controllers/      # HTTP request handlers
│   ├── Repositories/     # Data access layer
│   ├── Services/         # Business logic
│   ├── Security/         # Auth, CSRF, Authorization
│   ├── Domain/           # Domain models
│   ├── Database/         # Database connection
│   └── Support/          # Helper classes
├── config/               # Configuration files
├── content/              # Markdown content files
│   ├── id/              # Indonesian content
│   └── en/              # English content
├── storage/              # File storage
│   ├── media/           # Uploaded media
│   └── trash/           # Deleted content backup
├── database/             # SQLite database
├── public/               # Web root
├── views/                # Template files
├── assets/               # Static assets (CSS, JS, images)
├── tests/                # Test files
└── docs/                 # Documentation
```

## Fitur Utama

### Public Website
- Homepage dengan featured projects
- Halaman detail project
- Search dengan FTS5
- Tag filtering dan tag pages
- Multilingual (ID/EN) dengan hreflang
- Responsive design

### Content Management
- WYSIWYG Markdown editor
- Draft, publish, archive workflow
- Slug management dengan redirect otomatis
- Cover image upload (max 2MB)
- Tag management
- Content lifecycle tracking

### SEO
- Canonical URLs
- Hreflang tags untuk multilingual
- Open Graph meta tags
- Schema.org markup (Article)
- XML Sitemap
- robots.txt

### Security
- Google OAuth untuk admin authentication
- CSRF protection
- HTML sanitization
- Upload file validation
- Database dan storage tidak dapat diakses langsung

### Trash & Recovery
- Soft delete dengan backup ke trash
- Restore dari trash
- Permanent delete

## Instalasi

### Prerequisites

- Docker Desktop
- Domain (untuk production)
- Google OAuth Client credentials

### Setup Development

1. Clone repository:
```bash
git clone <repository-url>
cd dkwibowo
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Konfigurasi `.env`:
```env
APP_ENV=local
APP_URL=http://localhost:8080
APP_TIMEZONE=Asia/Jakarta
DB_PATH=/var/www/html/database/indexer.sqlite

GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8080/admin/oauth/callback
ADMIN_EMAILS=admin@example.com

UPLOAD_MAX_SIZE=2097152
DEFAULT_PER_PAGE=5
MAX_PER_PAGE=50
```

4. Install dependencies:
```bash
composer install
```

5. Start dengan Docker:
```bash
docker compose up -d --build
```

6. Akses aplikasi:
- Website: http://localhost:8080
- Admin: http://localhost:8080/admin

Migration database akan berjalan otomatis saat container start.

## Deployment

### Production Setup

1. Setup environment variables di `.env`:
```env
APP_ENV=production
APP_URL=https://your-domain.com
GOOGLE_REDIRECT_URI=https://your-domain.com/admin/oauth/callback
```

2. Build dan start container:
```bash
docker compose up -d --build
docker compose logs -f app
```

3. Verifikasi deployment:
```bash
curl -f https://your-domain.com/health
curl -f https://your-domain.com/sitemap.xml
curl -f https://your-domain.com/robots.txt
```

### Backup

Backup harus mencakup:
- `database/indexer.sqlite` - Database
- `content/` - Markdown files
- `storage/` - Media files

```bash
docker compose down
cp database/indexer.sqlite database/indexer.sqlite.backup
tar -czf backup-$(date +%Y%m%d).tar.gz content/ storage/ database/
```

## Konfigurasi Google OAuth

1. Buat project di [Google Cloud Console](https://console.cloud.google.com/)
2. Enable Google+ API
3. Buat OAuth 2.0 credentials (Web application)
4. Authorized redirect URIs:
   - Development: `http://localhost:8080/admin/oauth/callback`
   - Production: `https://your-domain.com/admin/oauth/callback`
5. Copy Client ID dan Client Secret ke `.env`

## URL Structure

### Public Routes
```
/                           # Homepage (redirect ke /id/ atau /en/)
/{lang}/                    # Homepage dalam bahasa tertentu
/{lang}/project/{slug}      # Detail project
/{lang}/tag/{slug}          # Halaman tag
/{lang}/search              # Search results
/sitemap.xml               # XML Sitemap
/robots.txt                # Robots.txt
```

### Admin Routes
```
/admin                      # Admin dashboard
/admin/login               # Login page
/admin/oauth/callback      # OAuth callback
/admin/content             # Content management
/admin/content/create      # Create new content
/admin/content/{id}/edit   # Edit content
/admin/trash               # Trash management
```

## Content Lifecycle

```
Draft → Published → Archived
  ↓         ↓          ↓
Trash → Restore → Permanent Delete
```

- **Draft**: Tidak visible di public, dapat diedit
- **Published**: Visible di public dengan `published_at` timestamp
- **Archived**: Tidak visible di public, dapat direstore
- **Trash**: Soft deleted dengan backup, dapat direstore atau permanent delete

## Testing

Run smoke tests:
```bash
docker compose exec app php tests/smoke.php
```

## Dokumentasi

Dokumentasi lengkap tersedia di direktori `docs/`:

- `docs/FSD.md` - Functional Specification Document
- `docs/TSD.md` - Technical Specification Document
- `docs/design.md` - Design System dan Visual Guidelines
- `docs/DEPLOYMENT.md` - Deployment Runbook
- `docs/plan/` - Implementation phases

## Contact

- Email: me@dkwibowo.my.id
- WhatsApp: +62811-341-6622
- LinkedIn: [linkedin.com/in/dirgahayu](https://www.linkedin.com/in/dirgahayu/)
- GitHub: [github.com/dirg-bisma](https://github.com/dirg-bisma)

## License

Proprietary
