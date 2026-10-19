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
     * Prevents "newQueryWithoutRelationships() on null" when Filament
     * applies nested where() filters on Livewire updates (tabs/filters).
     */
    protected function getTableQuery(): Builder
    {
        return OpenSourceAlternative::query()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereNull(
                    $q->getModel()->getQualifiedDeletedAtColumn()
                )),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $q) => $q
                    ->where('is_published', true)
                    ->whereNull($q->getModel()->getQualifiedDeletedAtColumn())),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $q) => $q
                    ->where('is_published', false)
                    ->whereNull($q->getModel()->getQualifiedDeletedAtColumn())),
            'broken' => Tab::make('Broken links')
                ->modifyQueryUsing(fn (Builder $q) => $q
                    ->where(function (Builder $inner) {
                        $inner->where('repo_reachable', false)
                            ->orWhere('website_reachable', false);
                    })
                    ->whereNull($q->getModel()->getQualifiedDeletedAtColumn())),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereNotNull(
                    $q->getModel()->getQualifiedDeletedAtColumn()
                )),
        ];
    }
}
