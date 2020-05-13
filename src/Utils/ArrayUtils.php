<?php

namespace App\Utils;

abstract class ArrayUtils
{
    public static function arrayDiffRecursive(array $a, array $b): array
    {
        $diff = [];

        foreach ($a as $key => $value) {
            if (!\array_key_exists($key, $b)) {
                $diff[$key] = $value;
                continue;
            }

            if (\is_array($value) && \is_array($b[$key]) && $subDiff = self::arrayDiffRecursive($value, $b[$key])) {
                $diff[$key] = $subDiff;
                continue;
            }

            if ($value !== $b[$key]) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }
}
