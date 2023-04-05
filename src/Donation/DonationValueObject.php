<?php

namespace App\Donation;

class DonationValueObject
{
    private \DateTimeInterface $date;

    private int $amount;

    private string $type;

    private bool $subscription;

    private bool $membership;

    public function __construct(
        \DateTimeInterface $date,
        int $amount,
        string $type,
        bool $subscription,
        bool $membership
    ) {
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
        $this->subscription = $subscription;
        $this->membership = $membership;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAmountInEuros(): int
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
}
