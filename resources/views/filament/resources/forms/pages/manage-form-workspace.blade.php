<x-filament-panels::page>
    <div class="ffb-workspace">
        <div class="ffb-stats-grid">
            @foreach ($this->workspaceStats() as $stat)
                <section class="ffb-card ffb-stat-card">
                    <div class="ffb-stat-label">{{ $stat['label'] }}</div>
                    <div class="ffb-stat-value">{{ $stat['value'] }}</div>
                </section>
            @endforeach
        </div>

        <div class="ffb-workspace-grid">
            <section class="ffb-workspace-main">
                <section class="ffb-card">
                    <div class="ffb-card-head">
                        <div>
                            <h2 class="ffb-card-title">Fields</h2>
                            <p class="ffb-card-description">
                                Manage the form definition from this workspace without leaving the current screen.
                            </p>
                        </div>

                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-plus"
                            wire:click="mountAction('createField')"
                        >
                            Add field
                        </x-filament::button>
                    </div>

                    <div class="ffb-panel-shell">
                        @if (count($this->flattenedFields()) === 0)
                            <div class="ffb-empty-state">
                                <div class="ffb-empty-title">No fields have been added yet.</div>
                                <p class="ffb-empty-description">
                                    Start with the practical essentials such as name, email, phone number, or attendee notes.
                                </p>
                            </div>
                        @else
                            <div class="ffb-field-list">
                                @foreach ($this->flattenedFields() as $item)
                                    @php($field = $item['record'])

                                    <article class="ffb-field-item">
                                        <div class="ffb-field-main">
                                            <div class="ffb-field-title-row" style="padding-inline-start: {{ $item['depth'] * 20 }}px;">
                                                <span class="ffb-field-title">{{ $field->displayLabel() }}</span>
                                                <span class="ffb-pill">{{ \Illuminate\Support\Str::headline($field->type) }}</span>
                                                <span class="ffb-pill">{{ $field->key }}</span>
                                                @if ($field->is_required)
                                                    <span class="ffb-pill is-required">Required</span>
                                                @endif
                                                <span class="ffb-pill">{{ \Illuminate\Support\Str::headline($field->width) }}</span>
                                            </div>

                                            @php($helpText = $field->getTranslation('help_text', app()->getLocale(), false) ?: $field->getTranslation('help_text', config('app.fallback_locale'), false))
                                            @if (filled($helpText))
                                                <p class="ffb-field-help">{{ $helpText }}</p>
                                            @endif
                                        </div>

                                        <div class="ffb-field-actions">
                                            <x-filament::button
                                                color="gray"
                                                size="sm"
                                                wire:click="requestEditField({{ $field->getKey() }})"
                                            >
                                                Edit
                                            </x-filament::button>

                                            <x-filament::button
                                                color="danger"
                                                size="sm"
                                                wire:click="requestDeleteField({{ $field->getKey() }})"
                                            >
                                                Delete
                                            </x-filament::button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="ffb-card">
                    <div class="ffb-card-head">
                        <div>
                            <h2 class="ffb-card-title">Recent submissions</h2>
                            <p class="ffb-card-description">
                                The latest responses stay visible here so the workspace remains useful even before we add a larger reporting experience.
                            </p>
                        </div>
                    </div>

                    <div class="ffb-panel-shell">
                        @if ($this->recentSubmissions()->isEmpty())
                            <div class="ffb-empty-state is-compact">
                                <p class="ffb-empty-description">No submissions yet.</p>
                            </div>
                        @else
                            <div class="ffb-submission-list">
                                @foreach ($this->recentSubmissions() as $submission)
                                    <article class="ffb-submission-item">
                                        <div>
                                            <div class="ffb-submission-title">Submission #{{ $submission->getKey() }}</div>
                                            <div class="ffb-submission-meta">
                                                {{ $submission->submitted_at?->format('Y-m-d H:i') ?? 'Pending timestamp' }}
                                            </div>
                                        </div>

                                        <div class="ffb-submission-badges">
                                            <span class="ffb-pill">{{ \Illuminate\Support\Str::headline((string) $submission->status) }}</span>
                                            <span class="ffb-pill">{{ count((array) $submission->payload) }} values</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="ffb-card">
                    <div class="ffb-card-head">
                        <div>
                            <h2 class="ffb-card-title">Preview</h2>
                            <p class="ffb-card-description">
                                Preview uses the currently saved field definition so you can evaluate the actual frontend rendering from the same workspace.
                            </p>
                        </div>
                    </div>

                    <div class="ffb-panel-shell">
                        @if ($this->availablePreview())
                            <div class="ffb-preview-shell">
                                <x-filament-form-builder::form :form="$this->getRecord()" />
                            </div>
                        @else
                            <div class="ffb-empty-state is-compact">
                                <p class="ffb-empty-description">Add at least one field to enable the live preview.</p>
                            </div>
                        @endif
                    </div>
                </section>
            </section>

            <aside class="ffb-workspace-side">
                <section class="ffb-card">
                    <h2 class="ffb-card-title">Form summary</h2>

                    <dl class="ffb-summary-list">
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Slug</dt>
                            <dd class="ffb-summary-value">{{ $this->getRecord()->slug }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Status</dt>
                            <dd class="ffb-summary-value">{{ \Illuminate\Support\Str::headline((string) $this->getRecord()->status) }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Active</dt>
                            <dd class="ffb-summary-value">{{ $this->getRecord()->is_active ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Submit label</dt>
                            <dd class="ffb-summary-value">
                                {{ $this->getRecord()->getTranslation('submit_label', app()->getLocale(), false) ?: $this->getRecord()->displayName() }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="ffb-card">
                    <div class="ffb-card-head">
                        <div>
                            <h2 class="ffb-card-title">Bindings</h2>
                            <p class="ffb-card-description">
                                Connect this form to host-side records such as events, pages, or other workflows without scattering configuration across multiple screens.
                            </p>
                        </div>

                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="mountAction('createBinding')"
                        >
                            Add binding
                        </x-filament::button>
                    </div>

                    <div class="ffb-binding-list">
                        @forelse ($this->formBindings() as $binding)
                            <article class="ffb-binding-item">
                                <div>
                                    <div class="ffb-binding-title">{{ $binding->bindable_type }}</div>
                                    <div class="ffb-binding-meta">
                                        Record #{{ $binding->bindable_id }}
                                        @if (filled($binding->context))
                                            · {{ $binding->context }}
                                        @endif
                                    </div>
                                </div>

                                <div class="ffb-binding-actions">
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        wire:click="requestEditBinding({{ $binding->getKey() }})"
                                    >
                                        Edit
                                    </x-filament::button>

                                    <x-filament::button
                                        color="danger"
                                        size="sm"
                                        wire:click="requestDeleteBinding({{ $binding->getKey() }})"
                                    >
                                        Delete
                                    </x-filament::button>
                                </div>
                            </article>
                        @empty
                            <div class="ffb-empty-state is-compact">
                                <p class="ffb-empty-description">No bindings yet.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="ffb-card">
                    <div class="ffb-card-head">
                        <div>
                            <h2 class="ffb-card-title">Notifications</h2>
                            <p class="ffb-card-description">
                                Keep basic notification behavior close to the form definition for faster setup and fewer screens.
                            </p>
                        </div>

                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="mountAction('editNotifications')"
                        >
                            Configure
                        </x-filament::button>
                    </div>

                    @php($notificationSettings = $this->notificationSettings())
                    <dl class="ffb-summary-list">
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Admin notifications</dt>
                            <dd class="ffb-summary-value">{{ $notificationSettings['send_admin_notification'] ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Admin email</dt>
                            <dd class="ffb-summary-value">{{ $notificationSettings['admin_email'] ?: 'Not configured' }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Submitter confirmation</dt>
                            <dd class="ffb-summary-value">{{ $notificationSettings['send_submitter_confirmation'] ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div class="ffb-summary-item">
                            <dt class="ffb-summary-label">Email field</dt>
                            <dd class="ffb-summary-value">{{ $notificationSettings['submitter_email_field'] ?: 'Not configured' }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
