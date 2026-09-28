<?php

namespace App\Filament\Resources\IssueReportResource\Pages;

use App\Filament\Resources\IssueReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssueReport extends EditRecord
{
    protected static string $resource = IssueReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
