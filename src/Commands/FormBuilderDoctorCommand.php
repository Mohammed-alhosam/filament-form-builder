<?php

namespace Alhosam\FilamentFormBuilder\Commands;

use Alhosam\FilamentFormBuilder\Services\FormBuilderRegistry;
use Alhosam\FilamentFormBuilder\Services\FormFieldTypeRegistry;
use Illuminate\Console\Command;

class FormBuilderDoctorCommand extends Command
{
    protected $signature = 'form-builder:doctor';

    protected $description = 'Run basic diagnostics for the Filament Form Builder package.';

    public function handle(): int
    {
        $fieldTypes = array_keys(app(FormFieldTypeRegistry::class)->all());
        $bindings = app(FormBuilderRegistry::class)->bindingOptions();

        $this->components->info('Filament Form Builder package is installed.');
        $this->line('Navigation group: '.config('filament-form-builder.navigation_group'));
        $this->line('Default status: '.config('filament-form-builder.default_status'));
        $this->line('Field types: '.(empty($fieldTypes) ? 'none' : implode(', ', $fieldTypes)));
        $this->line('Binding types: '.(empty($bindings) ? 'none' : implode(', ', array_keys($bindings))));

        return self::SUCCESS;
    }
}
