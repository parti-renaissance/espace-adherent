<?php

namespace AppBundle\VotingPlatform\Election\VoteCommand;

use AppBundle\Entity\VotingPlatform\CandidateGroup;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeAdherentVoteCommand extends VoteCommand
{
    /**
     * @var CandidateGroup
     *
     * @Assert\NotBlank
     */
    private $womanCandidate;

    /**
     * @var CandidateGroup
     *
     * @Assert\NotBlank
     */
    private $manCandidate;

    public function getManCandidate(): ?CandidateGroup
    {
        return $this->manCandidate;
    }

    public function setManCandidate(CandidateGroup $manCandidate): void
    {
        $this->manCandidate = $manCandidate;
    }

    public function getWomanCandidate(): ?CandidateGroup
    {
        return $this->womanCandidate;
    }

    public function setWomanCandidate(CandidateGroup $womanCandidate): void
    {
        $this->womanCandidate = $womanCandidate;
    }

    public function getCandidateGroups(): array
    {
        return array_filter([
            $this->womanCandidate,
            $this->manCandidate,
        ]);
    }
}
