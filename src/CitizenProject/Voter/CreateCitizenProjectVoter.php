<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CreateCitizenProjectVoter extends Voter
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject)
    {
        return CitizenProjectPermissions::CREATE === $attribute && null === $subject;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent || $adherent->isReferent()) {
            return false;
        }

        if ($this->manager->isCitizenProjectAdministrator($adherent)) {
            return false;
        }

        if ($this->manager->hasCitizenProjectInStatus($adherent, CitizenProjectManager::STATUS_NOT_ALLOWED_TO_CREATE)) {
            return false;
        }

        return true;
    }
}
