<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Concerns\HasContinueFormDraftAction;
use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Models\OpenSourceAlternative;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListOpenSourceAlternatives extends ListRecords
{
    use HasContinueFormDraftAction;

    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return array_values(array_filter([
            $this->getContinueFormDraftAction(),
            Actions\CreateAction::make(),
        ]));
    }

    protected function getTableQuery(): Builder
    {
        return OpenSourceAlternative::query()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public function getTabs(): array
    {
        // $query name is required — Filament injects by parameter name.
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
