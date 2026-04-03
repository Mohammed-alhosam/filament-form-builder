<?php

namespace Alhosam\FilamentFormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FormSubmission extends Model
{
    protected $table = 'form_builder_submissions';

    protected $fillable = [
        'uuid',
        'form_id',
        'status',
        'user_id',
        'ip_address',
        'user_agent',
        'locale',
        'submitted_at',
        'payload',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'payload' => 'array',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if (blank($submission->uuid)) {
                $submission->uuid = (string) Str::uuid();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
