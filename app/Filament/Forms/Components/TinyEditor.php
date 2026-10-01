<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class TinyEditor extends Field
{
    protected string $view = 'filament.forms.components.tiny-editor';

    protected int | string | null $height = 420;

    public function height(int | string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): int | string
    {
        return $this->height;
    }
}
