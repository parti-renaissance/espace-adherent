<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table('national_event_inscription_payment_status')]
class PaymentStatus
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\ManyToOne(inversedBy: 'statuses')]
    public Payment $payment;

    #[ORM\ManyToOne]
    public ?EventInscription $inscription = null;

    #[ORM\Column(type: 'json')]
    public array $payload = [];

    public function __construct(Payment $payment, array $payload = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->payment = $payment;
        $this->payload = $payload;
    }

    public function isSuccess(): bool
    {
        return isset($this->payload['STATUS']) && \in_array($this->payload['STATUS'], [5, 9]);
    }
}
