<?php

namespace Alhosam\FilamentFormBuilder\Services;

use Alhosam\FilamentFormBuilder\Models\Form;
use Alhosam\FilamentFormBuilder\Models\FormSubmission;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FormSubmissionService
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     */
    public function submit(Form $form, array $payload, array $context = []): FormSubmission
    {
        return FormSubmission::create([
            'form_id' => $form->getKey(),
            'status' => (string) ($context['status'] ?? 'submitted'),
            'user_id' => $context['user_id'] ?? auth()->id(),
            'ip_address' => $context['ip_address'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? (string) request()->userAgent(),
            'locale' => $context['locale'] ?? app()->getLocale(),
            'submitted_at' => $context['submitted_at'] ?? now(),
            'payload' => $this->normalizePayload($payload),
            'meta' => (array) ($context['meta'] ?? []),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            if ($value instanceof TemporaryUploadedFile || $value instanceof UploadedFile) {
                $normalized[$key] = [
                    'disk' => (string) config('filament-form-builder.uploads.disk', 'public'),
                    'path' => $value->store(
                        (string) config('filament-form-builder.uploads.directory', 'form-builder'),
                        (string) config('filament-form-builder.uploads.disk', 'public'),
                    ),
                    'original_name' => $value->getClientOriginalName(),
                    'size' => $value->getSize(),
                    'mime_type' => $value->getMimeType(),
                ];

                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
