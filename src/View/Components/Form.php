<?php

namespace Alhosam\FilamentFormBuilder\View\Components;

use Alhosam\FilamentFormBuilder\Models\Form as FormModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class Form extends Component
{
    public function __construct(
        public ?string $slug = null,
        public ?int $formId = null,
        public ?FormModel $form = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('filament-form-builder::components.form', [
            'resolvedFormId' => $this->resolveFormId(),
        ]);
    }

    protected function resolveFormId(): int
    {
        if ($this->form instanceof FormModel) {
            return $this->form->getKey();
        }

        if (filled($this->formId)) {
            return (int) $this->formId;
        }

        if (filled($this->slug)) {
            return (int) FormModel::query()
                ->where('slug', $this->slug)
                ->firstOrFail()
                ->getKey();
        }

        throw new InvalidArgumentException('The form component expects a slug, formId, or Form model instance.');
    }
}
