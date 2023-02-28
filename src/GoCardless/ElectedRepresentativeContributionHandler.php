<?php

namespace App\GoCardless;

use App\ElectedRepresentative\Contribution\ContributionRequest;
use App\Entity\Adherent;

class ElectedRepresentativeContributionHandler
{
    public function __construct(private readonly Client $goCardless)
    {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $customer = $this->goCardless->createCustomer(
            $adherent->getEmailAddress(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            [
                'adherent_uuid' => $adherent->getUuidAsString(),
            ]
        );

        $bankAccount = $this->goCardless->createBankAccount(
            $customer,
            $contributionRequest->iban,
            $contributionRequest->accountName
        );

        $mandate = $this->goCardless->createMandate($bankAccount);

        $this->goCardless->createSubscription(
            $mandate,
            $contributionRequest->getContributionAmount(),
            ['adherent_uuid' => $adherent->getUuidAsString()],
        );
    }
}
