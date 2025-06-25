<?php

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

    #[ORM\Column(nullable: true)]
    public ?string $transport = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $withDiscount = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $amount = 0;

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: PaymentStatus::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\Column(type: 'json')]
    public array $payload = [];

    public function __construct(UuidInterface $uuid, EventInscription $inscription, array $payload = [])
    {
        $this->uuid = $uuid;
        $this->inscription = $inscription;
        $this->amount = $inscription->transportCosts;
        $this->transport = $inscription->transport;
        $this->withDiscount = $inscription->withDiscount;
        $this->payload = $payload;
        $this->statuses = new ArrayCollection();
    }

    public function addStatus(PaymentStatus $status): void
    {
        $this->statuses->add($status);
        $this->status = $status->isSuccess() ? PaymentStatusEnum::CONFIRMED : PaymentStatusEnum::ERROR;
    }

    /** @return PaymentStatus[] */
    public function getStatuses(): array
    {
        return $this->statuses->toArray();
    }

    public function getAmountInEuro(): int
    {
        return (int) round($this->amount / 100);
    }
}
