<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\ResultCalculator;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;

/**
 * Default calculator, should be used by default and at the end of all others calculators
 * that is why support=true and priority=-255
 */
class MajoritarianCalculator extends AbstractResultCalculator
{
    public function support(Designation $designation): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return -255;
    }

    protected function calculateForPool(ElectionPoolResult $electionPoolResult): ?CandidateGroup
    {
        $elected = null;
        $max = 0;

        foreach ($electionPoolResult->getCandidateGroupResults() as $result) {
            $total = $result->getTotal();

            if ($total > $max) {
                $max = $total;
                $elected = $result->getCandidateGroup();
            } elseif ($max === $total) {
                $elected = null;
            }
        }

        return $elected;
    }
}
