<?php

namespace App\GoCardless;

use App\AppCodeEnum;
use App\ElectedRepresentative\Contribution\ContributionRequest;
use App\Entity\Adherent;
use App\Repository\ElectedRepresentative\ContributionRepository;

class ElectedRepresentativeContributionHandler
{
    public function __construct(
        private readonly ClientInterface $goCardless,
        private readonly ContributionRepository $contributionRepository
    ) {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): Subscription
    {
        $metadata = [
            'adherent_uuid' => $adherent->getUuidAsString(),
            'source' => AppCodeEnum::RENAISSANCE,
        ];

        $lastContribution = $this->contributionRepository->findLastAdherentContribution($adherent);

        if ($lastContribution) {
            if ($bankAccountId = $lastContribution->gocardlessBankAccountId) {
                $lastBankAccount = $this->goCardless->disableBankAccount($bankAccountId);

                $lastContribution->gocardlessBankAccountEnabled = $lastBankAccount->enabled;
            }

            if ($mandateId = $lastContribution->gocardlessMandateId) {
                $lastMandate = $this->goCardless->cancelMandate($mandateId);

                $lastContribution->gocardlessMandateStatus = $lastMandate->status;
            }

            if ($subscriptionId = $lastContribution->gocardlessSubscriptionId) {
                $lastSubscription = $this->goCardless->cancelSubscription($subscriptionId);

                $lastContribution->gocardlessSubscriptionStatus = $lastSubscription->status;
            }
        }

        $customer = ($lastContribution && $lastContribution->gocardlessCustomerId)
            ? $this->goCardless->getCustomer($lastContribution->gocardlessCustomerId)
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
}
