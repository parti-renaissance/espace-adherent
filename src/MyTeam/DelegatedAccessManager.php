<?php

namespace App\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\EntityManagerInterface;

class DelegatedAccessManager
{
    private EntityManagerInterface $entityManager;
    private DelegatedAccessRepository $delegatedAccessRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DelegatedAccessRepository $delegatedAccessRepository
    ) {
        $this->entityManager = $entityManager;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
    }

    public function createDelegatedAccessForMember(Member $member): void
    {
        $delegatedAccess = $this->findDelegatedAccess($member);
        if ($delegatedAccess) {
            $delegatedAccess->setScopeFeatures($member->getScopeFeatures());
            $this->entityManager->flush();

            return;
        }

        $delegatedAccess = new DelegatedAccess();
        $delegatedAccess->setDelegator($member->getTeam()->getOwner());
        $delegatedAccess->setDelegated($member->getAdherent());
        $delegatedAccess->setType($member->getTeam()->getScope());
        $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);
        $delegatedAccess->setScopeFeatures($member->getScopeFeatures());

        $this->entityManager->persist($delegatedAccess);
        $this->entityManager->flush();
    }

    public function updateDelegatedAccessForMember(Member $member): void
    {
        $delegatedAccess = $this->findDelegatedAccess($member);
        if ($delegatedAccess) {
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
    }
}
