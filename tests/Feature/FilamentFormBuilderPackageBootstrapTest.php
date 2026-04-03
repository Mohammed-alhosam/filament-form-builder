<?php

namespace Alhosam\FilamentFormBuilder\Tests\Feature;

use Alhosam\FilamentFormBuilder\Commands\FormBuilderDoctorCommand;
use Alhosam\FilamentFormBuilder\Filament\Plugins\FilamentFormBuilderPlugin;
use Alhosam\FilamentFormBuilder\Services\FormBuilderRegistry;
use Alhosam\FilamentFormBuilder\Services\FormFieldTypeRegistry;
use Alhosam\FilamentFormBuilder\Tests\TestCase;

class FilamentFormBuilderPackageBootstrapTest extends TestCase
{
    public function test_package_services_and_plugin_can_be_resolved(): void
    {
        $this->assertTrue($this->app->bound(FormBuilderRegistry::class));
        $this->assertTrue($this->app->bound(FormFieldTypeRegistry::class));
        $this->assertSame('filament-form-builder', FilamentFormBuilderPlugin::make()->getId());
    }

    public function test_doctor_command_is_registered(): void
    {
        $this->assertTrue($this->app->bound(FormBuilderDoctorCommand::class));
    }
}
