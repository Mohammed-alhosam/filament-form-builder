<?php

namespace Alhosam\FilamentFormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Form extends Model
{
    use HasTranslations;

    protected $table = 'form_builder_forms';

    public array $translatable = [
        'name',
        'description',
        'success_message',
        'submit_label',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'status',
        'is_active',
        'success_message',
        'submit_label',
        'settings',
        'notifications',
        'availability',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'success_message' => 'array',
            'submit_label' => 'array',
            'settings' => 'array',
            'notifications' => 'array',
            'availability' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $form): void {
            if (blank($form->uuid)) {
                $form->uuid = (string) Str::uuid();
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id')->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id')->latest('submitted_at');
    }

    public function bindings(): HasMany
    {
        return $this->hasMany(FormBinding::class, 'form_id');
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = array_filter((array) $this->getTranslations('name'));

        return (string) (
            $translations[$locale]
            ?? $translations[(string) config('app.fallback_locale')]
            ?? reset($translations)
            ?? $this->slug
        );
    }
}
