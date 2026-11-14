<?php

if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        $delimiter = '~';
        $escapedPattern = str_replace($delimiter, '\\' . $delimiter, $pattern);
        $result = preg_split($delimiter . $escapedPattern . $delimiter . 'u', $string, $limit);

        if ($result === false) {
            $result = preg_split($delimiter . $escapedPattern . $delimiter, $string, $limit);
        }

        return $result;
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        $slice = substr($string, $start, $width);

        if (strlen($string) > $start + $width && $trim_marker !== '') {
            $markerLength = strlen($trim_marker);
            $slice = substr($slice, 0, max(0, $width - $markerLength)) . $trim_marker;
        }

        return $slice;
    }
}
