<?php

declare(strict_types=1);

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table('national_event_inscription_payment')]
class Payment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'payments')]
    public EventInscription $inscription;

    #[ORM\Column(enumType: PaymentStatusEnum::class, options: ['default' => PaymentStatusEnum::PENDING])]
    public PaymentStatusEnum $status = PaymentStatusEnum::PENDING;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $toRefund = false;

    #[ORM\Column(nullable: true)]
    public ?string $packagePlan = null;

    #[ORM\Column(nullable: true)]
    public ?string $packageDonation = null;

    #[ORM\Column(nullable: true)]
    public ?string $transport = null;

    #[ORM\Column(nullable: true)]
    public ?string $accommodation = null;

    #[ORM\Column(nullable: true)]
    public ?string $visitDay = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $withDiscount = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $amount = 0;

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: PaymentStatus::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\Column(type: 'json')]
    public array $payload = [];

    #[ORM\ManyToOne(targetEntity: self::class)]
    public ?self $replacement = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $expiredCheckedAt = null;

    public function __construct(
        UuidInterface $uuid,
        EventInscription $inscription,
        ?int $amount = null,
        ?string $visitDay = null,
        ?string $transport = null,
        ?string $accommodation = null,
        ?string $packagePlan = null,
        ?string $packageDonation = null,
        ?bool $withDiscount = null,
        array $payload = [],
    ) {
        $this->uuid = $uuid;
        $this->inscription = $inscription;
        $this->visitDay = $visitDay;
        $this->amount = $amount;
        $this->transport = $transport;
        $this->accommodation = $accommodation;
        $this->packagePlan = $packagePlan;
        $this->packageDonation = $packageDonation;
        $this->withDiscount = $withDiscount;
        $this->payload = $payload;
        $this->statuses = new ArrayCollection();
    }

    public function addStatus(PaymentStatus $status): void
    {
        $this->statuses->add($status);
        $this->status = $status->getStatus();
    }

    /** @return PaymentStatus[] */
    public function getStatuses(): array
    {
        return $this->statuses->toArray();
    }

    public function getAmountInEuro(): float
    {
        return $this->amount / 100;
    }

    public function isConfirmed(): bool
    {
        return PaymentStatusEnum::CONFIRMED === $this->status;
    }

    public function markAsToRefund(?self $replacement = null): void
    {
        $this->replacement = $replacement;
        $this->toRefund = true;
    }

    public function isPending(): bool
    {
        return PaymentStatusEnum::PENDING === $this->status;
    }

    public function isExpired(): bool
    {
        return PaymentStatusEnum::EXPIRED === $this->status;
    }
}
