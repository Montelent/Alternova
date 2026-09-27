# Open Alt Finder + Domain Combinator

Production-ready Laravel 11 application housing two powerful tools:

1. **Open Source Alternative Finder** – Discover high-quality, self-hostable open-source alternatives to proprietary software with live GitHub metrics, health scoring, Docker blueprints, and SEO-optimized detail pages.
2. **Domain Name Idea Combinator** – Generate brandable domain combinations from seed keywords, score them for brandability, check availability via DNS + RDAP, and export results with affiliate links.

## Stack

- **Framework**: Laravel 11 (PHP 8.3+)
- **Frontend**: Tailwind CSS, Alpine.js, Laravel Livewire v3
- **Database**: PostgreSQL
- **Search**: Laravel Scout + Meilisearch
- **Queue & Cache**: Redis
- **Admin**: Filament v3
- **Icons**: Heroicons

## Features Implemented

### Task 1 – Database Architecture
- `ProprietaryTool`, `OpenSourceAlternative`, `RepoMetric`, `DomainSearchLog` models + migrations
- Spatie Tags for categories/features
- Factories and full Eloquent relationships
- Scout searchable arrays

### Task 2 – Open Source Alternative Finder
- `SyncGitHubMetricsJob` (queued, rate-limit friendly)
- `app:sync-metrics` artisan command (scheduled daily)
- Livewire `OpenSourceFinder` with Scout search, multi-select filters, sorting & pagination
- Livewire `AlternativeDetail` with metrics widgets, feature comparison, syntax-ready Docker Compose + copy button, affiliate deploy buttons, Schema.org SoftwareApplication markup, and rich SEO footer

### Task 3 – Domain Name Idea Combinator
- `DomainCombinatorService` – combinatorial generation + brandability scoring (length, syllables, metaphone, vowel balance)
- `DomainCheckService` – DNS fast path + RDAP fallback, 24 h Redis cache, affiliate link generator
- Livewire `DomainCombinator` – chip keywords, TLD multi-select, sliders, live status polling, CSV/TXT export

### Task 4 – Admin (Filament v3)
- Full CRUD resources for Proprietary Tools and Open Source Alternatives
- Manual “Sync GitHub” action on alternatives
- Docker Compose blueprint editor
- **System Tools** page – run migrations, migration status, clear caches, and optimize from the admin panel

### Task 5 – Performance & SEO
- Dynamic OpenGraph-ready layout support
- `SitemapService` for XML sitemap generation
- SEO-rich explanatory sections + FAQs on every alternative page

### First-time Installer (locked after setup)
- Visit `/install` on a fresh install
- Step 1: Server requirements check
- Step 2: Run database migrations from the browser
- Step 3: Create the admin account
- Step 4: Installer locks itself (`storage/app/installed`) — `/install` becomes inaccessible (403)
- All other routes redirect to the installer until installation is complete
- Local-only unlock route: `/install/unlock-dev` (only in `APP_ENV=local`)

## Getting Started

### Recommended: Web Installer

```bash
git clone https://github.com/Montelent/open-alt-finder.git
cd open-alt-finder
composer install
cp .env.example .env
php artisan key:generate

# Configure PostgreSQL, Redis, Meilisearch, and GitHub token in .env
npm install && npm run build
php artisan serve
```

Then open **http://localhost:8000/install** and complete the wizard.
After finishing, the installer is locked. Log in at `/admin` with the account you created.

### Alternative: CLI

```bash
php artisan migrate --seed
php artisan scout:import "App\Models\OpenSourceAlternative"
php artisan scout:import "App\Models\ProprietaryTool"
# Create admin user manually, then:
# touch storage/app/installed   # or visit the installer once
```

### System Tools (Admin)

In Filament → **System → System Tools** you can:

- **Run Migrations** — `php artisan migrate --force` (confirmation required)
- **Migration Status** — list which migrations have run
- **Clear Caches** — config, route, view, application cache
- **Optimize** — production cache warm-up

### Required Environment Variables

```env
DB_CONNECTION=pgsql
REDIS_CLIENT=phpredis
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
GITHUB_TOKEN=ghp_xxxxxxxx          # for higher rate limits
```

### Queue Worker

```bash
php artisan queue:work redis --queue=default
```

### Schedule

The `app:sync-metrics` command is registered to run daily at 03:00.

## Project Structure (key paths)

```
app/
├── Console/Commands/SyncMetricsCommand.php
├── Filament/
│   ├── Pages/SystemTools.php          # Run migrations from admin
│   └── Resources/...
├── Http/Middleware/
│   ├── EnsureNotInstalled.php         # Blocks /install when locked
│   └── RedirectIfNotInstalled.php     # Forces installer until done
├── Jobs/SyncGitHubMetricsJob.php
├── Livewire/
│   ├── InstallWizard.php              # First-time installer
│   ├── OpenSourceFinder.php
│   ├── AlternativeDetail.php
│   └── DomainCombinator.php
├── Models/...
├── Services/...
└── Support/Installer.php              # Lock file + artisan helper
```

## License

MIT
