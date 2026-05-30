<?php

namespace App\Support;

class RutHelper
{
    public static function clean(?string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', (string) $rut));
    }

    public static function format(?string $rut): string
    {
        $clean = self::clean($rut);
        if (strlen($clean) < 2) return $clean;

        $body = substr($clean, 0, -1);
        $dv = substr($clean, -1);

        return number_format((int) $body, 0, ',', '.') . '-' . $dv;
    }

    public static function calcDv(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += (int) $number[$i] * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $rest = 11 - ($sum % 11);
        return match ($rest) {
            11 => '0',
            10 => 'K',
            default => (string) $rest,
        };
    }

    public static function validate(?string $rut): bool
    {
        $clean = self::clean($rut);
        if (strlen($clean) < 2) return false;

        $body = substr($clean, 0, -1);
        $dv = substr($clean, -1);

        if (!ctype_digit($body)) return false;

        return self::calcDv($body) === $dv;
    }
}
