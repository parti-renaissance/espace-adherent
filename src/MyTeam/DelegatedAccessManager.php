<?php

namespace App\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\OAuth\TokenRevocationAuthority;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\EntityManagerInterface;

class DelegatedAccessManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DelegatedAccessRepository $delegatedAccessRepository,
        private readonly TokenRevocationAuthority $tokenRevocationAuthority,
    ) {
    }

    public function createDelegatedAccessForMember(Member $member): void
    {
        if (!$delegatedAccess = $this->findDelegatedAccess($member)) {
            $delegatedAccess = new DelegatedAccess();
            $delegatedAccess->setDelegator($member->getTeam()->getOwner());
            $delegatedAccess->setDelegated($member->getAdherent());
            $delegatedAccess->setType($member->getTeam()->getScope());
            $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);

            $this->entityManager->persist($delegatedAccess);
        }

        $delegatedAccess->setScopeFeatures($member->getScopeFeatures());

        $this->entityManager->flush();

        $this->tokenRevocationAuthority->revokeUserTokens($member->getAdherent());
    }

    public function updateDelegatedAccessForMember(Member $member, ?Member $fromMember = null): void
    {
        if ($fromMember && $member->getAdherent() !== $fromMember->getAdherent() && ($fromDelegatedAccess = $this->findDelegatedAccess($fromMember))) {
            $this->removeDelegatedAccess($fromDelegatedAccess);
        }

        if ($delegatedAccess = $this->findDelegatedAccess($member)) {
            if ($member->getScopeFeatures()) {
                $delegatedAccess->setScopeFeatures($member->getScopeFeatures());
                $this->entityManager->flush();

                return;
            }

            $this->removeDelegatedAccess($delegatedAccess);
        } elseif ($member->getScopeFeatures()) {
            $this->createDelegatedAccessForMember($member);
        }
    }

    public function findDelegatedAccess(Member $member): ?DelegatedAccess
    {
        $team = $member->getTeam();

        return $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $team->getOwner(),
            'type' => $team->getScope(),
        ]);
    }

    public function removeDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        if (0 === \count($delegatedAccess->getAccesses())) {
            $this->entityManager->remove($delegatedAccess);
        } else {
            $delegatedAccess->setScopeFeatures([]);
        }

        $this->entityManager->flush();

        $this->tokenRevocationAuthority->revokeUserTokens($delegatedAccess->getDelegated());
    }
}
