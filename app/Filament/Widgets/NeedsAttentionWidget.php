<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\NeedsAttentionPage;
use Filament\Widgets\Widget;

class NeedsAttentionWidget extends Widget
{
    protected static string $view = 'filament.widgets.needs-attention';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function count(): int
    {
        return NeedsAttentionPage::totalCount();
    }

    public function cards(): array
    {
        return collect(app(NeedsAttentionPage::class)->summary())
            ->filter(fn ($c) => ($c['count'] ?? 0) > 0)
            ->values()
            ->all();
    }
}
