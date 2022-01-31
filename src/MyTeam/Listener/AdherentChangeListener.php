<?php

namespace App\MyTeam\Listener;

use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\GeneralScopeGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdherentChangeListener implements EventSubscriberInterface
{
    private MyTeamRepository $myTeamRepository;
    private DelegatedAccessRepository $delegatedAccessRepository;
    private GeneralScopeGenerator $scopeGenerator;

    public function __construct(
        MyTeamRepository $myTeamRepository,
        DelegatedAccessRepository $delegatedAccessRepository,
        GeneralScopeGenerator $scopeGenerator
    ) {
        $this->myTeamRepository = $myTeamRepository;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
        $this->scopeGenerator = $scopeGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_UPDATED_IN_ADMIN => 'removeDelegatorDelegatedAccesses',
        ];
    }

    public function removeDelegatorDelegatedAccesses(UserEvent $event): void
    {
        $adherent = $event->getUser();
        $teams = $this->myTeamRepository->findBy(['owner' => $adherent]);

        foreach ($teams as $team) {
            try {
                $this->scopeGenerator->getGenerator($team->getScope(), $adherent);
            } catch (NotFoundScopeGeneratorException $e) {
                $this->delegatedAccessRepository->removeFromDelegator($team->getOwner(), $team->getScope());
            }
        }
    }
}
