<?php

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
        $adherent = $event->getUser();
        $teams = $this->myTeamRepository->findBy(['owner' => $adherent]);

        foreach ($teams as $team) {
            try {
                $this->scopeGenerator->getGenerator($team->getScope(), $adherent);
                foreach ($team->getMembers() as $member) {
                    $this->delegatedAccessManager->createDelegatedAccessForMember($member);
                }
            } catch (NotFoundScopeGeneratorException $e) {
                $this->delegatedAccessRepository->removeFromDelegator($team->getOwner(), $team->getScope());
            }
        }
    }
}
