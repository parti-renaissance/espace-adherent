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

    #[ORM\ManyToOne(targetEntity: EventInscription::class, inversedBy: 'paymentStatuses')]
    public EventInscription $inscription;

    #[ORM\Column(type: 'json')]
    public array $payload = [];

    public function __construct(EventInscription $inscription, array $payload = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->inscription = $inscription;
        $this->payload = $payload;
    }

    public function isSuccess(): bool
    {
        return isset($this->payload['STATUS']) && \in_array($this->payload['STATUS'], [5, 9]);
    }
}
