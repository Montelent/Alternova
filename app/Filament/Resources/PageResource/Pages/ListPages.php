<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('seedDefaults')
                ->label('Seed default pages')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Create About, Privacy, Terms, Disclosure?')
                ->modalDescription('Only creates pages that do not already exist. Existing pages are left unchanged.')
                ->action(function () {
                    $n = \App\Support\DefaultPages::seed();
                    Notification::make()
                        ->title($n ? "Created {$n} page(s)" : 'All default pages already exist')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
