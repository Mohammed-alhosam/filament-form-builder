# Filament Form Builder

Reusable form builder package for Laravel 12 and Filament v4.

## Features

- reusable forms with translatable names, descriptions, and submit labels
- single-screen Filament workspace for fields, bindings, notifications, and preview
- frontend Blade and Livewire renderer
- submission storage with payload and request metadata
- generic host bindings for contexts such as event registration
- availability controls for auth and time windows
- honeypot and rate limiting hooks for public forms
- field type registry for future extensibility
- doctor command and official Filament asset registration
- clean host bindings for contexts such as event registration or landing pages

## Installation

```bash
composer require alhosam/filament-form-builder
php artisan vendor:publish --tag=filament-form-builder-config
php artisan vendor:publish --tag=filament-form-builder-migrations
php artisan migrate
php artisan filament:assets
```

Register the plugin in your Filament panel:

```php
use Alhosam\FilamentFormBuilder\Filament\Plugins\FilamentFormBuilderPlugin;

->plugin(FilamentFormBuilderPlugin::make())
```

## Admin experience

The package provides:

- forms resource
- create and edit pages
- a unified form workspace page

The workspace keeps key management tasks in one place:

- fields
- recent submissions
- bindings
- notification settings
- live preview

## Frontend rendering

Render a form by slug:

```blade
<x-filament-form-builder::form slug="event-registration" />
```

Render a form by model instance:

```blade
<x-filament-form-builder::form :form="$form" />
```

## Security and availability

Public forms support:

- honeypot spam protection
- configurable rate limiting
- guest or authenticated-only submissions
- availability windows through `available_from` and `available_until`

These controls are managed through the package configuration and form settings.

## Host bindings

The package stays generic. The host app can register binding types:

```php
use Alhosam\FilamentFormBuilder\Services\FormBuilderRegistry;

app(FormBuilderRegistry::class)->registerBinding('events.registration', [
    'label' => 'Event registration',
    'bindable_type' => \App\Models\Event::class,
]);
```

This keeps project-specific models and workflows outside the package.

## Release workflow

This repository includes:

- `tests` GitHub Actions workflow
- `smoke-install` workflow for clean Laravel installation checks
- Packagist release checklist in `docs/packagist-release-checklist.md`

## Field types

Included field types:

- text
- textarea
- email
- phone
- number
- select
- radio
- checkbox
- checkbox_list
- date
- datetime
- file
- heading
- paragraph
- divider

## Included console command

```bash
php artisan form-builder:doctor
```

This prints:

- navigation group
- default status
- registered field types
- registered binding types

## Development

Run formatting:

```bash
vendor/bin/pint
```

Run tests:

```bash
vendor/bin/phpunit
```

## License

MIT
