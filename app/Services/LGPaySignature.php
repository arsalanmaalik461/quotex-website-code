<?php

namespace App\Services;

class LGPaySignature
{
    public static function make(array $data, string $secretKey, array $exclude = []): string
    {
        foreach ($exclude as $field) {
            unset($data[$field]);
        }

        $data = array_filter($data, static fn ($value): bool => $value !== null && $value !== '');
        ksort($data);

        return strtoupper(md5(trim(urldecode(http_build_query($data))) . '&key=' . $secretKey));
    }

    public static function verify(array $data, string $secretKey): bool
    {
        if (empty($data['sign'])) {
            return false;
        }

        return hash_equals(self::make($data, $secretKey, ['sign']), (string) $data['sign']);
    }
}
