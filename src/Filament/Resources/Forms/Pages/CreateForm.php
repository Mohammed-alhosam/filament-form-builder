<?php

namespace Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages;

use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\FormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('workspace', [
            'record' => $this->getRecord(),
        ]);
    }
}
