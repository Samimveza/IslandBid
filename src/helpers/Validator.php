<?php

class Validator
{
    public static function required(array $payload, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            $value = $payload[$field] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $errors[$field] = 'This field is required.';
            }
        }

        return $errors;
    }

    public static function email(?string $value): bool
    {
        if ($value === null) {
            return false;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength(?string $value, int $length): bool
    {
        if ($value === null) {
            return false;
        }
        return mb_strlen($value) >= $length;
    }

    public static function inArray(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }
}
