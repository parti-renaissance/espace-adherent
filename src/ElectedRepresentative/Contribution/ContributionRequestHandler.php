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
        private readonly ElectedRepresentativeContributionHandler $goCardless,
        private readonly EntityManagerInterface $entityManager,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
    }

    public function handleMandate(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $customerId = $this->goCardless->handle($contributionRequest, $adherent);

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $electedRepresentative->setLastContributionDate(new \DateTime());
        $electedRepresentative->addContribution(Contribution::createMandate($electedRepresentative, $customerId));

        $this->entityManager->flush();
    }
}
