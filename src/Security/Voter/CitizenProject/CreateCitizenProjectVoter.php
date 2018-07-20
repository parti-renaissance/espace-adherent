<?php

namespace AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;

class CreateCitizenProjectVoter extends AbstractAdherentVoter
{
    private $projectRepository;

    public function __construct(CitizenProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return CitizenProjectPermissions::CREATE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        // Cannot create a project when already administrate one
        return $adherent->isAdherent()
            && !$adherent->isCitizenProjectAdministrator()
            && !$this->projectRepository->hasCitizenProjectInStatus($adherent, CitizenProject::STATUSES_NOT_ALLOWED_TO_CREATE)
        ;
    }
}
