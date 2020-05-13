<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Statistics\Acquisition\StatisticsRequest;

interface CalculatorInterface
{
    public function getLabel(): string;

    public function getCategory(): string;

    public function calculate(StatisticsRequest $request, array $keys): array;
}
