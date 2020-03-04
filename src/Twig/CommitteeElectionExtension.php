<?php

namespace AppBundle\Twig;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeElectionExtension extends AbstractExtension
{
    /**
     * @var CommitteeManager
     */
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('is_voting_committee', [$this, 'isVotingCommittee']),
            new TwigFunction('is_candidate', [$this, 'isCandidate']),
        ];
    }

    public function isVotingCommittee(Adherent $adherent, Committee $committee): bool
    {
        $membership = $this->manager->getCommitteeMembership($adherent, $committee);

        if (!$membership || !$membership->isVotingCommittee()) {
            return false;
        }

        return true;
    }

    public function isCandidate(Adherent $adherent, Committee $committee): bool
    {
        $membership = $this->manager->getCommitteeMembership($adherent, $committee);

        if (!$membership || !$membership->isVotingCommittee() || !$membership->getCommitteeCandidacy()) {
            return false;
        }

        return true;
    }
}
