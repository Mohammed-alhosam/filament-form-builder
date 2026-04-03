<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    @if (! $this->isAvailable())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            This form is not currently available.
        </div>
    @elseif ($submitted)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $successMessage }}
        </div>
    @else
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-950">{{ $form->displayName() }}</h2>
            @php($description = $form->getTranslation('description', app()->getLocale(), false) ?: $form->getTranslation('description', config('app.fallback_locale'), false))
            @if (filled($description))
                <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>

        <form wire:submit="submit" class="space-y-6">
            <div class="ffb-honeypot" aria-hidden="true">
                <label for="ffb-honeypot-{{ $form->getKey() }}">{{ $this->honeypotFieldName() }}</label>
                <input
                    id="ffb-honeypot-{{ $form->getKey() }}"
                    type="text"
                    wire:model="trapField"
                    tabindex="-1"
                    autocomplete="off"
                >
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($fields as $field)
                    @php($type = $field->type)
                    @php($fieldId = $this->fieldId($field->key))
                    @php($label = $field->displayLabel())
                    @php($placeholder = $field->getTranslation('placeholder', app()->getLocale(), false) ?: $field->getTranslation('placeholder', config('app.fallback_locale'), false))
                    @php($helpText = $field->getTranslation('help_text', app()->getLocale(), false) ?: $field->getTranslation('help_text', config('app.fallback_locale'), false))
                    <div class="{{ $this->fieldWidthClass((string) $field->width) }}">
                        @if ($type === 'heading')
                            <h3 class="text-lg font-semibold text-gray-950">{{ $label }}</h3>
                        @elseif ($type === 'paragraph')
                            <p class="text-sm leading-6 text-gray-600">{{ $label }}</p>
                        @elseif ($type === 'divider')
                            <hr class="border-gray-200" />
                        @else
                            <div class="space-y-2">
                                <label for="{{ $fieldId }}" class="block text-sm font-medium text-gray-800">
                                    {{ $label }}
                                    @if ($field->is_required)
                                        <span class="text-amber-600">*</span>
                                    @endif
                                </label>

                                @if ($type === 'textarea')
                                    <textarea
                                        id="{{ $fieldId }}"
                                        wire:model="data.{{ $field->key }}"
                                        rows="4"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                                    ></textarea>
                                @elseif ($type === 'select')
                                    <select
                                        id="{{ $fieldId }}"
                                        wire:model="data.{{ $field->key }}"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                                    >
                                        <option value="">Select an option</option>
                                        @foreach ((array) $field->options as $option)
                                            <option value="{{ $option['value'] ?? '' }}">
                                                {{ $option['label'] ?? $option['value'] ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'radio')
                                    <div class="space-y-2 rounded-xl border border-gray-200 p-4">
                                        @foreach ((array) $field->options as $option)
                                            <label class="flex items-center gap-3 text-sm text-gray-800">
                                                <input
                                                    type="radio"
                                                    wire:model="data.{{ $field->key }}"
                                                    value="{{ $option['value'] ?? '' }}"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                >
                                                <span>{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($type === 'checkbox')
                                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-800">
                                        <input
                                            id="{{ $fieldId }}"
                                            type="checkbox"
                                            wire:model="data.{{ $field->key }}"
                                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @elseif ($type === 'checkbox_list')
                                    <div class="space-y-2 rounded-xl border border-gray-200 p-4">
                                        @foreach ((array) $field->options as $option)
                                            <label class="flex items-center gap-3 text-sm text-gray-800">
                                                <input
                                                    type="checkbox"
                                                    wire:model="data.{{ $field->key }}"
                                                    value="{{ $option['value'] ?? '' }}"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                >
                                                <span>{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($type === 'file')
                                    <input
                                        id="{{ $fieldId }}"
                                        type="file"
                                        wire:model="data.{{ $field->key }}"
                                        class="block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition file:me-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                                    >
                                @else
                                    <input
                                        id="{{ $fieldId }}"
                                        type="{{ $this->fieldComponentType($type) }}"
                                        wire:model="data.{{ $field->key }}"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                                    >
                                @endif

                                @if (filled($helpText) && ! in_array($type, ['checkbox'], true))
                                    <p class="text-xs text-gray-500">{{ $helpText }}</p>
                                @endif

                                @error('data.'.$field->key)
                                    <p class="text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-300"
                >
                    {{ $this->submitLabel() }}
                </button>
            </div>
        </form>
    @endif
</div>
