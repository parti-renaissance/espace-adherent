<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\UserListDefinition\UserListDefinitionPermissions;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ManageUserListDefinitionElectedRepresentativeVoter extends AbstractAdherentVoter
{
    private $electedRepresentativeRepository;
    private $session;

    public function __construct(
        ElectedRepresentativeRepository $electedRepresentativeRepository,
        SessionInterface $session
    ) {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
        $this->session = $session;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($adherent->isReferent()) {
            return $this->electedRepresentativeRepository->isInReferentManagedArea(
                $subject,
                $adherent->getManagedArea()->getTags()->toArray()
            );
        }

        if ($adherent->isLre()) {
            return $this->electedRepresentativeRepository->isInReferentManagedArea(
                $subject,
                [$adherent->getLreArea()->getReferentTag()]
            );
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER === $attribute
            && $subject instanceof ElectedRepresentative;
    }
}
