<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CreateCitizenProjectVoter implements VoterInterface
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $adherent = $token->getUser();

        if (null !== $subject || !$adherent instanceof Adherent) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(CitizenProjectPermissions::CREATE, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        if ($adherent->isReferent()) {
            return self::ACCESS_DENIED;
        }

        if ($this->manager->isCitizenProjectAdministrator($adherent)) {
            return self::ACCESS_DENIED;
        }

        if ($this->manager->hasCitizenProjectInStatus($adherent, CitizenProjectManager::STATUS_NOT_ALLOWED_TO_CREATE)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }
}
