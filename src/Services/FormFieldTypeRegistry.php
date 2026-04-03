<?php

namespace Alhosam\FilamentFormBuilder\Services;

class FormFieldTypeRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $types = [];

    /**
     * @param  array<string, mixed>  $definition
     */
    public function register(string $key, array $definition): void
    {
        $this->types[$key] = $definition;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->types;
    }
}
