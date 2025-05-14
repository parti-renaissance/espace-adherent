<?php

namespace App\Agora;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use App\History\UserActionHistoryHandler;
use App\Repository\AgoraMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;

class AgoraMembershipHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AgoraMembershipRepository $agoraMembershipRepository,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
    }

    public function isMember(Adherent $adherent, Agora $agora): bool
    {
        return $this->agoraMembershipRepository->count(['adherent' => $adherent, 'agora' => $agora]) > 0;
    }

    public function add(Adherent $adherent, Agora $agora): void
    {
        $agoraMembership = new AgoraMembership();
        $agoraMembership->agora = $agora;
        $agoraMembership->adherent = $adherent;

        $this->entityManager->persist($agoraMembership);
        $this->entityManager->flush();

        $this->userActionHistoryHandler->createAgoraMembershipAdd($adherent, $agora);
    }

    public function remove(Adherent $adherent, Agora $agora): void
    {
        $agoraMembership = $this->agoraMembershipRepository->findMembership($agora, $adherent);

        if (!$agoraMembership) {
            return;
        }

        $this->entityManager->remove($agoraMembership);
        $this->entityManager->flush();

        $this->userActionHistoryHandler->createAgoraMembershipRemove($adherent, $agora);
    }
}
