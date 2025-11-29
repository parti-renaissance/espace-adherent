<?php

declare(strict_types=1);

namespace App\Entity\Contribution;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Ohme\PaymentStatusEnum;
use App\Repository\Contribution\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'contribution_payment')]
class Payment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(length: 50)]
    public ?string $ohmeId = null;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $date = null;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(length: 50)]
    public ?string $method = null;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $status = null;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(type: 'integer')]
    public ?int $amount = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'payments')]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function fromArray(Adherent $adherent, array $data): self
    {
        $payment = new self();

        $payment->adherent = $adherent;
        $payment->ohmeId = $data['id'];
        $payment->date = $data['date'] ? \DateTime::createFromFormat('Y-m-d\TH:i:sP', $data['date']) : null;
        $payment->method = $data['payment_method_name'];
        $payment->status = $data['payment_status'];
        $payment->amount = (int) round($data['amount']);

        return $payment;
    }

    #[Groups(['adherent_elect_read'])]
    public function getStatusLabel(): ?string
    {
        if ($this->status && isset(PaymentStatusEnum::LABELS[$this->status])) {
            return PaymentStatusEnum::LABELS[$this->status];
        }

        return $this->status;
    }

    public function isConfirmed(): bool
    {
        return \in_array($this->status, PaymentStatusEnum::CONFIRMED_PAYMENT_STATUSES, true);
    }
}
