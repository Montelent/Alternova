<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOpenSourceAlternatives extends ListRecords
{
    protected static string $resource = OpenSourceAlternativeResource::class;

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
            'broken' => Tab::make('Broken links')
                ->modifyQueryUsing(fn (Builder $q) => $q->where(function (Builder $inner) {
                    $inner->where('repo_reachable', false)
                        ->orWhere('website_reachable', false);
                })),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $q) => $q->onlyTrashed()),
        ];
    }
}
