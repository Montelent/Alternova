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
                Section::make('Import alternatives CSV')
                    ->schema([
                        Placeholder::make('cols_alt')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300">'
                                .'Columns: <code>name</code>, <code>slug</code>, <code>description</code>, <code>repo_url</code>, <code>website_url</code>, '
                                .'<code>license_type</code>, <code>primary_language</code>, <code>self_host_difficulty</code>, '
                                .'<code>is_published</code>, <code>proprietary_slugs</code> (comma-separated).'
                                .'</p>'
                            )),
                        FileUpload::make('csv_file')
                            ->label('Alternatives CSV')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', '.csv'])
                            ->disk('local')
                            ->directory('catalog-imports')
                            ->visibility('private')
                            ->maxSize(5120),
                    ]),
                Section::make('Import proprietary tools CSV')
                    ->schema([
                        Placeholder::make('cols_tool')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300">'
                                .'Columns: <code>name</code>, <code>slug</code>, <code>website_url</code>, <code>description</code>, '
                                .'<code>target_audience</code>, <code>key_features</code> (pipe or comma separated), <code>is_published</code>.'
                                .'</p>'
                            )),
                        FileUpload::make('tools_csv_file')
                            ->label('Tools CSV')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', '.csv'])
                            ->disk('local')
                            ->directory('catalog-imports')
                            ->visibility('private')
                            ->maxSize(5120),
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
                'name', 'slug', 'description', 'repo_url', 'website_url',
                'license_type', 'primary_language', 'self_host_difficulty',
                'is_published', 'proprietary_slugs',
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
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportToolsCsv(): StreamedResponse
    {
        $filename = 'proprietary-tools-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'name', 'slug', 'website_url', 'description',
                'target_audience', 'key_features', 'is_published',
            ]);

            ProprietaryTool::query()->orderBy('name')->chunk(100, function ($rows) use ($out) {
                foreach ($rows as $tool) {
                    $features = $tool->key_features;
                    if (is_array($features)) {
                        $features = implode('|', $features);
                    }
                    $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $tool->description)) ?? '');
                    fputcsv($out, [
                        $tool->name,
                        $tool->slug,
                        $tool->website_url,
                        mb_substr($plain, 0, 2000),
                        $tool->target_audience,
                        $features,
                        $tool->is_published ? 1 : 0,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importCsv(): void
    {
        $this->runImport('csv_file', 'alternatives');
    }

    public function importToolsCsv(): void
    {
        $this->runImport('tools_csv_file', 'tools');
    }

    protected function runImport(string $field, string $type): void
    {
        $this->importReport = '';
        $state = $this->form->getState();
        $path = $state[$field] ?? null;

        if (! $path) {
            Notification::make()->title('Choose a CSV file first')->warning()->send();

            return;
        }

        $full = $this->resolveUploadPath((string) $path);
        if (! $full) {
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

            try {
                if ($type === 'tools') {
                    [$c, $u, $s] = $this->importToolRow($data, $rowNum, $errors);
                } else {
                    [$c, $u, $s] = $this->importAlternativeRow($data, $rowNum, $errors);
                }
                $created += $c;
                $updated += $u;
                $skipped += $s;
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

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $errors
     * @return array{0: int, 1: int, 2: int}
     */
    protected function importAlternativeRow(array $data, int $rowNum, array &$errors): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '' && $name !== '') {
            $slug = Str::slug($name);
        }
        if ($name === '' || $slug === '') {
            $errors[] = "Row {$rowNum}: missing name/slug";

            return [0, 0, 1];
        }

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

        $alt = OpenSourceAlternative::query()->where('slug', $slug)->first();
        if ($alt) {
            $alt->fill($payload)->save();
            $updated = 1;
            $created = 0;
        } else {
            $alt = OpenSourceAlternative::query()->create($payload);
            $created = 1;
            $updated = 0;
        }

        $toolSlugs = array_filter(array_map('trim', explode(',', (string) ($data['proprietary_slugs'] ?? ''))));
        if ($toolSlugs !== [] && Schema::hasTable('proprietary_tools')) {
            $ids = ProprietaryTool::query()->whereIn('slug', $toolSlugs)->pluck('id')->all();
            if (method_exists($alt, 'proprietaryTools')) {
                $alt->proprietaryTools()->sync(array_slice($ids, 0, 5));
            }
        }

        return [$created, $updated, 0];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $errors
     * @return array{0: int, 1: int, 2: int}
     */
    protected function importToolRow(array $data, int $rowNum, array &$errors): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '' && $name !== '') {
            $slug = Str::slug($name);
        }
        if ($name === '' || $slug === '') {
            $errors[] = "Row {$rowNum}: missing name/slug";

            return [0, 0, 1];
        }

        $featuresRaw = trim((string) ($data['key_features'] ?? ''));
        $features = [];
        if ($featuresRaw !== '') {
            $parts = preg_split('/[|,]/', $featuresRaw) ?: [];
            $features = array_values(array_filter(array_map('trim', $parts)));
        }

        $payload = [
            'name' => mb_substr($name, 0, 200),
            'slug' => mb_substr($slug, 0, 180),
            'website_url' => mb_substr(trim((string) ($data['website_url'] ?? '')), 0, 500) ?: null,
            'description' => (string) ($data['description'] ?? ''),
            'target_audience' => mb_substr(trim((string) ($data['target_audience'] ?? '')), 0, 200) ?: null,
            'key_features' => $features,
            'is_published' => in_array(strtolower((string) ($data['is_published'] ?? '0')), ['1', 'true', 'yes', 'on'], true),
        ];

        $tool = ProprietaryTool::query()->where('slug', $slug)->first();
        if ($tool) {
            $tool->fill($payload)->save();

            return [0, 1, 0];
        }

        ProprietaryTool::query()->create($payload);

        return [1, 0, 0];
    }

    protected function resolveUploadPath(string $path): ?string
    {
        $candidates = [
            storage_path('app/'.$path),
            storage_path('app/private/'.$path),
            storage_path('app/catalog-imports/'.basename($path)),
            $path,
        ];

        foreach ($candidates as $full) {
            if (is_file($full)) {
                return $full;
            }
        }

        return null;
    }
}
