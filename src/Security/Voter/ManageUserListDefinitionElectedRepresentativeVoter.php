<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\UserListDefinition\UserListDefinitionPermissions;

class ManageUserListDefinitionElectedRepresentativeVoter extends AbstractAdherentVoter
{
    private $electedRepresentativeRepository;

    public function __construct(ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$adherent->isReferent()) {
            return false;
        }

        return $this->electedRepresentativeRepository->isInReferentManagedArea(
            $subject,
            $adherent->getManagedArea()->getTags()->toArray()
        );
    }

    protected function supports($attribute, $subject)
    {
        return UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER === $attribute
            && $subject instanceof ElectedRepresentative;
    }
}
