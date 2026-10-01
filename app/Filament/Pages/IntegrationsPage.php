<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\IntegrationsSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class IntegrationsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Integrations';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 13;

    protected static string $view = 'filament.pages.integrations';

    protected static ?string $title = 'Integrations';

    public ?array $data = [];

    public string $lastOutput = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'github_token' => SiteSetting::get('github_token', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('GitHub')
                    ->description('Used when syncing stars, forks, issues, and health scores. Works on any host — no .env edit required.')
                    ->schema([
                        Placeholder::make('github_help')
                            ->label('How to create a token')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<ol class="list-decimal list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">'
                                .'<li>GitHub → Settings → Developer settings → Personal access tokens</li>'
                                .'<li>Classic token: enable <code class="text-xs">public_repo</code> (or fine-grained: read public repositories)</li>'
                                .'<li>Paste the token below and save</li>'
                                .'</ol>'
                            )),
                        TextInput::make('github_token')
                            ->label('GitHub personal access token')
                            ->password()
                            ->revealable()
                            ->helperText('Stored in the database (site settings). Leave blank to keep using GITHUB_TOKEN from .env if set.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $token = trim((string) ($this->form->getState()['github_token'] ?? ''));
        SiteSetting::set('github_token', $token);
        IntegrationsSettings::apply();

        Notification::make()
            ->title('Integrations saved')
            ->success()
            ->send();
    }

    public function testGithub(): void
    {
        $this->save();
        IntegrationsSettings::apply();

        $token = IntegrationsSettings::githubToken();
        if (! $token) {
            Notification::make()->title('No GitHub token configured')->warning()->send();

            return;
        }

        try {
            $response = Http::timeout(12)
                ->withToken($token)
                ->withHeaders(['Accept' => 'application/vnd.github+json', 'User-Agent' => 'Alternova'])
                ->get('https://api.github.com/rate_limit');

            if (! $response->successful()) {
                $this->lastOutput = 'HTTP '.$response->status().': '.$response->body();
                Notification::make()->title('GitHub test failed')->body('HTTP '.$response->status())->danger()->send();

                return;
            }

            $core = $response->json('resources.core');
            $remaining = $core['remaining'] ?? '?';
            $limit = $core['limit'] ?? '?';
            $this->lastOutput = "OK — core rate limit remaining: {$remaining} / {$limit}";
            Notification::make()
                ->title('GitHub token works')
                ->body("Rate limit remaining: {$remaining} / {$limit}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('GitHub test failed')->body($e->getMessage())->danger()->send();
        }
    }
}
