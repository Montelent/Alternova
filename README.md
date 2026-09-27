# Alternova

**Find open-source alternatives. Generate brandable domains.**

Alternova is a production-ready Laravel 11 platform with two core tools:

1. **Open Source Alternative Finder** – Discover high-quality, self-hostable open-source alternatives to proprietary software, with live GitHub metrics, health scoring, Docker blueprints, and SEO-optimized detail pages.
2. **Domain Name Idea Combinator** – Generate brandable domain combinations from seed keywords, score them for brandability, check availability via DNS + RDAP, and export results with affiliate links.

## Stack

- **Framework**: Laravel 11 (PHP 8.3+)
- **Frontend**: Tailwind CSS, Alpine.js, Laravel Livewire v3
- **Database**: PostgreSQL (MySQL also supported in installer)
- **Search**: Laravel Scout + Meilisearch
- **Queue & Cache**: Redis
- **Admin**: Filament v3
- **Icons**: Heroicons

## Features

### Open Source Alternative Finder
- Queued GitHub metrics sync (`SyncGitHubMetricsJob`)
- Artisan command `app:sync-metrics` (scheduled daily)
- Livewire search with Scout/Meilisearch, filters, sorting & pagination
- Detail pages with metrics, feature comparison, Docker Compose + copy, deploy buttons, Schema.org markup, SEO footer

### Domain Name Idea Combinator
- Combinatorial generation + brandability scoring
- DNS + RDAP availability checks with 24h Redis cache
- Livewire UI with chip keywords, TLD filters, sliders, live status, CSV/TXT export

### Admin (Filament v3)
- CRUD for proprietary tools & open-source alternatives
- Manual GitHub sync action
- **System Tools** – run migrations, status, clear caches, optimize from the panel

### First-time Installer (locks after setup)
- `/install` wizard:
  1. Server requirements
  2. Auto-create `.env` + generate `APP_KEY` + save DB credentials
  3. Run migrations
  4. Create admin account
  5. Lock installer (`storage/app/installed`)
- After install, `/install` returns 403

## Getting Started

```bash
git clone https://github.com/Montelent/Alternova.git
cd Alternova
composer install
# Optional until installer creates .env:
# cp .env.example .env && php artisan key:generate
npm install && npm run build
php artisan serve
```

Open **http://localhost:8000/install** and complete the wizard.

Then log in at `/admin`.

### System Tools (Admin)

**System → System Tools**

- Run Migrations (`migrate --force`)
- Migration Status
- Clear Caches
- Optimize

### Environment (set by installer or manually)

```env
APP_NAME=Alternova
DB_CONNECTION=pgsql   # or mysql
REDIS_CLIENT=phpredis
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
GITHUB_TOKEN=
```

### Queue

```bash
php artisan queue:work redis --queue=default
```

## License

MIT
