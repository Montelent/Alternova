<?php

namespace App\Filament\Pages;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogImportExportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Catalog CSV';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.catalog-import-export';

    protected static ?string $title = 'Catalog import / export';

    protected static ?string $slug = 'catalog-csv';

    public ?array $data = [];

    public string $importReport = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->isEditor();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Import CSV')
                    ->description('Upload a CSV exported from this page or another Alternova install. Matching rows update by slug; new slugs are created as drafts unless is_published=1.')
                    ->schema([
                        Placeholder::make('cols')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300">'
                                .'Columns: <code>name</code>, <code>slug</code>, <code>description</code>, <code>repo_url</code>, <code>website_url</code>, '
                                .'<code>license_type</code>, <code>primary_language</code>, <code>self_host_difficulty</code> (1–5), '
                                .'<code>is_published</code> (0/1), <code>proprietary_slugs</code> (comma-separated tool slugs).'
                                .'</p>'
                            )),
                        FileUpload::make('csv_file')
                            ->label('CSV file')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', '.csv'])
                            ->disk('local')
                            ->directory('catalog-imports')
                            ->visibility('private')
                            ->maxSize(5120)
                            ->helperText('Max 5 MB. UTF-8 CSV with a header row.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'alternatives-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'name',
                'slug',
                'description',
                'repo_url',
                'website_url',
                'license_type',
                'primary_language',
                'self_host_difficulty',
                'is_published',
                'proprietary_slugs',
            ]);

            OpenSourceAlternative::query()
                ->with('proprietaryTools:id,slug')
                ->orderBy('name')
                ->chunk(100, function ($rows) use ($out) {
                    foreach ($rows as $alt) {
                        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $alt->description)) ?? '');
                        fputcsv($out, [
                            $alt->name,
                            $alt->slug,
                            mb_substr($plain, 0, 2000),
                            $alt->repo_url,
                            $alt->website_url,
                            $alt->license_type,
                            $alt->primary_language,
                            $alt->self_host_difficulty,
                            $alt->is_published ? 1 : 0,
                            $alt->proprietaryTools->pluck('slug')->filter()->implode(','),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importCsv(): void
    {
        $this->importReport = '';
        $state = $this->form->getState();
        $path = $state['csv_file'] ?? null;

        if (! $path) {
            Notification::make()->title('Choose a CSV file first')->warning()->send();

            return;
        }

        $full = storage_path('app/'.$path);
        if (! is_file($full)) {
            // Livewire may store relative without prefix
            $full = storage_path('app/private/'.$path);
        }
        if (! is_file($full)) {
            $full = storage_path('app/catalog-imports/'.basename((string) $path));
        }
        if (! is_file($full)) {
            Notification::make()->title('Could not read uploaded file')->danger()->send();

            return;
        }

        $handle = fopen($full, 'r');
        if (! $handle) {
            Notification::make()->title('Unable to open CSV')->danger()->send();

            return;
        }

        $header = fgetcsv($handle);
        if (! is_array($header)) {
            fclose($handle);
            Notification::make()->title('Empty CSV')->danger()->send();

            return;
        }

        $header = array_map(fn ($h) => Str::slug((string) $h, '_'), $header);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = $row[$i] ?? '';
            }

            $name = trim((string) ($data['name'] ?? ''));
            $slug = trim((string) ($data['slug'] ?? ''));
            if ($slug === '' && $name !== '') {
                $slug = Str::slug($name);
            }
            if ($name === '' || $slug === '') {
                $skipped++;
                $errors[] = "Row {$rowNum}: missing name/slug";

                continue;
            }

            try {
                $alt = OpenSourceAlternative::query()->where('slug', $slug)->first();
                $payload = [
                    'name' => mb_substr($name, 0, 200),
                    'slug' => mb_substr($slug, 0, 180),
                    'description' => (string) ($data['description'] ?? ''),
                    'repo_url' => mb_substr(trim((string) ($data['repo_url'] ?? '')), 0, 500) ?: null,
                    'website_url' => mb_substr(trim((string) ($data['website_url'] ?? '')), 0, 500) ?: null,
                    'license_type' => mb_substr(trim((string) ($data['license_type'] ?? '')), 0, 80) ?: null,
                    'primary_language' => mb_substr(trim((string) ($data['primary_language'] ?? '')), 0, 80) ?: null,
                    'self_host_difficulty' => max(1, min(5, (int) ($data['self_host_difficulty'] ?? 3))),
                    'is_published' => in_array(strtolower((string) ($data['is_published'] ?? '0')), ['1', 'true', 'yes', 'on'], true),
                ];

                if ($alt) {
                    $alt->fill($payload)->save();
                    $updated++;
                } else {
                    $alt = OpenSourceAlternative::query()->create($payload);
                    $created++;
                }

                $toolSlugs = array_filter(array_map('trim', explode(',', (string) ($data['proprietary_slugs'] ?? ''))));
                if ($toolSlugs !== [] && Schema::hasTable('proprietary_tools')) {
                    $ids = ProprietaryTool::query()->whereIn('slug', $toolSlugs)->pluck('id')->all();
                    if (method_exists($alt, 'proprietaryTools')) {
                        $alt->proprietaryTools()->sync(array_slice($ids, 0, 5));
                    }
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        $this->importReport = "Created {$created}, updated {$updated}, skipped {$skipped}."
            .($errors !== [] ? "\n".implode("\n", array_slice($errors, 0, 15)) : '');

        Notification::make()
            ->title('Import finished')
            ->body("Created {$created}, updated {$updated}, skipped {$skipped}.")
            ->success()
            ->send();
    }
}
