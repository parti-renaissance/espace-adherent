<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\GoCardless\ClientInterface;
use App\GoCardless\Subscription;
use App\Repository\Contribution\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use GoCardlessPro\Core\Exception\InvalidStateException;
use Symfony\Component\Messenger\MessageBusInterface;

class ContributionRequestHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientInterface $gocardless,
        private readonly ContributionRepository $contributionRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function cancelLastContribution(Adherent $adherent): ?Contribution
    {
        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if ($lastContribution) {
            try {
                $this->cancelContributionSubscription($lastContribution);
            } catch (InvalidStateException $exception) {
            }
        }

        return $lastContribution;
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $lastContribution = $this->cancelLastContribution($adherent);

        $subscription = $this->createSubscription($contributionRequest, $adherent, $lastContribution?->gocardlessCustomerId);

        $contribution = Contribution::fromSubscription($adherent, $subscription);

        $adherent->addContribution($contribution);
        $adherent->setLastContribution($contribution);
        $adherent->setContributionStatus(ContributionStatusEnum::ELIGIBLE);
        $adherent->setContributedAt(new \DateTime());

        $this->entityManager->flush();

        $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));
    }

    private function cancelContributionSubscription(Contribution $contribution): void
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
