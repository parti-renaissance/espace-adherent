<?php

namespace App\ElectedRepresentative\Contribution;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Contribution;
use App\GoCardless\Client;
use App\GoCardless\Subscription;
use App\Repository\ElectedRepresentative\ContributionRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContributionRequestHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Client $goCardless,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly ContributionRepository $contributionRepository
    ) {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if ($lastContribution) {
            $this->cancelPreviousSubscription($lastContribution);
        }

        $subscription = $this->createSubscription($contributionRequest, $adherent, $lastContribution?->gocardlessCustomerId);

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $electedRepresentative->setLastContributionDate(new \DateTime());
        $electedRepresentative->addContribution(Contribution::fromSubscription($electedRepresentative, $subscription));

        $this->entityManager->flush();
    }

    private function cancelPreviousSubscription(Contribution $contribution): void
    {
        if ($bankAccountId = $contribution->gocardlessBankAccountId) {
            $lastBankAccount = $this->goCardless->disableBankAccount($bankAccountId);

            $contribution->gocardlessBankAccountEnabled = $lastBankAccount->enabled;
        }

        if ($mandateId = $contribution->gocardlessMandateId) {
            $lastMandate = $this->goCardless->cancelMandate($mandateId);

            $contribution->gocardlessMandateStatus = $lastMandate->status;
        }

        if ($subscriptionId = $contribution->gocardlessSubscriptionId) {
            $lastSubscription = $this->goCardless->cancelSubscription($subscriptionId);

            $contribution->gocardlessSubscriptionStatus = $lastSubscription->status;
        }
    }

    private function createSubscription(
        ContributionRequest $contributionRequest,
        Adherent $adherent,
        ?string $customerId = null
    ): Subscription {
        $metadata = $this->createMetadata($adherent);

        $customer = $customerId
            ? $this->goCardless->getCustomer($customerId)
            : $this->goCardless->createCustomer(
                $adherent->getEmailAddress(),
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getAddress(),
                $adherent->getCityName(),
                $adherent->getPostalCode(),
                $adherent->getCountry(),
                $metadata
            )
        ;

        $bankAccount = $this->goCardless->createBankAccount(
            $customer,
            $contributionRequest->iban,
            $contributionRequest->accountName,
            $metadata
        );

        $mandate = $this->goCardless->createMandate($bankAccount, $metadata);

        $subscription = $this->goCardless->createSubscription(
            $mandate,
            $contributionRequest->getContributionAmount(),
            $metadata
        );

        return new Subscription($customer, $bankAccount, $mandate, $subscription);
    }

    private function createMetadata(Adherent $adherent): array
    {
        return [
            'adherent_uuid' => $adherent->getUuidAsString(),
            'source' => AppCodeEnum::RENAISSANCE,
        ];
    }
}
