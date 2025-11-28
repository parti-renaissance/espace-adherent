<?php

declare(strict_types=1);

namespace App\MyTeam\Listener;

use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\MyTeam\DelegatedAccessManager;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\GeneralScopeGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdherentChangeListener implements EventSubscriberInterface
{
    private MyTeamRepository $myTeamRepository;
    private DelegatedAccessRepository $delegatedAccessRepository;
    private DelegatedAccessManager $delegatedAccessManager;
    private GeneralScopeGenerator $scopeGenerator;

    public function __construct(
        MyTeamRepository $myTeamRepository,
        DelegatedAccessRepository $delegatedAccessRepository,
        DelegatedAccessManager $delegatedAccessManager,
        GeneralScopeGenerator $scopeGenerator,
    ) {
        $this->myTeamRepository = $myTeamRepository;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
        $this->delegatedAccessManager = $delegatedAccessManager;
        $this->scopeGenerator = $scopeGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_UPDATED_IN_ADMIN => 'updateDelegatorDelegatedAccesses',
        ];
    }

    public function updateDelegatorDelegatedAccesses(UserEvent $event): void
    {
        $adherent = $event->getAdherent();
        $teams = $this->myTeamRepository->findBy(['owner' => $adherent]);

        $delegatedScopes = $this->delegatedAccessManager->getDelegatedScopes($adherent);

        foreach ($adherent->getZoneBasedRoles() as $zoneBasedRole) {
            if (false !== ($index = array_search($zoneBasedRole->getType(), $delegatedScopes))) {
                unset($delegatedScopes[$index]);
            }

            foreach ($teams as $team) {
                try {
                    $this->scopeGenerator->getGenerator($team->getScope(), $adherent);
                    foreach ($team->getMembers() as $member) {
                        $this->delegatedAccessManager->createDelegatedAccessForMember($member);
                    }
                } catch (NotFoundScopeGeneratorException $e) {
                }
            }
        }

        foreach ($delegatedScopes as $scope) {
            $this->delegatedAccessRepository->removeFromDelegator($adherent, $scope);
        }
    }
}
