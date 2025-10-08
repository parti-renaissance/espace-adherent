<?php

namespace App\JeMengage\Hit\Stats\DTO;

class StatsOutput implements \JsonSerializable
{
    private array $data = [];

    public function push(array $stats): void
    {
        $this->data = array_merge($this->data, $stats);
    }

    public function jsonSerialize(): array
    {
        $result = [];

        $allKeys = array_keys($this->data);

        foreach ($this->data as $key => $value) {
            $recurrenceCount = 0;
            array_walk($allKeys, static function (string $k) use ($key, &$recurrenceCount) {
                if (str_starts_with($k, $key)) {
                    ++$recurrenceCount;
                }
            });

            if (str_contains($key, '__')) {
                [$base, $sub] = explode('__', $key, 2);
                $result[$base][$sub] = $value;
                continue;
            }

            if (1 === $recurrenceCount) {
                $result[$key] = $value;
                continue;
            }

            $result[$key]['total'] = $value;
        }

        $this->recursiveSort($result);

        return $result;
    }

    private function recursiveSort(array &$array): void
    {
        ksort($array);
        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->recursiveSort($value);
            }
        }
    }
}
