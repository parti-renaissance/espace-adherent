<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\UserListDefinition\UserListDefinitionPermissions;
use Symfony\Component\HttpFoundation\RequestStack;

class ManageUserListDefinitionElectedRepresentativeVoter extends AbstractAdherentVoter
{
    private $electedRepresentativeRepository;
    private $requestStack;

    public function __construct(
        ElectedRepresentativeRepository $electedRepresentativeRepository,
        RequestStack $requestStack
    ) {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
        $this->requestStack = $requestStack;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->requestStack->getMasterRequest()->get('delegated_access_uuid'))) {
            $adherent = $delegatedAccess->getDelegator();
        }

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
