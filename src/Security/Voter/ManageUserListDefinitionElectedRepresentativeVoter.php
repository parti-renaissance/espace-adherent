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

        $isGranted = false;

        if ($adherent->isReferent()) {
            $isGranted = $this->electedRepresentativeRepository->isInReferentManagedArea(
                $subject,
                $adherent->getManagedArea()->getTags()->toArray()
            );
        }

        if (!$isGranted && $adherent->isLre()) {
            $isGranted = $adherent->getLreArea()->isAllTags() || $this->electedRepresentativeRepository->isInReferentManagedArea(
                $subject,
                [$adherent->getLreArea()->getReferentTag()]
            );
        }

        return $isGranted;
    }

    protected function supports($attribute, $subject)
    {
        return UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER === $attribute
            && $subject instanceof ElectedRepresentative;
    }
}
