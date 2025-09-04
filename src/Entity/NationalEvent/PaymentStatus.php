<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityTimestampableTrait;
use App\NationalEvent\PaymentStatusEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('national_event_inscription_payment_status')]
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

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $rawBody = null;

    public function __construct(Payment $payment, array $payload = [], ?string $rawBody = null)
    {
        $this->payment = $payment;
        $this->payload = $payload;
        $this->rawBody = $rawBody;
    }

    public function isSuccess(): bool
    {
        return PaymentStatusEnum::CONFIRMED === $this->getStatus();
    }

    public function getStatus(): PaymentStatusEnum
    {
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
