<?php

namespace App\Donation;

class DonationValueObject
{
    public function __construct(
        private \DateTimeInterface $date,
        private int $amount,
        private string $type,
        private bool $subscription,
        private bool $membership,
        private string $status
    ) {
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAmountInEuros(): float
    {
        return $this->amount / 100;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isSubscription(): bool
    {
        return $this->subscription;
    }

    public function isMembership(): bool
    {
        return $this->membership;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
