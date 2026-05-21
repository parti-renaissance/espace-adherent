<?php

declare(strict_types=1);

namespace App\Entity\Contribution;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Ohme\PaymentStatusEnum;
use App\Repository\Contribution\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'contribution_payment')]
class Payment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(length: 50)]
    public ?string $ohmeId = null;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $date = null;

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

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
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
