<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Support\DefaultPages;
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
                    $n = DefaultPages::seed();
                    Notification::make()
                        ->title($n ? "Created {$n} page(s)" : 'All default pages already exist')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->label('New page')
                // Force a real navigation link (avoids broken Livewire-only create)
                ->url(fn (): string => PageResource::getUrl('create'))
                ->openUrlInNewTab(false),
        ];
    }
}
