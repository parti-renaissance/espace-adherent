<?php

declare(strict_types=1);

namespace App\Donation;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

final readonly class DonationHistoryItem
{
    public function __construct(
        private \DateTimeInterface $date,
        private int $amount,
        private string $transactionType,
        private DonationSemanticType $type,
        private DonationGlobalStatus $status,
        private Uuid $uuid,
        private ?int $donatorId = null,
        private ?string $donatorFullName = null,
        private ?string $donatorIdentifier = null,
    ) {
    }

    #[Groups(['donation_read'])]
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    #[Groups(['donation_read'])]
    public function getAmount(): float
    {
        return $this->amount / 100;
    }

    #[Groups(['donation_read'])]
    public function getType(): string
    {
        return $this->type->value;
    }

    #[Groups(['donation_read'])]
    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    #[Groups(['donation_read'])]
    public function getStatus(): string
    {
        return $this->status->value;
    }

    #[Groups(['donation_read'])]
    public function getUuid(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function getTypeEnum(): DonationSemanticType
    {
        return $this->type;
    }

    public function getStatusEnum(): DonationGlobalStatus
    {
        return $this->status;
    }

    public function isSubscription(): bool
    {
        return DonationSemanticType::RECURRING === $this->type;
    }

    public function isMembership(): bool
    {
        return DonationSemanticType::MEMBERSHIP === $this->type;
    }

    public function getDonatorId(): ?int
    {
        return $this->donatorId;
    }

    public function getDonatorFullName(): ?string
    {
        return $this->donatorFullName;
    }

    public function getDonatorIdentifier(): ?string
    {
        return $this->donatorIdentifier;
    }
}
