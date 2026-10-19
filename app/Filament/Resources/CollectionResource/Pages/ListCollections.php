<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\HasContinueFormDraftAction;
use App\Filament\Resources\CollectionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCollections extends ListRecords
{
    use HasContinueFormDraftAction;

    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return array_values(array_filter([
            $this->getContinueFormDraftAction(),
            Actions\CreateAction::make(),
        ]));
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true)),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', false)),
        ];
    }
}
