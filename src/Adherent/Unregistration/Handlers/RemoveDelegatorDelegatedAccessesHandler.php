<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MyTeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemoveDelegatorDelegatedAccessesHandler implements UnregistrationAdherentHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private MyTeamRepository $myTeamRepository;
    private DelegatedAccessRepository $delegatedAccessRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MyTeamRepository $myTeamRepository,
        DelegatedAccessRepository $delegatedAccessRepository
    ) {
        $this->entityManager = $entityManager;
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
            $delegatedAccesses = $this->delegatedAccessRepository->findBy([
                'delegator' => $team->getOwner(),
                'type' => $team->getScope(),
            ]);

            array_walk($delegatedAccesses, function ($entity) {
                $this->entityManager->remove($entity);
            });
        }

        $this->entityManager->flush();
    }
}
