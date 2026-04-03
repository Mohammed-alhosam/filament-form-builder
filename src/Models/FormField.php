<?php

namespace Alhosam\FilamentFormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class FormField extends Model
{
    use HasTranslations;

    protected $table = 'form_builder_fields';

    public array $translatable = [
        'label',
        'placeholder',
        'help_text',
        'default_value',
    ];

    protected $fillable = [
        'uuid',
        'form_id',
        'parent_id',
        'type',
        'key',
        'label',
        'placeholder',
        'help_text',
        'default_value',
        'is_required',
        'width',
        'sort_order',
        'validation_rules',
        'options',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'placeholder' => 'array',
            'help_text' => 'array',
            'default_value' => 'array',
            'is_required' => 'boolean',
            'validation_rules' => 'array',
            'options' => 'array',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $field): void {
            if (blank($field->uuid)) {
                $field->uuid = (string) Str::uuid();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function displayLabel(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = array_filter((array) $this->getTranslations('label'));

        return (string) (
            $translations[$locale]
            ?? $translations[(string) config('app.fallback_locale')]
            ?? reset($translations)
            ?? $this->key
        );
    }
}
