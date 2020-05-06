<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractCalculator implements CalculatorInterface
{
    private $calculatedData;

    final public function calculate(StatisticsRequest $request, array $keys): array
    {
        if (\is_array($this->calculatedData)) {
            return $this->calculatedData;
        }

        return $this->calculatedData = $this->fillEmptyCase($this->processing($request, $keys), $keys);
    }

    protected function fillEmptyCase(array $data, array $keys, int $value = 0): array
    {
        if (\array_key_exists(0, $data)) {
            $data = array_column($data, 'total', 'date');
        }

        $result = $this->formatEachValue($data) + array_fill_keys($keys, $value);
        ksort($result);

        return $result;
    }

    protected function mergeResults(callable $callback, array $keys, ...$data): array
    {
        return array_combine($keys, array_map($callback, ...$data));
    }

    protected function formatEachValue(array $data): array
    {
        return array_map('intval', $data);
    }

    abstract protected function processing(StatisticsRequest $request, array $keys): array;
}
