<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Models\OpenSourceAlternative;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListOpenSourceAlternatives extends ListRecords
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Always return a real Eloquent builder with a model.
     */
    protected function getTableQuery(): Builder
    {
        return OpenSourceAlternative::query()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public function getTabs(): array
    {
        // Parameter MUST be named $query — Filament injects by name.
        // Use deleted_at directly (SoftDeletingScope is already removed on the base query).
        return [
            'all' => Tab::make('All')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('is_published', true)
                    ->whereNull('deleted_at')),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('is_published', false)
                    ->whereNull('deleted_at')),
            'broken' => Tab::make('Broken links')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where(function (Builder $inner) {
                        $inner->where('repo_reachable', false)
                            ->orWhere('website_reachable', false);
                    })
                    ->whereNull('deleted_at')),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at')),
        ];
    }
}
