<?php

namespace App\Utils;

abstract class ArrayUtils
{
    public static function arrayDiffRecursive(array $a, array $b, bool $unordered = false): array
    {
        $diff = [];

        foreach ($a as $key => $value) {
            if (!\array_key_exists($key, $b)) {
                $diff[$key] = $value;
                continue;
            }

            if (\is_array($value) && \is_array($b[$key])) {
                if (
                    $unordered
                    && array_keys($value) === range(0, \count($value) - 1)
                    && array_keys($b[$key]) === range(0, \count($b[$key]) - 1)
                ) {
                    $aSorted = self::normalizeArray($value);
                    $bSorted = self::normalizeArray($b[$key]);

                    if ($aSorted !== $bSorted) {
                        $diff[$key] = $value;
                    }

                    continue;
                }

                if ($subDiff = self::arrayDiffRecursive($value, $b[$key], $unordered)) {
                    $diff[$key] = $subDiff;
                }

                continue;
            }

            if ($value !== $b[$key]) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }

    private static function normalizeArray(array $array): array
    {
        foreach ($array as &$item) {
            if (\is_array($item)) {
                ksort($item);
            }
        }

        usort($array, function ($a, $b) {
            return strcmp(json_encode($a), json_encode($b));
        });

        return $array;
    }
}
