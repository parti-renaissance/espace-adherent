<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\GoCardless\ClientInterface;
use App\GoCardless\Subscription;
use App\Repository\Contribution\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use GoCardlessPro\Core\Exception\ApiException;
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

    public function handleDeclaration(Adherent $adherent, int $revenueAmount): DeclarationResult
    {
        $previousDeclaration = $adherent->getLastRevenueDeclaration();
        $previousExpectedAmount = null !== $previousDeclaration
            ? ContributionAmountUtils::getContributionAmount($previousDeclaration->amount)
            : null;

        $lastContribution = $adherent->getLastContribution();
        $hasActiveContribution = null !== $lastContribution && $lastContribution->isActive();

        $needContribution = ContributionAmountUtils::needContribution($revenueAmount);
        $newExpectedAmount = ContributionAmountUtils::getContributionAmount($revenueAmount);

        $adherent->addRevenueDeclaration($revenueAmount);
        $adherent->setContributionStatus(
            $needContribution ? ContributionStatusEnum::ELIGIBLE : ContributionStatusEnum::NOT_ELIGIBLE
        );

        if (!$needContribution) {
            if ($hasActiveContribution) {
                $this->cancelLastContribution($adherent);
            }
            $adherent->setContributedAt(new \DateTime());
        } elseif ($hasActiveContribution && $previousExpectedAmount !== $newExpectedAmount) {
            $this->handleAmountChange($adherent, $newExpectedAmount);
        }

        $this->entityManager->flush();
        $this->dispatchTagRefresh($adherent);

        return new DeclarationResult(
            paymentStepRequired: $needContribution && !$hasActiveContribution,
            currentContributionAmount: $needContribution ? $newExpectedAmount : 0,
        );
    }

    public function cancelLastContribution(Adherent $adherent): ?Contribution
    {
        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if ($lastContribution) {
            try {
                $this->cancelContributionSubscription($lastContribution);
            } catch (InvalidStateException $exception) {
            }

            $this->entityManager->flush();
            $this->dispatchTagRefresh($adherent);
        }

        return $lastContribution;
    }

    public function handle(ContributionPaymentRequest $paymentRequest, Adherent $adherent): Contribution
    {
        $lastContribution = $this->cancelLastContribution($adherent);

        $subscription = $this->createSubscription($paymentRequest, $adherent, $lastContribution?->gocardlessCustomerId);

        $contribution = Contribution::fromSubscription($adherent, $subscription);

        $adherent->addContribution($contribution);
        $adherent->setLastContribution($contribution);
        $adherent->setContributionStatus(ContributionStatusEnum::ELIGIBLE);
        $adherent->setContributedAt(new \DateTime());

        $this->entityManager->flush();
        $this->dispatchTagRefresh($adherent);

        return $contribution;
    }

    public function handleAmountChange(Adherent $adherent, int $newContributionAmount): ?Contribution
    {
        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if (!$lastContribution || !$lastContribution->gocardlessSubscriptionId) {
            return null;
        }

        try {
            $updatedSubscription = $this->gocardless->updateSubscriptionAmount(
                $lastContribution->gocardlessSubscriptionId,
                $newContributionAmount,
            );
            $lastContribution->gocardlessSubscriptionStatus = $updatedSubscription->status;
            $contribution = $lastContribution;
        } catch (ApiException $exception) {
            if (!$this->isAmendmentsLimitReached($exception) || !$lastContribution->gocardlessMandateId) {
                throw $exception;
            }
            $contribution = $this->replaceSubscriptionOnExistingMandate($adherent, $lastContribution, $newContributionAmount);
        }

        $adherent->setContributedAt(new \DateTime());

        $this->entityManager->flush();
        $this->dispatchTagRefresh($adherent);

        return $contribution;
    }

    private function dispatchTagRefresh(Adherent $adherent): void
    {
        $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
    }

    private function isAmendmentsLimitReached(ApiException $exception): bool
    {
        foreach ($exception->getErrors() as $error) {
            if (($error->reason ?? null) === 'number_of_subscription_amendments_exceeded') {
                return true;
            }
        }

        return false;
    }

    /**
     * Fallback used when the GoCardless amendments limit (10 per subscription) is reached.
     * Cancels the existing subscription and creates a new one on the same mandate (no re-IBAN prompt).
     */
    private function replaceSubscriptionOnExistingMandate(
        Adherent $adherent,
        Contribution $previousContribution,
        int $newContributionAmount,
    ): Contribution {
        try {
            $cancelled = $this->gocardless->cancelSubscription($previousContribution->gocardlessSubscriptionId);
            $previousContribution->gocardlessSubscriptionStatus = $cancelled->status;
        } catch (InvalidStateException) {
        }

        $newSubscription = $this->gocardless->createSubscription(
            $previousContribution->gocardlessMandateId,
            $newContributionAmount,
            $this->createMetadata($adherent),
        );

        $contribution = Contribution::fromPreviousWithNewSubscription($previousContribution, $newSubscription);

        $adherent->addContribution($contribution);
        $adherent->setLastContribution($contribution);

        return $contribution;
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
        ContributionPaymentRequest $paymentRequest,
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
            $paymentRequest->iban,
            $paymentRequest->accountName,
            $metadata
        );

        $mandate = $this->gocardless->createMandate($bankAccount, $metadata);

        $amount = ContributionAmountUtils::getContributionAmount(
            $adherent->getLastRevenueDeclaration()->amount,
        );

        $subscription = $this->gocardless->createSubscription(
            $mandate->id,
            $amount,
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
