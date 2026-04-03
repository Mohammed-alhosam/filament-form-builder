<?php

namespace Alhosam\FilamentFormBuilder\Services;

use Closure;

class FormBuilderRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $bindings = [];

    /**
     * @param  array<string, mixed>  $definition
     */
    public function registerBinding(string $key, array $definition): void
    {
        $this->bindings[$key] = $definition;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bindings(): array
    {
        return $this->bindings;
    }

    /**
     * @return array<string, string>
     */
    public function bindingOptions(): array
    {
        return collect($this->bindings)
            ->mapWithKeys(fn (array $definition, string $key): array => [
                $key => (string) ($definition['label'] ?? $key),
            ])
            ->all();
    }

    public function bindingDefinition(string $key): ?array
    {
        return $this->bindings[$key] ?? null;
    }

    public function bindingBindableType(string $key): ?string
    {
        return $this->bindings[$key]['bindable_type'] ?? null;
    }

    /**
     * @return array<int|string, string>
     */
    public function bindingRecordOptions(string $key): array
    {
        $definition = $this->bindingDefinition($key);
        $options = $definition['options'] ?? null;

        if ($options instanceof Closure) {
            return (array) app()->call($options);
        }

        return is_array($options) ? $options : [];
    }
}
