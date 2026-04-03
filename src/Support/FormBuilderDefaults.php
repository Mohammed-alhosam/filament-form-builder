<?php

namespace Alhosam\FilamentFormBuilder\Support;

class FormBuilderDefaults
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fieldTypes(): array
    {
        return [
            'text' => ['label' => 'Text'],
            'textarea' => ['label' => 'Textarea'],
            'email' => ['label' => 'Email'],
            'phone' => ['label' => 'Phone'],
            'number' => ['label' => 'Number'],
            'select' => ['label' => 'Select'],
            'radio' => ['label' => 'Radio'],
            'checkbox' => ['label' => 'Checkbox'],
            'checkbox_list' => ['label' => 'Checkbox list'],
            'date' => ['label' => 'Date'],
            'datetime' => ['label' => 'Date time'],
            'file' => ['label' => 'File'],
            'heading' => ['label' => 'Heading'],
            'paragraph' => ['label' => 'Paragraph'],
            'divider' => ['label' => 'Divider'],
        ];
    }
}
