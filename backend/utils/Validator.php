<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Small fluent validator for request input. Collects per-field error messages.
 *
 * Example:
 *   $v = Validator::make($data)
 *       ->required('email')->email('email')
 *       ->required('password')->min('password', 8)
 *       ->confirmed('password', 'password_confirmation');
 *   if ($v->fails()) { ... }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, ?string $label = null): self
    {
        $label ??= ucfirst($field);
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') {
            $this->errors[$field][] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Enter a valid email address.';
        }
        return $this;
    }

    public function min(string $field, int $min): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && mb_strlen((string) $value) < $min) {
            $this->errors[$field][] = "Must be at least $min characters.";
        }
        return $this;
    }

    public function max(string $field, int $max): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->errors[$field][] = "Must be no more than $max characters.";
        }
        return $this;
    }

    public function confirmed(string $field, string $confirmation): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$confirmation] ?? null)) {
            $this->errors[$field][] = 'The two values do not match.';
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
