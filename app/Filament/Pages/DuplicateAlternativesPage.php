<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Services\DuplicateAlternativeService;
use Filament\Pages\Page;

class DuplicateAlternativesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Duplicates';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 26;

    protected static string $view = 'filament.pages.duplicate-alternatives';

    protected static ?string $title = 'Duplicate detector';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function sameRepoGroups()
    {
        return app(DuplicateAlternativeService::class)->sameRepoGroups();
    }

    public function similarPairs()
    {
        return app(DuplicateAlternativeService::class)->similarNamePairs(85.0);
    }

    public function editUrl(int $id): string
    {
        return OpenSourceAlternativeResource::getUrl('edit', ['record' => $id]);
    }
}
