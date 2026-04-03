<?php

namespace Alhosam\FilamentFormBuilder\Filament\Resources\Forms\Pages;

use Alhosam\FilamentFormBuilder\Filament\Resources\Forms\FormResource;
use Alhosam\FilamentFormBuilder\Models\Form;
use Alhosam\FilamentFormBuilder\Models\FormBinding;
use Alhosam\FilamentFormBuilder\Models\FormField;
use Alhosam\FilamentFormBuilder\Services\FormBuilderRegistry;
use Alhosam\FilamentFormBuilder\Services\FormFieldTypeRegistry;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManageFormWorkspace extends ViewRecord
{
    protected static string $resource = FormResource::class;

    protected string $view = 'filament-form-builder::filament.resources.forms.pages.manage-form-workspace';

    public ?int $selectedFieldId = null;

    public ?int $selectedBindingId = null;

    public function getTitle(): string
    {
        return 'Form Workspace: '.$this->getRecord()->displayName();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function flattenedFields(): array
    {
        return $this->flattenFieldCollection(
            $this->getRecord()
                ->fields()
                ->whereNull('parent_id')
                ->with('children')
                ->get()
        );
    }

    public function recentSubmissions(): Collection
    {
        return $this->getRecord()
            ->submissions()
            ->limit(5)
            ->get();
    }

    public function formBindings(): Collection
    {
        return $this->getRecord()
            ->bindings()
            ->latest('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function notificationSettings(): array
    {
        return array_replace_recursive([
            'send_admin_notification' => false,
            'admin_email' => null,
            'send_submitter_confirmation' => false,
            'submitter_email_field' => null,
            'subject' => [
                'ar' => null,
                'en' => null,
            ],
        ], (array) $this->getRecord()->notifications);
    }

    public function availablePreview(): bool
    {
        return $this->getRecord()->fields()->exists();
    }

    /**
     * @return array<string, string>
     */
    public function bindingTypeOptions(): array
    {
        return app(FormBuilderRegistry::class)->bindingOptions();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function workspaceStats(): array
    {
        $form = $this->getRecord();

        return [
            [
                'label' => 'Fields',
                'value' => (string) $form->fields()->count(),
            ],
            [
                'label' => 'Submissions',
                'value' => (string) $form->submissions()->count(),
            ],
            [
                'label' => 'Bindings',
                'value' => (string) $form->bindings()->count(),
            ],
            [
                'label' => 'Status',
                'value' => Str::headline((string) $form->status),
            ],
        ];
    }

    public function requestEditField(int $fieldId): void
    {
        $this->selectedFieldId = $fieldId;

        $this->mountAction('editField', ['field' => $fieldId]);
    }

    public function requestDeleteField(int $fieldId): void
    {
        $this->selectedFieldId = $fieldId;

        $this->mountAction('deleteField', ['field' => $fieldId]);
    }

    public function requestEditBinding(int $bindingId): void
    {
        $this->selectedBindingId = $bindingId;

        $this->mountAction('editBinding', ['binding' => $bindingId]);
    }

    public function requestDeleteBinding(int $bindingId): void
    {
        $this->selectedBindingId = $bindingId;

        $this->mountAction('deleteBinding', ['binding' => $bindingId]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createField')
                ->label('Add field')
                ->modalHeading('Add field')
                ->form(fn (): array => $this->fieldFormComponents())
                ->action(function (array $data): void {
                    FormField::create($this->normalizeFieldPayload($data));

                    Notification::make()
                        ->success()
                        ->title('Field created successfully.')
                        ->send();

                    $this->redirect($this->workspaceUrl(), navigate: true);
                }),
            Action::make('createBinding')
                ->label('Add binding')
                ->modalHeading('Add binding')
                ->form(fn (): array => $this->bindingFormComponents())
                ->action(function (array $data): void {
                    FormBinding::create($this->normalizeBindingPayload($data));

                    Notification::make()
                        ->success()
                        ->title('Binding created successfully.')
                        ->send();

                    $this->redirect($this->workspaceUrl(), navigate: true);
                }),
            Action::make('editNotifications')
                ->label('Notifications')
                ->modalHeading('Notification settings')
                ->fillForm(fn (): array => $this->notificationSettings())
                ->form(fn (): array => $this->notificationFormComponents())
                ->action(function (array $data): void {
                    $this->getRecord()->update([
                        'notifications' => $this->normalizeNotificationPayload($data),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Notification settings updated successfully.')
                        ->send();

                    $this->redirect($this->workspaceUrl(), navigate: true);
                }),
            Action::make('editForm')
                ->label('Edit form')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->getRecord()])),
            $this->editFieldAction()->extraAttributes(['style' => 'display: none']),
            $this->deleteFieldAction()->extraAttributes(['style' => 'display: none']),
            $this->editBindingAction()->extraAttributes(['style' => 'display: none']),
            $this->deleteBindingAction()->extraAttributes(['style' => 'display: none']),
        ];
    }

    protected function editFieldAction(): Action
    {
        return Action::make('editField')
            ->modalHeading('Edit field')
            ->fillForm(function (): array {
                $field = $this->resolveSelectedField();

                return [
                    'type' => $field->type,
                    'key' => $field->key,
                    'label' => $field->getTranslations('label'),
                    'placeholder' => $field->getTranslations('placeholder'),
                    'help_text' => $field->getTranslations('help_text'),
                    'default_value' => $field->getTranslations('default_value'),
                    'is_required' => $field->is_required,
                    'width' => $field->width,
                    'sort_order' => $field->sort_order,
                    'validation_rules_text' => implode(PHP_EOL, (array) $field->validation_rules),
                    'options_text' => $this->formatOptionsForEditing((array) $field->options),
                ];
            })
            ->form(fn (): array => $this->fieldFormComponents(true))
            ->action(function (array $data): void {
                $this->resolveSelectedField()->update($this->normalizeFieldPayload($data));

                Notification::make()
                    ->success()
                    ->title('Field updated successfully.')
                    ->send();

                $this->redirect($this->workspaceUrl(), navigate: true);
            });
    }

    protected function deleteFieldAction(): Action
    {
        return Action::make('deleteField')
            ->modalHeading('Delete field')
            ->modalDescription('This removes the field definition from the form. Existing submissions remain unchanged.')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (): void {
                $this->resolveSelectedField()->delete();

                Notification::make()
                    ->success()
                    ->title('Field deleted successfully.')
                    ->send();

                $this->redirect($this->workspaceUrl(), navigate: true);
            });
    }

    protected function editBindingAction(): Action
    {
        return Action::make('editBinding')
            ->modalHeading('Edit binding')
            ->fillForm(function (): array {
                $binding = $this->resolveSelectedBinding();

                return [
                    'binding_key' => $binding->context && array_key_exists($binding->context, $this->bindingTypeOptions())
                        ? $binding->context
                        : null,
                    'bindable_type' => $binding->bindable_type,
                    'bindable_id' => $binding->bindable_id,
                    'context' => $binding->context,
                ];
            })
            ->form(fn (): array => $this->bindingFormComponents(true))
            ->action(function (array $data): void {
                $this->resolveSelectedBinding()->update($this->normalizeBindingPayload($data));

                Notification::make()
                    ->success()
                    ->title('Binding updated successfully.')
                    ->send();

                $this->redirect($this->workspaceUrl(), navigate: true);
            });
    }

    protected function deleteBindingAction(): Action
    {
        return Action::make('deleteBinding')
            ->modalHeading('Delete binding')
            ->modalDescription('This removes the host binding only. The form and submissions remain intact.')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (): void {
                $this->resolveSelectedBinding()->delete();

                Notification::make()
                    ->success()
                    ->title('Binding deleted successfully.')
                    ->send();

                $this->redirect($this->workspaceUrl(), navigate: true);
            });
    }

    /**
     * @return array<int, mixed>
     */
    protected function fieldFormComponents(bool $isEditing = false): array
    {
        return [
            Select::make('type')
                ->label('Type')
                ->options($this->fieldTypeOptions())
                ->required()
                ->live(),
            TextInput::make('key')
                ->label('Key')
                ->required()
                ->maxLength(100)
                ->rule(function () use ($isEditing) {
                    $rule = Rule::unique('form_builder_fields', 'key')
                        ->where('form_id', $this->getRecord()->getKey());

                    if ($isEditing && $this->selectedFieldId) {
                        $rule->ignore($this->selectedFieldId);
                    }

                    return $rule;
                })
                ->helperText('Use a stable machine-friendly key such as full_name or email.')
                ->regex('/^[a-z0-9_]+$/'),
            ...$this->translatableTextInputs('label', 'Label', true),
            ...$this->translatableTextInputs('placeholder', 'Placeholder'),
            ...$this->translatableTextAreas('help_text', 'Help text'),
            ...$this->translatableTextInputs('default_value', 'Default value'),
            Toggle::make('is_required')
                ->label('Required')
                ->default(false),
            Select::make('width')
                ->label('Width')
                ->options([
                    'full' => 'Full width',
                    'half' => 'Half width',
                    'third' => 'Third width',
                ])
                ->default('full')
                ->required(),
            TextInput::make('sort_order')
                ->label('Sort order')
                ->numeric()
                ->default(0)
                ->required(),
            Textarea::make('validation_rules_text')
                ->label('Validation rules')
                ->helperText('Enter one Laravel validation rule per line.')
                ->rows(4),
            Textarea::make('options_text')
                ->label('Options')
                ->helperText('For select, radio, and checkbox list. Use one option per line in the format value|Label.')
                ->rows(5)
                ->visible(fn (Get $get): bool => in_array((string) $get('type'), ['select', 'radio', 'checkbox_list'], true)),
            Placeholder::make('field_type_hint')
                ->label('Field type guidance')
                ->content('Keep the first version practical: collect the data you need now, and leave advanced branching or multi-step flows for later iterations.'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function bindingFormComponents(bool $isEditing = false): array
    {
        return [
            Select::make('binding_key')
                ->label('Binding type')
                ->options($this->bindingTypeOptions())
                ->live()
                ->afterStateUpdated(function ($state, $set): void {
                    if (filled($state)) {
                        $set('bindable_type', app(FormBuilderRegistry::class)->bindingBindableType((string) $state));
                    }
                })
                ->helperText('Use a registered binding type when the host project provides one.'),
            TextInput::make('bindable_type')
                ->label('Bindable type')
                ->required()
                ->maxLength(255),
            TextInput::make('bindable_id')
                ->label('Record ID')
                ->required()
                ->numeric(),
            TextInput::make('context')
                ->label('Context')
                ->helperText('Examples: registration, contact, sidebar-form.'),
            Placeholder::make('binding_help')
                ->label('Binding guidance')
                ->content('Bindings connect this form to host-side records such as events, landing pages, or custom workflows without making the package depend on those models directly.'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function notificationFormComponents(): array
    {
        return [
            Toggle::make('send_admin_notification')
                ->label('Send admin notification')
                ->default(false)
                ->live(),
            TextInput::make('admin_email')
                ->label('Admin email')
                ->email()
                ->visible(fn (Get $get): bool => (bool) $get('send_admin_notification')),
            Toggle::make('send_submitter_confirmation')
                ->label('Send submitter confirmation')
                ->default(false)
                ->live(),
            Select::make('submitter_email_field')
                ->label('Submitter email field')
                ->options($this->emailFieldOptions())
                ->visible(fn (Get $get): bool => (bool) $get('send_submitter_confirmation')),
            ...$this->translatableTextInputs('subject', 'Email subject'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function fieldTypeOptions(): array
    {
        return collect(app(FormFieldTypeRegistry::class)->all())
            ->mapWithKeys(fn (array $definition, string $key): array => [
                $key => (string) ($definition['label'] ?? Str::headline($key)),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function emailFieldOptions(): array
    {
        return $this->getRecord()
            ->fields()
            ->where('type', 'email')
            ->get()
            ->mapWithKeys(fn (FormField $field): array => [
                $field->key => $field->displayLabel(),
            ])
            ->all();
    }

    protected function resolveSelectedField(): FormField
    {
        return $this->getRecord()
            ->fields()
            ->findOrFail((int) ($this->selectedFieldId ?? 0));
    }

    protected function resolveSelectedBinding(): FormBinding
    {
        return $this->getRecord()
            ->bindings()
            ->findOrFail((int) ($this->selectedBindingId ?? 0));
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeFieldPayload(array $data): array
    {
        return [
            'form_id' => $this->getRecord()->getKey(),
            'type' => (string) $data['type'],
            'key' => (string) $data['key'],
            'label' => (array) ($data['label'] ?? []),
            'placeholder' => (array) ($data['placeholder'] ?? []),
            'help_text' => (array) ($data['help_text'] ?? []),
            'default_value' => (array) ($data['default_value'] ?? []),
            'is_required' => (bool) ($data['is_required'] ?? false),
            'width' => (string) ($data['width'] ?? 'full'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'validation_rules' => $this->normalizeValidationRules((string) ($data['validation_rules_text'] ?? '')),
            'options' => $this->normalizeOptions((string) ($data['options_text'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeBindingPayload(array $data): array
    {
        $bindingKey = blank($data['binding_key'] ?? null) ? null : (string) $data['binding_key'];

        return [
            'form_id' => $this->getRecord()->getKey(),
            'bindable_type' => filled($bindingKey)
                ? (app(FormBuilderRegistry::class)->bindingBindableType($bindingKey) ?? (string) $data['bindable_type'])
                : (string) $data['bindable_type'],
            'bindable_id' => (int) $data['bindable_id'],
            'context' => blank($data['context'] ?? null) ? $bindingKey : (string) $data['context'],
            'settings' => filled($bindingKey) ? ['binding_key' => $bindingKey] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeNotificationPayload(array $data): array
    {
        return [
            'send_admin_notification' => (bool) ($data['send_admin_notification'] ?? false),
            'admin_email' => blank($data['admin_email'] ?? null) ? null : (string) $data['admin_email'],
            'send_submitter_confirmation' => (bool) ($data['send_submitter_confirmation'] ?? false),
            'submitter_email_field' => blank($data['submitter_email_field'] ?? null) ? null : (string) $data['submitter_email_field'],
            'subject' => (array) ($data['subject'] ?? []),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeValidationRules(string $rules): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $rules) ?: [])
            ->map(fn (string $rule): string => trim($rule))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function normalizeOptions(string $options): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $options) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(function (string $line): array {
                [$value, $label] = array_pad(explode('|', $line, 2), 2, null);

                $value = trim((string) $value);
                $label = trim((string) ($label ?? $value));

                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, string>>  $options
     */
    protected function formatOptionsForEditing(array $options): string
    {
        return collect($options)
            ->map(fn (array $option): string => ($option['value'] ?? '').'|'.($option['label'] ?? $option['value'] ?? ''))
            ->implode(PHP_EOL);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function flattenFieldCollection(iterable $fields, int $depth = 0): array
    {
        $flattened = [];

        foreach ($fields as $field) {
            $flattened[] = [
                'record' => $field,
                'depth' => $depth,
            ];

            $children = $field->relationLoaded('children') ? $field->children : $field->children()->get();

            if ($children->isNotEmpty()) {
                $flattened = [
                    ...$flattened,
                    ...$this->flattenFieldCollection($children, $depth + 1),
                ];
            }
        }

        return $flattened;
    }

    /**
     * @return array<int, mixed>
     */
    protected function translatableTextInputs(string $key, string $label, bool $required = false): array
    {
        return [
            TextInput::make("{$key}.ar")
                ->label("{$label} (AR)")
                ->required($required),
            TextInput::make("{$key}.en")
                ->label("{$label} (EN)"),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function translatableTextAreas(string $key, string $label): array
    {
        return [
            Textarea::make("{$key}.ar")
                ->label("{$label} (AR)")
                ->rows(3),
            Textarea::make("{$key}.en")
                ->label("{$label} (EN)")
                ->rows(3),
        ];
    }

    protected function workspaceUrl(): string
    {
        /** @var Form $record */
        $record = $this->getRecord();

        return static::getResource()::getUrl('workspace', ['record' => $record]);
    }
}
