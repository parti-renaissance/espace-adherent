<?php

namespace App\VotingPlatform\Election\ResultCalculator;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\ElectionRoundResult;

interface ResultCalculatorInterface
{
    public static function getPriority(): int;

    public function support(Designation $designation): bool;

    public function calculate(ElectionRoundResult $electionRoundResult): void;
}
