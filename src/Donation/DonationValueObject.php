<?php

declare(strict_types=1);

namespace App\Donation;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

class DonationValueObject
{
    public function __construct(
        private \DateTimeInterface $date,
        private int $amount,
        private string $type,
        private bool $subscription,
        private bool $membership,
        private string $status,
        private UuidInterface $uuid,
        private int $donatorId,
        private string $donatorIdentifier,
        private string $donatorFullName,
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

    #[Groups(['donation_read'])]
    public function getUuid(): string
    {
        return $this->uuid->toString();
    }

    public function getDonatorId(): int
    {
        return $this->donatorId;
    }

    public function getDonatorFullName(): string
    {
        return $this->donatorFullName;
    }

    public function getDonatorIdentifier(): string
    {
        return $this->donatorIdentifier;
    }
}
