<?php

namespace Alhosam\FilamentFormBuilder;

use Alhosam\FilamentFormBuilder\Commands\FormBuilderDoctorCommand;
use Alhosam\FilamentFormBuilder\Livewire\RenderForm;
use Alhosam\FilamentFormBuilder\Services\FormBuilderRegistry;
use Alhosam\FilamentFormBuilder\Services\FormFieldTypeRegistry;
use Alhosam\FilamentFormBuilder\Services\FormSubmissionGuard;
use Alhosam\FilamentFormBuilder\Services\FormSubmissionService;
use Alhosam\FilamentFormBuilder\Support\FormBuilderDefaults;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentFormBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-form-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations()
            ->hasCommand(FormBuilderDoctorCommand::class);
    }

    public function packageBooted(): void
    {
        $this->mergeConfigFrom(
            $this->getPackageBaseDir().'/config/filament-form-builder.php',
            'filament-form-builder',
        );

        $this->loadMigrationsFrom($this->getPackageBaseDir().'/database/migrations');
        $this->loadViewsFrom($this->getPackageBaseDir().'/resources/views', 'filament-form-builder');

        $this->app->singleton(FormBuilderRegistry::class);
        $this->app->singleton(FormFieldTypeRegistry::class);
        $this->app->singleton(FormSubmissionGuard::class);
        $this->app->singleton(FormSubmissionService::class);

        foreach (FormBuilderDefaults::fieldTypes() as $key => $definition) {
            $this->app->make(FormFieldTypeRegistry::class)->register($key, $definition);
        }

        Blade::componentNamespace('Alhosam\\FilamentFormBuilder\\View\\Components', 'filament-form-builder');
        Livewire::component('filament-form-builder.render-form', RenderForm::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                FormBuilderDoctorCommand::class,
            ]);

            $this->publishes([
                $this->getPackageBaseDir().'/config/filament-form-builder.php' => config_path('filament-form-builder.php'),
            ], 'filament-form-builder-config');

            $this->publishes($this->migrationPublishPaths(), 'filament-form-builder-migrations');
        }
    }

    protected function getPackageBaseDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @return array<string, string>
     */
    protected function migrationPublishPaths(): array
    {
        return collect(glob($this->getPackageBaseDir().'/database/migrations/*.php') ?: [])
            ->mapWithKeys(fn (string $path): array => [
                $path => database_path('migrations/'.basename($path)),
            ])
            ->all();
    }
}
