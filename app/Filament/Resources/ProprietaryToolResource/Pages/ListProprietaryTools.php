<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Resources\ProprietaryToolResource;
use App\Models\ProprietaryTool;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListProprietaryTools extends ListRecords
{
    protected static string $resource = ProprietaryToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Always return a real Eloquent builder with a model (see OpenSource list).
     */
    protected function getTableQuery(): Builder
    {
        return ProprietaryTool::query()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->withAlternativesTotalCount();
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
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereNotNull(
                    $q->getModel()->getQualifiedDeletedAtColumn()
                )),
        ];
    }
}
