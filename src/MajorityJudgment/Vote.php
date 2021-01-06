<?php

namespace App\MajorityJudgment;

class Vote
{
    private $candidate;
    private $mention;

    public function __construct(Candidate $candidate, Mention $mention)
    {
        $this->candidate = $candidate;
        $this->mention = $mention;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getMention(): Mention
    {
        return $this->mention;
    }
}
