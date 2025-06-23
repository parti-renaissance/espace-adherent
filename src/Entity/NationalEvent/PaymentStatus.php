<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityTimestampableTrait;
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

    public function __construct(Payment $payment, array $payload = [])
    {
        $this->payment = $payment;
        $this->payload = $payload;
    }

    public function isSuccess(): bool
    {
        return isset($this->payload['STATUS']) && '9' === $this->payload['STATUS'];
    }
}
