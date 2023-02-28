<?php

namespace App\ElectedRepresentative\Contribution;

use App\Entity\Adherent;
use App\GoCardless\ElectedRepresentativeContributionHandler;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContributionRequestHandler
{
    public function __construct(
        private readonly ElectedRepresentativeContributionHandler $goCardless,
        private readonly EntityManagerInterface $entityManager,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $this->goCardless->handle($contributionRequest, $adherent);

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);
        $electedRepresentative->setLastContributionRequestDate(new \DateTime());

        $this->entityManager->flush();
    }
}
