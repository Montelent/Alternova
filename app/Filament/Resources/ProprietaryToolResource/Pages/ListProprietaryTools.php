<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Concerns\HasContinueFormDraftAction;
use App\Filament\Resources\ProprietaryToolResource;
use App\Models\ProprietaryTool;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListProprietaryTools extends ListRecords
{
    use HasContinueFormDraftAction;

    protected static string $resource = ProprietaryToolResource::class;

    protected function getHeaderActions(): array
    {
        return array_values(array_filter([
            $this->getContinueFormDraftAction(),
            Actions\CreateAction::make(),
        ]));
    }

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
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('is_published', true)
                    ->whereNull('deleted_at')),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('is_published', false)
                    ->whereNull('deleted_at')),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at')),
        ];
    }
}
