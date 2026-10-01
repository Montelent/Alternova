<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view')
                ->label('View on site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => $this->record->publicUrl(), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->is_published),
            Actions\DeleteAction::make(),
        ];
    }
}
