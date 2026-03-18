<?php

declare(strict_types=1);

namespace App\Donation;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

final readonly class DonationHistoryItem
{
    public function __construct(
        private \DateTimeInterface $date,
        private int $amount,
        private string $transactionType,
        private DonationSemanticType $type,
        private DonationGlobalStatus $status,
        private UuidInterface $uuid,
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
        return $this->uuid->toString();
    }

    public function getTypeEnum(): DonationSemanticType
    {
        return $this->type;
    }

    public function getStatusEnum(): DonationGlobalStatus
    {
        return $this->status;
    }
}
