<?php

namespace AppBundle\Statistics\Acquisition;

use AppBundle\Statistics\Acquisition\Calculator\CalculatorInterface;

class Aggregator
{
    private $calculators = [];

    public function addCalculator(CalculatorInterface $calculator): void
    {
        $this->calculators[] = $calculator;
    }

    public function calculate(StatisticsRequest $request): array
    {
        return array_map(
            function (CalculatorInterface $calculator) use ($request) {
                return [$calculator->getLabel() => $calculator->calculate(
                    $request,
                    $this->generateDateKeys($request->getStartDate(), $request->getEndDate())
                )];
            },
            $this->calculators
        );
    }

    private function generateDateKeys(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return array_map(
            function (\DateTime $date) { return $date->format('Ym'); },
            iterator_to_array(new \DatePeriod($startDate, new \DateInterval('P1M'), $endDate))
        );
    }
}
