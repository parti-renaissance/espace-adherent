<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Statistics\Acquisition\StatisticsRequest;

interface CalculatorInterface
{
    public function getLabel(): string;

    public function calculate(StatisticsRequest $request, array $keys): array;
}
