<?php

namespace App\GoCardless;

use App\AppCodeEnum;
use App\ElectedRepresentative\Contribution\ContributionRequest;
use App\Entity\Adherent;

class ElectedRepresentativeContributionHandler
{
    public function __construct(private readonly ClientInterface $goCardless)
    {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $metadata = [
            'adherent_uuid' => $adherent->getUuidAsString(),
            'source' => AppCodeEnum::RENAISSANCE,
        ];

        $customer = $this->goCardless->createCustomer(
            $adherent->getEmailAddress(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $metadata
        );

        $bankAccount = $this->goCardless->createBankAccount(
            $customer,
            $contributionRequest->iban,
            $contributionRequest->accountName,
            $metadata
        );

        $mandate = $this->goCardless->createMandate($bankAccount, $metadata);

        $this->goCardless->createSubscription(
            $mandate,
            $contributionRequest->getContributionAmount(),
            $metadata
        );
    }
}
