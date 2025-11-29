<?php

declare(strict_types=1);

namespace App\ElectedRepresentative\Contribution;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Contribution;
use App\GoCardless\ClientInterface;
use App\GoCardless\Subscription;
use App\Repository\ElectedRepresentative\ContributionRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use GoCardlessPro\Core\Exception\InvalidStateException;

class ContributionRequestHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientInterface $gocardless,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly ContributionRepository $contributionRepository,
    ) {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if ($lastContribution) {
            try {
                $this->cancelPreviousSubscription($lastContribution);
            } catch (InvalidStateException $exception) {
            }
        }

        $subscription = $this->createSubscription($contributionRequest, $adherent, $lastContribution?->gocardlessCustomerId);

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $contribution = Contribution::fromSubscription($electedRepresentative, $subscription);

        $electedRepresentative->addContribution($contribution);
        $electedRepresentative->setLastContribution($contribution);
        $electedRepresentative->setContributionStatus(ContributionStatusEnum::ELIGIBLE);
        $electedRepresentative->setContributedAt(new \DateTime());

        $this->entityManager->flush();
    }

    private function cancelPreviousSubscription(Contribution $contribution): void
    {
        if ($bankAccountId = $contribution->gocardlessBankAccountId) {
            $lastBankAccount = $this->gocardless->disableBankAccount($bankAccountId);

            $contribution->gocardlessBankAccountEnabled = $lastBankAccount->enabled;
        }

        if ($mandateId = $contribution->gocardlessMandateId) {
            $lastMandate = $this->gocardless->cancelMandate($mandateId);

            $contribution->gocardlessMandateStatus = $lastMandate->status;
        }

        if ($subscriptionId = $contribution->gocardlessSubscriptionId) {
            $lastSubscription = $this->gocardless->cancelSubscription($subscriptionId);

            $contribution->gocardlessSubscriptionStatus = $lastSubscription->status;
        }
    }

    private function createSubscription(
        ContributionRequest $contributionRequest,
        Adherent $adherent,
        ?string $customerId = null,
    ): Subscription {
        $metadata = $this->createMetadata($adherent);

        $customer = $customerId
            ? $this->gocardless->getCustomer($customerId)
            : $this->gocardless->createCustomer(
                $adherent->getEmailAddress(),
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getAddress(),
                $adherent->getCityName(),
                $adherent->getPostalCode(),
                $adherent->getCountry(),
                $metadata
            );

        $bankAccount = $this->gocardless->createBankAccount(
            $customer,
            $contributionRequest->iban,
            $contributionRequest->accountName,
            $metadata
        );

        $mandate = $this->gocardless->createMandate($bankAccount, $metadata);

        $subscription = $this->gocardless->createSubscription(
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
