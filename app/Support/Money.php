<?php

namespace App\Support;

final class Money
{
    public static function add(mixed $left, mixed $right): string
    {
        return self::fromCents(self::toCents($left) + self::toCents($right));
    }

    public static function subtract(mixed $left, mixed $right): string
    {
        return self::fromCents(self::toCents($left) - self::toCents($right));
    }

    public static function maxZero(mixed $amount): string
    {
        return self::fromCents(max(0, self::toCents($amount)));
    }

    public static function greaterThan(mixed $left, mixed $right): bool
    {
        return self::toCents($left) > self::toCents($right);
    }

    public static function percent(mixed $amount, mixed $percent): string
    {
        $cents = self::toCents($amount);
        $basisPoints = self::toScaledInt($percent, 2);

        return self::fromCents((int) round(($cents * $basisPoints) / 10000));
    }

    public static function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign . intdiv($absolute, 100) . '.' . str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function toCents(mixed $amount): int
    {
        return self::toScaledInt($amount, 2);
    }

    private static function toScaledInt(mixed $amount, int $scale): int
    {
        $value = trim((string) $amount);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');

        if ($value === '') {
            return 0;
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $whole = preg_replace('/\D/', '', $whole) ?: '0';
        $fraction = preg_replace('/\D/', '', $fraction);

        $nextDigit = (int) ($fraction[$scale] ?? 0);
        $fraction = str_pad(substr($fraction, 0, $scale), $scale, '0');
        $scaled = ((int) $whole * (10 ** $scale)) + (int) $fraction;

        if ($nextDigit >= 5) {
            $scaled++;
        }

        return $negative ? -$scaled : $scaled;
    }
}
