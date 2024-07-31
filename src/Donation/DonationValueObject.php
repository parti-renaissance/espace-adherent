<?php

namespace App\Donation;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

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

    #[Groups(['donation_read'])]
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    #[Groups(['donation_read'])]
    #[SerializedName('amount')]
    public function getAmountInEuros(): float
    {
        return $this->amount / 100;
    }

    #[Groups(['donation_read'])]
    public function getType(): string
    {
        return $this->type;
    }

    #[Groups(['donation_read'])]
    public function isSubscription(): bool
    {
        return $this->subscription;
    }

    #[Groups(['donation_read'])]
    public function isMembership(): bool
    {
        return $this->membership;
    }

    #[Groups(['donation_read'])]
    public function getStatus(): string
    {
        return $this->status;
    }
}
