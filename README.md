# Alternova

**Find open-source alternatives. Generate brandable domains.**

Production-ready Laravel 11 product: Open Source Alternative Finder + Domain Name Idea Combinator, with Filament admin, SEO tools, email digests, and shared-hosting friendly install.

## Requirements

- PHP 8.2+
- MySQL or PostgreSQL
- Composer
- Writable `storage/` and `bootstrap/cache/`

## Install (any domain / host)

1. Upload or clone the project into the web root your host assigns (often `public_html` or `httpdocs`).
2. Point the domain document root at that folder (this package is designed so `index.php` lives at the app root).
3. Run:

```bash
composer install --no-dev --optimize-autoloader
```

4. Open `https://YOUR-DOMAIN/install` and complete the wizard (creates `.env`, APP_KEY, migrations, admin user, locks the installer).

Paths, `APP_URL`, PHP binary for cron, and sitemap URLs are detected from **this install** — nothing is tied to a specific host brand or seller domain.

## After install

- **Admin:** `/admin`
- **Cron settings:** Admin → System → Cron settings (copy the auto-detected command into your host’s cron, every minute)
- **SEO / Email / Ads:** Admin → System settings pages
- Optional starter catalog: Admin → System tools → Seed demo data (real OSS projects, optional)

## Optional environment

| Variable | Purpose |
|----------|---------|
| `GITHUB_TOKEN` | Higher rate limits for metrics sync |
| `RESEND_KEY` or SMTP via Admin | Outbound email |

## License

MIT (see repository license file if present).
