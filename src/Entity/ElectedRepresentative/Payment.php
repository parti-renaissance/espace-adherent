<?php

namespace App\Entity\ElectedRepresentative;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="elected_representative_payment")
 */
class Payment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $ohmeId = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"elected_representative_read"})
     */
    public ?\DateTime $date = null;

    /**
     * @ORM\Column(length=50)
     *
     * @Groups({"elected_representative_read"})
     */
    public ?string $method = null;

    /**
     * @ORM\Column(length=50)
     *
     * @Groups({"elected_representative_read"})
     */
    public ?string $status = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\ElectedRepresentative", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?ElectedRepresentative $electedRepresentative = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function fromArray(ElectedRepresentative $electedRepresentative, array $data): self
    {
        $payment = new self();

        $payment->electedRepresentative = $electedRepresentative;
        $payment->ohmeId = $data['id'];
        $payment->date = $data['date'] ?? \DateTime::createFromFormat('Y-m-d\TH:i:sP', $data['date']);
        $payment->method = $data['payment_method_name'];
        $payment->status = $data['payment_status'];

        return $payment;
    }
}
