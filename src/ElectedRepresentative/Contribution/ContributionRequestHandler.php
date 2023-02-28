<?php

namespace App\ElectedRepresentative\Contribution;

use App\Entity\Adherent;
use App\GoCardless\ElectedRepresentativeContributionHandler;

class ContributionRequestHandler
{
    public function __construct(private readonly ElectedRepresentativeContributionHandler $goCardless)
    {
    }

    public function handle(ContributionRequest $contributionRequest, Adherent $adherent): void
    {
        $this->goCardless->handle($contributionRequest, $adherent);
    }
}
