<?php

declare(strict_types=1);

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MyTeamRepository;

class RemoveDelegatorDelegatedAccessesHandler implements UnregistrationAdherentHandlerInterface
{
    private MyTeamRepository $myTeamRepository;
    private DelegatedAccessRepository $delegatedAccessRepository;

    public function __construct(
        MyTeamRepository $myTeamRepository,
        DelegatedAccessRepository $delegatedAccessRepository,
    ) {
        $this->myTeamRepository = $myTeamRepository;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $teams = $this->myTeamRepository->findBy(['owner' => $adherent]);

        foreach ($teams as $team) {
            $this->delegatedAccessRepository->removeFromDelegator($team->getOwner(), $team->getScope());
        }
    }
}
