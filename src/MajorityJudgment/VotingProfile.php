<?php

namespace App\MajorityJudgment;

class VotingProfile
{
    private $candidate;

    /**
     * @var Merit[]
     */
    private $merits = [];

    public function __construct(Candidate $candidate, array $merits = [])
    {
        $this->candidate = $candidate;

        foreach ($merits as $merit) {
            $this->addMerit($merit);
        }
    }

    public function addMerit(Merit $merit): void
    {
        $this->merits[$merit->getMention()->getIndex()] = $merit;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    /**
     * @return Merit[]
     */
    public function getMerits(): array
    {
        return $this->merits;
    }
}
