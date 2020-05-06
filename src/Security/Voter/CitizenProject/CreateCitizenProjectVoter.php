<?php

namespace App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Repository\CitizenProjectRepository;
use App\Security\Voter\AbstractAdherentVoter;

class CreateCitizenProjectVoter extends AbstractAdherentVoter
{
    private $projectRepository;

    public function __construct(CitizenProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    protected function supports($attribute, $subject)
    {
        return CitizenProjectPermissions::CREATE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        // Cannot create a project when already has three citizen projects
        return $adherent->isAdherent()
            && !$this->projectRepository->hasCitizenProjectInStatus($adherent, CitizenProject::STATUSES_NOT_ALLOWED_TO_CREATE)
        ;
    }
}
