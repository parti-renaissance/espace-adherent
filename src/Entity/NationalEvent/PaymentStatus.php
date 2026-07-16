<?php

declare(strict_types=1);

namespace App\Entity\NationalEvent;

use App\Entity\EntityTimestampableTrait;
use App\NationalEvent\PaymentStatusEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('national_event_inscription_payment_status')]
#[ORM\UniqueConstraint(columns: ['payment_id', 'worldline_payment_id', 'status_code'])]
class PaymentStatus
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'statuses')]
    public Payment $payment;

    #[ORM\Column(type: 'json')]
    public array $payload = [];

    #[ORM\Column(nullable: true)]
    public ?string $worldlinePaymentId = null;

    #[ORM\Column(nullable: true)]
    public ?string $statusCode = null;

    #[ORM\Column(enumType: PaymentStatusEnum::class, nullable: true)]
    public ?PaymentStatusEnum $status = null;

    public function __construct(Payment $payment, array $payload = [])
    {
        $this->payment = $payment;
        $this->payload = $payload;
    }

    public function isSuccess(): bool
    {
        return PaymentStatusEnum::CONFIRMED === $this->getStatus();
    }

    public function getStatus(): PaymentStatusEnum
    {
        if (null !== $this->status) {
            return $this->status;
        }

        if (!isset($this->payload['STATUS'])) {
            return PaymentStatusEnum::UNKNOWN;
        }

        return match ((string) $this->payload['STATUS']) {
            '8' => PaymentStatusEnum::REFUNDED,
            '9' => PaymentStatusEnum::CONFIRMED,
            default => PaymentStatusEnum::ERROR,
        };
    }
}
