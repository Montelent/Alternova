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

    /**
     * @return list<array{repo: string, items: list<array{id: int, name: string, published: bool, tool: string|null}>}>
     */
    public function sameRepoGroups(): array
    {
        try {
            return app(DuplicateAlternativeService::class)->sameRepoGroupsForView();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{score: float, a: array{id: int, name: string}, b: array{id: int, name: string}>
     */
    public function similarPairs(): array
    {
        try {
            return app(DuplicateAlternativeService::class)->similarNamePairsForView(85.0);
        } catch (\Throwable) {
            return [];
        }
    }

    public function editUrl(int $id): string
    {
        return OpenSourceAlternativeResource::getUrl('edit', ['record' => $id]);
    }
}
