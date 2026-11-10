<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CapsuleText implements ValidationRule
{
    public const MAX_TITLE = 120;

    public const MAX_BODY = 12000;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/[^\p{Z}\s]/u', $value) !== 1) {
            $fail('Введите текст.');

            return;
        }

        $controlCharacters = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F\x{200B}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2060}\x{2066}-\x{2069}\x{FEFF}]/u';

        if (preg_match($controlCharacters, $value) === 1 || ($attribute === 'title' && strpbrk($value, "\r\n\t") !== false)) {
            $fail('Удалите из текста управляющие символы.');
        }
    }
}
