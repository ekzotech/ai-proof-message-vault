<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NewPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
            $fail('Пароль должен содержать до 72 байт без нулевого символа.');
        }
    }
}
