<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;

class AdministrateCitizenProjectVoter extends AbstractCitizenProjectVoter
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject): bool
    {
        return CitizenProjectPermissions::ADMINISTRATE === $attribute && $subject instanceof CitizenProject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$citizenProject->isApproved()) {
            return $adherent->getUuid()->toString() === $citizenProject->getCreatedBy();
        }

        return $this->manager->administrateCitizenProject($adherent, $citizenProject);
    }
}
