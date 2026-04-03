<?php

namespace Alhosam\FilamentFormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormBinding extends Model
{
    protected $table = 'form_builder_bindings';

    protected $fillable = [
        'form_id',
        'bindable_type',
        'bindable_id',
        'context',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function bindable(): MorphTo
    {
        return $this->morphTo();
    }
}
