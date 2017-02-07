<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CreateCommitteeVoter implements VoterInterface
{
    private $committeeRepository;
    private $committeeMembershipRepository;

    public function __construct(
        CommitteeMembershipRepository $committeeMembershipRepository,
        CommitteeRepository $committeeRepository
    ) {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->committeeRepository = $committeeRepository;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $adherent = $token->getUser();
        if (null !== $subject || !$adherent instanceof Adherent) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(CommitteePermissions::CREATE, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        return $this->voteOnCreateCommitteeAttribute($adherent);
    }

    private function voteOnCreateCommitteeAttribute(Adherent $adherent)
    {
        if ($adherent->isReferent()) {
            return self::ACCESS_DENIED;
        }

        $adherentUuid = (string) $adherent->getUuid();

        if ($this->committeeMembershipRepository->hostCommittee($adherent)) {
            return self::ACCESS_DENIED;
        }

        if ($this->committeeRepository->hasWaitingForApprovalCommittees($adherentUuid)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }
}
