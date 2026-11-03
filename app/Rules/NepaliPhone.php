<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NepaliPhone implements ValidationRule
{
    /**
     * Regex matching Nepali 10-digit mobile numbers with optional +977 or 977 prefix, spaces, and hyphens.
     */
    public const REGEX = '/^(?:\+977|977)?(9[78]\d{8})$/';

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid phone number.');

            return;
        }

        $clean = preg_replace('/[\s\-]/', '', $value);

        if (! preg_match(self::REGEX, $clean)) {
            $fail('Please provide a valid 10-digit Nepali mobile number (e.g. 98XXXXXXXX or 97XXXXXXXX).');
        }
    }

    /**
     * Normalize a Nepali phone number to standard 10-digit format (98XXXXXXXX or 97XXXXXXXX).
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $clean = preg_replace('/[\s\-]/', '', trim($phone));

        if (preg_match(self::REGEX, $clean, $matches)) {
            return $matches[1];
        }

        return trim($phone);
    }
}
