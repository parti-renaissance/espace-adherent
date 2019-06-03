<?php

namespace AppBundle\Security\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectMembershipRepository;

class ManageCitizenActionVoter extends AbstractAdherentVoter
{
    private $projectMembershipRepository;

    public function __construct(CitizenProjectMembershipRepository $projectMembershipRepository)
    {
        $this->projectMembershipRepository = $projectMembershipRepository;
    }

    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, CitizenActionPermissions::MANAGE, true)
            && $subject instanceof CitizenProject
        ;
    }

    /**
     * @param CitizenProject $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$subject->isApproved()) {
            return false;
        }

        if ($subject->isCreatedBy($adherent->getUuid())) {
            return true;
        }

        if ($adherent->hasLoadedCitizenProjectMemberships()) {
            return $adherent->isAdministratorOf($subject); // Prevent SQL query
        }

        return $this->projectMembershipRepository->administrateCitizenProject($adherent, $subject);
    }
}
