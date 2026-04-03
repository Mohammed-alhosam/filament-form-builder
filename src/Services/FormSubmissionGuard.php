<?php

namespace Alhosam\FilamentFormBuilder\Services;

use Alhosam\FilamentFormBuilder\Models\Form;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class FormSubmissionGuard
{
    public function ensurePasses(Form $form, string $honeypotValue, ?string $requestFingerprint = null): void
    {
        $this->guardAgainstHoneypot($honeypotValue);
        $this->guardAgainstRateLimit($form, $requestFingerprint);
    }

    public function registerSuccessfulSubmission(Form $form, ?string $requestFingerprint = null): void
    {
        if (! config('filament-form-builder.security.rate_limit.enabled', true)) {
            return;
        }

        RateLimiter::hit(
            $this->rateLimitKey($form, $requestFingerprint),
            (int) config('filament-form-builder.security.rate_limit.decay_seconds', 60),
        );
    }

    protected function guardAgainstHoneypot(string $honeypotValue): void
    {
        if (! config('filament-form-builder.security.honeypot.enabled', true)) {
            return;
        }

        if (filled(trim($honeypotValue))) {
            throw ValidationException::withMessages([
                'form' => 'The submission could not be processed.',
            ]);
        }
    }

    protected function guardAgainstRateLimit(Form $form, ?string $requestFingerprint = null): void
    {
        if (! config('filament-form-builder.security.rate_limit.enabled', true)) {
            return;
        }

        $key = $this->rateLimitKey($form, $requestFingerprint);
        $maxAttempts = (int) config('filament-form-builder.security.rate_limit.max_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'form' => 'Too many submission attempts. Please try again later.',
            ]);
        }
    }

    protected function rateLimitKey(Form $form, ?string $requestFingerprint = null): string
    {
        $fingerprint = $requestFingerprint
            ?: request()->ip()
            ?: 'guest';

        return 'filament-form-builder:'.$form->getKey().':'.$fingerprint;
    }
}
