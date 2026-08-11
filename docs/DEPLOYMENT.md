# Deployment Runbook

## Prerequisites

- Docker Desktop aktif.
- Domain dan HTTPS certificate.
- Google OAuth client dengan callback URL production.

## Configuration

Copy `.env.example` menjadi `.env`, lalu isi minimal:

```env
APP_ENV=production
APP_URL=https://example.com
APP_TIMEZONE=Asia/Jakarta
DB_PATH=/var/www/html/database/indexer.sqlite
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://example.com/admin/oauth/callback
ADMIN_EMAILS=admin@example.com
UPLOAD_MAX_SIZE=2097152
DEFAULT_PER_PAGE=5
MAX_PER_PAGE=50
```

Jangan commit `.env` atau credential.

## Start

```bash
docker compose up -d --build
docker compose logs -f app
```

Migration database dijalankan otomatis oleh container entrypoint.

## Verification

```bash
curl -f https://example.com/health
curl -f https://example.com/sitemap.xml
curl -f https://example.com/robots.txt
docker compose exec app php tests/smoke.php
```

Pastikan document root Apache adalah `/var/www/html/public`. Directory `content/`, `database/`, dan `storage/` harus tetap berada di luar document root dan tidak boleh diekspos melalui HTTP.

## Stop and backup

```bash
docker compose down
cp database/indexer.sqlite database/indexer.sqlite.backup
```

Backup juga harus mencakup `content/` dan `storage/`.
