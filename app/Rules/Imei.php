<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Imei implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('The :attribute must be a 15-digit number.');

            return;
        }

        $imei = trim((string) $value);

        if (! self::isValid($imei)) {
            $fail('The :attribute must be a valid 15-digit IMEI with a valid Luhn check digit.');
        }
    }

    /**
     * Validate 15-digit IMEI with Luhn algorithm.
     */
    public static function isValid(string $imei): bool
    {
        if (! preg_match('/^\d{15}$/', $imei)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $digit = (int) $imei[$i];
            // 0-indexed: positions 1, 3, 5, 7, 9, 11, 13 (2nd, 4th, 6th...) are doubled
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Compute the 15th Luhn check digit from the first 14 digits.
     */
    public static function computeCheckDigit(string $first14): int
    {
        if (! preg_match('/^\d{14}$/', $first14)) {
            throw new \InvalidArgumentException('First 14 digits are required to compute IMEI check digit.');
        }

        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $digit = (int) $first14[$i];
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Generate a valid 15-digit IMEI for testing and factories.
     */
    public static function fake(string $prefix = '35693803'): string
    {
        $remaining = 14 - strlen($prefix);
        $random = '';
        for ($i = 0; $i < $remaining; $i++) {
            $random .= (string) mt_rand(0, 9);
        }
        $first14 = $prefix.$random;

        return $first14.self::computeCheckDigit($first14);
    }
}
