<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

class DeclarationResult
{
    public function __construct(
        public readonly bool $paymentStepRequired,
        public readonly int $currentContributionAmount,
    ) {
    }
}
