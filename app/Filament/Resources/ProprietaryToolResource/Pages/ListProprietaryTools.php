<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Resources\ProprietaryToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProprietaryTools extends ListRecords
{
    protected static string $resource = ProprietaryToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('is_published', true)),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('is_published', false)),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $q) => $q->onlyTrashed()),
        ];
    }
}
