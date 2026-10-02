<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\ImageField;
use Filament\Tables;

trait HasAlternativeLogoFields
{
    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function logoFormFields(): array
    {
        return ImageField::make('logo_path', 'Logo', 'logos', true, 'external_logo_url');
    }

    public static function logoTableColumn(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('logo_path')
            ->label('Logo')
            ->getStateUsing(fn ($record) => $record->logo_url)
            ->circular()
            ->defaultImageUrl(fn () => 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><rect width="40" height="40" rx="8" fill="#e2e8f0"/><text x="50%" y="54%" text-anchor="middle" fill="#64748b" font-size="14" font-family="sans-serif">?</text></svg>'))
            ->toggleable();
    }
}
