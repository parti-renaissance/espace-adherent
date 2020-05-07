<?php

namespace AppBundle\VotingPlatform\Election\VoteCommand;

use Symfony\Component\Validator\Constraints as Assert;

class CommitteeAdherentVoteCommand extends VoteCommand
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $womanCandidate;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $manCandidate;

    public function getManCandidate(): ?string
    {
        return $this->manCandidate;
    }

    public function setManCandidate(?string $manCandidate): void
    {
        $this->manCandidate = $manCandidate;
    }

    public function getWomanCandidate(): ?string
    {
        return $this->womanCandidate;
    }

    public function setWomanCandidate(?string $womanCandidate): void
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
