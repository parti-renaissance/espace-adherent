<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxFiscalYearDonation extends Constraint
{
    public string $message = 'donation.max_fiscal_year_donation';
    public int $maxDonationInCents = 750000; // Amount in cents
    public ?string $path = null;

    public function __construct(
        ?int $maxDonationInCents = null,
        ?string $path = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->maxDonationInCents = $maxDonationInCents ?? $this->maxDonationInCents;
        $this->path = $path ?? $this->path;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
