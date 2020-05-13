<?php

namespace App\Statistics\Acquisition;

use App\Statistics\Acquisition\Calculator\CalculatorInterface;

class Aggregator
{
    private $calculators = [];

    public function addCalculator(CalculatorInterface $calculator, int $priority = 0): void
    {
        $this->calculators[$priority][] = $calculator;
    }

    public function calculate(StatisticsRequest $request): array
    {
        $result = [];
        foreach ($this->getCalculators() as $calculators) {
            foreach ($calculators as $calculator) {
                $result[] = [
                    'title' => $calculator->getLabel(),
                    'category' => $calculator->getCategory(),
                    'items' => $calculator->calculate(
                        $request,
                        $this->generateDateKeys($request->getStartDate(), $request->getEndDate())
                    ),
                ];
            }
        }

        return $result;
    }

    private function generateDateKeys(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return array_map(
            function (\DateTime $date) { return $date->format('Ym'); },
            iterator_to_array(new \DatePeriod($startDate, new \DateInterval('P1M'), $endDate))
        );
    }

    /**
     * @return CalculatorInterface[][]
     */
    private function getCalculators(): array
    {
        ksort($this->calculators);

        return $this->calculators;
    }
}
