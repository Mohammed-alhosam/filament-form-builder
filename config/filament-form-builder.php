<?php

return [
    'navigation_group' => 'Forms',
    'default_status' => 'draft',
    'default_submit_label' => [
        'en' => 'Submit',
        'ar' => 'إرسال',
    ],
    'default_success_message' => [
        'en' => 'Thank you. Your response has been submitted successfully.',
        'ar' => 'شكرًا لك. تم إرسال النموذج بنجاح.',
    ],
    'uploads' => [
        'disk' => env('FORM_BUILDER_UPLOAD_DISK', env('FILESYSTEM_DISK', 'public')),
        'directory' => env('FORM_BUILDER_UPLOAD_DIRECTORY', 'form-builder'),
    ],
    'security' => [
        'honeypot' => [
            'enabled' => true,
            'field' => 'website',
        ],
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 5,
            'decay_seconds' => 60,
        ],
    ],
    'locales' => array_values(array_unique(array_filter([
        app()->getLocale(),
        config('app.fallback_locale'),
    ]))),
];
