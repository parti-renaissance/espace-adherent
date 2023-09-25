<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\Reporting\DeclaredMandateHistory;
use App\Repository\Reporting\DeclaredMandateHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeclaredMandateHistoryHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly DeclaredMandateHistoryRepository $declaredMandateHistoryRepository)
    {
    }

    /**
     * @param array|string[] $addedMandates
     * @param array|string[] $removedMandates
     */
    public function handle(Adherent $adherent, array $addedMandates, array $removedMandates): void
    {
        $history = $this->declaredMandateHistoryRepository->findNotNotifiedForAdherent($adherent);

        if ($history) {
            $history->setRemovedMandates(array_diff($history->getRemovedMandates(), $addedMandates));
            $history->setAddedMandates(array_diff($history->getAddedMandates(), $removedMandates));

            if (empty($history->getAddedMandates()) && empty($history->getRemovedMandates())) {
                $this->entityManager->remove($history);
            }
        } else {
            $history = new DeclaredMandateHistory($adherent, $addedMandates, $removedMandates);

            $this->entityManager->persist($history);
        }

        $this->entityManager->flush();
    }
}
