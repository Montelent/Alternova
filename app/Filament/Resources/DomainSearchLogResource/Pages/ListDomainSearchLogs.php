<?php

namespace App\Filament\Resources\DomainSearchLogResource\Pages;

use App\Filament\Resources\DomainSearchLogResource;
use App\Models\DomainSearchLog;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListDomainSearchLogs extends ListRecords
{
    protected static string $resource = DomainSearchLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DomainSearchLogResource\Widgets\DomainSearchStats::class,
        ];
    }
}
