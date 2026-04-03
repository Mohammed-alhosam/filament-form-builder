<?php

namespace Alhosam\FilamentFormBuilder\Livewire;

use Alhosam\FilamentFormBuilder\Models\Form;
use Alhosam\FilamentFormBuilder\Services\FormSubmissionGuard;
use Alhosam\FilamentFormBuilder\Services\FormSubmissionService;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class RenderForm extends Component
{
    use WithFileUploads;

    public int $formId;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public bool $submitted = false;

    public string $successMessage = '';

    public string $trapField = '';

    protected ?Form $cachedForm = null;

    protected ?Collection $cachedFields = null;

    public function mount(int $formId): void
    {
        $this->formId = $formId;
        $this->successMessage = $this->resolveSuccessMessage();
    }

    public function render()
    {
        return view('filament-form-builder::livewire.render-form', [
            'form' => $this->form(),
            'fields' => $this->fields(),
        ]);
    }

    public function submit(): void
    {
        if (! $this->isAvailable()) {
            throw ValidationException::withMessages([
                'form' => 'This form is not currently available.',
            ]);
        }

        app(FormSubmissionGuard::class)->ensurePasses(
            $this->form(),
            $this->trapField,
            $this->requestFingerprint(),
        );

        $validated = $this->validate($this->validationRules(), $this->validationMessages());
        $payload = $this->normalizeSubmissionPayload($validated['data'] ?? []);

        app(FormSubmissionService::class)->submit($this->form(), $payload, [
            'status' => 'submitted',
            'meta' => [
                'referer' => request()->headers->get('referer'),
            ],
        ]);

        app(FormSubmissionGuard::class)->registerSuccessfulSubmission(
            $this->form(),
            $this->requestFingerprint(),
        );

        $this->submitted = true;
        $this->data = [];
        $this->trapField = '';
        $this->successMessage = $this->resolveSuccessMessage();

        Notification::make()
            ->success()
            ->title($this->successMessage)
            ->send();
    }

    public function form(): Form
    {
        if ($this->cachedForm instanceof Form) {
            return $this->cachedForm;
        }

        /** @var Form */
        $form = Form::query()
            ->with('fields')
            ->findOrFail($this->formId);

        return $this->cachedForm = $form;
    }

    public function isAvailable(): bool
    {
        $form = $this->form();
        $availability = (array) $form->availability;

        if (($availability['requires_auth'] ?? false) && ! auth()->check()) {
            return false;
        }

        if (! ($availability['accept_guest_submissions'] ?? true) && ! auth()->check()) {
            return false;
        }

        $from = data_get($availability, 'available_from');
        $until = data_get($availability, 'available_until');

        if (filled($from) && now()->lt(Carbon::parse($from))) {
            return false;
        }

        if (filled($until) && now()->gt(Carbon::parse($until))) {
            return false;
        }

        return $form->is_active && $form->status === 'published';
    }

    public function fields(): Collection
    {
        if ($this->cachedFields instanceof Collection) {
            return $this->cachedFields;
        }

        return $this->cachedFields = $this->form()
            ->fields
            ->whereNull('parent_id')
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationRules(): array
    {
        $rules = [];

        foreach ($this->fields() as $field) {
            if (in_array($field->type, ['heading', 'paragraph', 'divider'], true)) {
                continue;
            }

            $fieldRules = (array) $field->validation_rules;

            if (empty($fieldRules)) {
                $fieldRules = $this->defaultRulesForField($field->type, $field->is_required);
            }

            if ($field->is_required && ! collect($fieldRules)->contains(fn (string $rule): bool => str_starts_with($rule, 'required'))) {
                array_unshift($fieldRules, 'required');
            }

            if (! $field->is_required && ! collect($fieldRules)->contains(fn (string $rule): bool => in_array($rule, ['nullable', 'sometimes'], true))) {
                array_unshift($fieldRules, 'nullable');
            }

            $rules["data.{$field->key}"] = array_values(array_unique($fieldRules));
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        $messages = [];

        foreach ($this->fields() as $field) {
            $attribute = "data.{$field->key}.required";
            $messages[$attribute] = $field->displayLabel().' is required.';
        }

        return $messages;
    }

    /**
     * @return array<int, string>
     */
    protected function defaultRulesForField(string $type, bool $isRequired): array
    {
        $rules = match ($type) {
            'email' => ['email'],
            'number' => ['numeric'],
            'checkbox' => ['boolean'],
            'checkbox_list' => ['array'],
            'date', 'datetime' => ['date'],
            'file' => ['file'],
            default => ['string'],
        };

        array_unshift($rules, $isRequired ? 'required' : 'nullable');

        return $rules;
    }

    protected function resolveSuccessMessage(): string
    {
        $form = $this->form();
        $locale = app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $translations = array_filter((array) $form->getTranslations('success_message'));

        return (string) (
            $translations[$locale]
            ?? $translations[$fallback]
            ?? config("filament-form-builder.default_success_message.{$locale}")
            ?? config("filament-form-builder.default_success_message.{$fallback}")
            ?? 'Thank you. Your response has been submitted successfully.'
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function normalizeSubmissionPayload(array $validated): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn ($field): array => [
                $field->key => $validated[$field->key] ?? null,
            ])
            ->all();
    }

    public function submitLabel(): string
    {
        $form = $this->form();
        $locale = app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $translations = array_filter((array) $form->getTranslations('submit_label'));

        return (string) (
            $translations[$locale]
            ?? $translations[$fallback]
            ?? config("filament-form-builder.default_submit_label.{$locale}")
            ?? config("filament-form-builder.default_submit_label.{$fallback}")
            ?? 'Submit'
        );
    }

    public function fieldComponentType(string $type): string
    {
        return match ($type) {
            'textarea' => 'textarea',
            'select' => 'select',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'checkbox_list' => 'checkbox_list',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'file' => 'file',
            default => $type,
        };
    }

    public function fieldWidthClass(string $width): string
    {
        return match ($width) {
            'half' => 'md:col-span-1',
            'third' => 'md:col-span-1 xl:col-span-1',
            default => 'md:col-span-2 xl:col-span-3',
        };
    }

    public function fieldId(string $key): string
    {
        return 'form-builder-'.Str::slug($key);
    }

    public function honeypotFieldName(): string
    {
        return (string) config('filament-form-builder.security.honeypot.field', 'website');
    }

    protected function requestFingerprint(): string
    {
        return request()->ip()
            ?: request()->header('X-Livewire')
            ?: 'guest';
    }
}
