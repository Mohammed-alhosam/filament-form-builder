<?php

namespace Alhosam\FilamentFormBuilder\Filament\Plugins;

use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\FormResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;

class FilamentFormBuilderPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-form-builder';
    }

    public function register(Panel $panel): void
    {
        $panel->assets([
            Css::make('filament-form-builder-package', __DIR__.'/../../../resources/css/filament-form-builder.css'),
        ], package: 'alhosam/filament-form-builder');

        $panel->resources([
            FormResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
