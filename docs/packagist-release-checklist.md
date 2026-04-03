# Packagist Release Checklist

Use this checklist when publishing or updating the package on Packagist.

## 1. Prepare the release locally

Run the package checks from the package root:

```bash
composer install
composer test
vendor/bin/pint --test
```

Verify the GitHub workflows are green:

- `tests`
- `smoke-install`

## 2. Update release metadata

Before tagging a release:

- update `CHANGELOG.md`
- confirm `README.md` reflects the current installation and rendering steps
- confirm the supported stack still matches Laravel 12 and Filament v4

## 3. Create the Git tag

Example:

```bash
git tag v0.1.1
git push origin v0.1.1
```

## 4. Connect the repository to Packagist

If the package is not connected yet:

1. Open [Packagist](https://packagist.org/packages/submit)
2. Submit the repository URL:
   - `https://github.com/Mohammed-alhosam/filament-form-builder`
3. Confirm the detected package name:
   - `alhosam/filament-form-builder`

## 5. Enable automatic updates

Inside the Packagist package page:

1. open `Settings`
2. copy the Packagist webhook URL
3. add it to the GitHub repository:
   - `Settings`
   - `Webhooks`
   - `Add webhook`

## 6. Create the GitHub release

For each new version:

- create a GitHub release from the tag
- paste the matching `CHANGELOG.md` notes

## 7. Validate public installation

After the package appears on Packagist, verify:

```bash
composer require alhosam/filament-form-builder
php artisan vendor:publish --tag=filament-form-builder-config
php artisan vendor:publish --tag=filament-form-builder-migrations
php artisan migrate
php artisan filament:assets
php artisan form-builder:doctor
```
