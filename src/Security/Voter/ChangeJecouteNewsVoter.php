<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use Symfony\Component\HttpFoundation\RequestStack;

class ChangeJecouteNewsVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_CHANGE_JECOUTE_NEWS';

    private ZoneRepository $zoneRepository;
    private RequestStack $requestStack;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        ZoneRepository $zoneRepository,
        RequestStack $requestStack,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->zoneRepository = $zoneRepository;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    /** @param News $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->authorizationChecker->getScope($this->requestStack->getMasterRequest());
        if (ScopeEnum::NATIONAL === $scope) {
            return !$subject->getSpace();
        }

        if (ScopeEnum::REFERENT === $scope) {
            if (!$zone = $subject->getZone()) {
                return false;
            }

            return $this->zoneRepository->isInZones([$zone], $adherent->getManagedArea()->getZones()->toArray());
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
