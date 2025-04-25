<?php

namespace App\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\History\UserActionHistoryHandler;
use App\OAuth\TokenRevocationAuthority;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;

class DelegatedAccessManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DelegatedAccessRepository $delegatedAccessRepository,
        private readonly TokenRevocationAuthority $tokenRevocationAuthority,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly DelegatedAccessNotifier $delegatedAccessNotifier,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
    }

    public function createDelegatedAccessForMember(Member $member): void
    {
        if ($newMember = !($delegatedAccess = $this->findDelegatedAccess($member))) {
            $delegatedAccess = new DelegatedAccess();
            $delegatedAccess->setDelegator($member->getTeam()->getOwner());
            $delegatedAccess->setDelegated($member->getAdherent());
            $delegatedAccess->setType($member->getTeam()->getScope());
            $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);

            $this->entityManager->persist($delegatedAccess);
        }

        $delegatedAccess->setScopeFeatures($this->calculateFeatures($member->getScopeFeatures()));

        $this->entityManager->flush();

        if ($newMember) {
            $this->tokenRevocationAuthority->revokeUserTokens($member->getAdherent());
            $this->delegatedAccessNotifier->sendNewDelegatedAccessNotification($delegatedAccess);
            $this->userActionHistoryHandler->createDelegatedAccessAdd($delegatedAccess);
        }
    }

    public function updateDelegatedAccessForMember(Member $member, ?Member $fromMember = null): void
    {
        if ($fromMember && $member->getAdherent() !== $fromMember->getAdherent() && ($fromDelegatedAccess = $this->findDelegatedAccess($fromMember))) {
            $this->removeDelegatedAccess($fromDelegatedAccess);
        }

        if ($delegatedAccess = $this->findDelegatedAccess($member)) {
            if ($member->getScopeFeatures()) {
                $delegatedAccess->setScopeFeatures($this->calculateFeatures($member->getScopeFeatures()));
                $this->entityManager->flush();

                $this->userActionHistoryHandler->createDelegatedAccessEdit($delegatedAccess);

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

        $this->userActionHistoryHandler->createDelegatedAccessRemove($delegatedAccess);
        $this->tokenRevocationAuthority->revokeUserTokens($delegatedAccess->getDelegated());
    }

    private function calculateFeatures(array $memberFeatures): array
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return $memberFeatures;
        }

        return array_values(array_unique(array_merge($scope->getAutomaticallyDelegatableFeatures(), $memberFeatures)));
    }

    public function getDelegatedScopes(Adherent $adherent): array
    {
        return $this->delegatedAccessRepository->findDelegatedScopes($adherent);
    }
}
