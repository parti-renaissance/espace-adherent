<?php

namespace App\Statistics\Acquisition;

use App\Statistics\Acquisition\Calculator\CalculatorInterface;

class Aggregator
{
    /**
     * @var iterable|CalculatorInterface[]
     */
    private iterable $calculators;

    public function __construct(iterable $calculators)
    {
        $this->calculators = $calculators;
    }

    public function calculate(StatisticsRequest $request): array
    {
        $result = [];
        foreach ($this->calculators as $calculator) {
            $result[] = [
                'title' => $calculator->getLabel(),
                'category' => $calculator->getCategory(),
                'items' => $calculator->calculate(
                    $request,
                    $this->generateDateKeys($request->getStartDate(), $request->getEndDate())
                ),
            ];
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
}
