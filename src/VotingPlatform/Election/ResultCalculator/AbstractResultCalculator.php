<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\ResultCalculator;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\Entity\VotingPlatform\ElectionResult\ElectionRoundResult;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractResultCalculator implements ResultCalculatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    final public function calculate(ElectionRoundResult $electionRoundResult): void
    {
        foreach ($electionRoundResult->getElectionPoolResults() as $poolResult) {
            if ($elected = $this->calculateForPool($poolResult)) {
                $poolResult->setIsElected(true);
                $elected->setElected(true);
            }
        }
    }

    public static function getPriority(): int
    {
        return 0;
    }

    abstract protected function calculateForPool(ElectionPoolResult $electionPoolResult): ?CandidateGroup;
}
