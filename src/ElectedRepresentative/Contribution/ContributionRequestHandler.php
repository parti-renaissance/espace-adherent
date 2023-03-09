<?php

namespace App\ElectedRepresentative\Contribution;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Contribution;
use App\GoCardless\ElectedRepresentativeContributionHandler;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContributionRequestHandler
{
    public function __construct(
        private readonly ElectedRepresentativeContributionHandler $electedRepresentativeContributionHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
    }

    public function handleMandate(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $subscription = $this->electedRepresentativeContributionHandler->handle($contributionRequest, $adherent);

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $electedRepresentative->setLastContributionDate(new \DateTime());
        $electedRepresentative->addContribution(Contribution::fromSubscription($electedRepresentative, $subscription));

        $this->entityManager->flush();
    }
}
