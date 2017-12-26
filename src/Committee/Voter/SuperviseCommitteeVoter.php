<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SuperviseCommitteeVoter extends Voter
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject): bool
    {
        return CommitteePermissions::SUPERVISE === $attribute && $subject instanceof Committee;
    }

    /**
     * @param string         $attribute
     * @param Committee      $committee
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $committee, TokenInterface $token): bool
    {
        if ($token instanceof AnonymousToken) {
            return false;
        }

        $supervisor = $token->getUser();

        if (!$supervisor instanceof Adherent) {
            return false;
        }

        return $this->manager->superviseCommittee($supervisor, $committee);
    }
}
