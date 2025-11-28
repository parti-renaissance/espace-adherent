<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'elected_representative_payment')]
class Payment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(length: 50)]
    public ?string $ohmeId = null;

    #[Groups(['elected_representative_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $date = null;

    #[Groups(['elected_representative_read'])]
    #[ORM\Column(length: 50)]
    public ?string $method = null;

    #[Groups(['elected_representative_read'])]
    #[ORM\Column(length: 50, nullable: true)]
    public ?string $status = null;

    #[Groups(['elected_representative_read'])]
    #[ORM\Column(type: 'integer')]
    public ?int $amount = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectedRepresentative::class, inversedBy: 'payments')]
    public ?ElectedRepresentative $electedRepresentative = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function fromArray(ElectedRepresentative $electedRepresentative, array $data): self
    {
        $payment = new self();

        $payment->electedRepresentative = $electedRepresentative;
        $payment->ohmeId = $data['id'];
        $payment->date = $data['date'] ? \DateTime::createFromFormat('Y-m-d\TH:i:sP', $data['date']) : null;
        $payment->method = $data['payment_method_name'];
        $payment->status = $data['payment_status'];
        $payment->amount = (int) round($data['amount']);

        return $payment;
    }
}
