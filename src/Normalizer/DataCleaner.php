<?php

declare(strict_types=1);

namespace App\Normalizer;

class DataCleaner
{
    /**
     * Nulls out any key not in $allowedKeys (recursively for nested sub-allow-lists)
     * and truncates datetime values to date-only for keys ending in "_at" or listed in $extraDateKeys.
     *
     * @param array    $allowedKeys   flat allow-list, optionally with nested sub-allow-lists:
     *                                ['name', 'post_address' => ['city_name', 'country']]
     * @param string[] $extraDateKeys keys whose datetime value must be truncated to Y-m-d
     *                                (in addition to keys ending in "_at")
     */
    public function clean(array $data, array $allowedKeys, array $extraDateKeys = []): array
    {
        foreach ($data as $key => $value) {
            if (!\in_array($key, $allowedKeys)) {
                $data[$key] = null;
                continue;
            }

            if (\is_array($value) && !empty($allowedKeys[$key]) && \is_array($allowedKeys[$key])) {
                $data[$key] = $this->clean($value, $allowedKeys[$key], $extraDateKeys);
                continue;
            }

            // Keep only date part of datetime fields
            if ($value && (str_ends_with($key, '_at') || \in_array($key, $extraDateKeys, true))) {
                $data[$key] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : substr($value, 0, 10);
            }
        }

        return $data;
    }
}
