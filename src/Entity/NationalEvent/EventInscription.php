<?php

namespace App\Entity\NationalEvent;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Put;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Event\Request\EventInscriptionRequest;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Put(requirements: ['uuid' => '%pattern_uuid%']),
    ],
    normalizationContext: ['groups' => ['event_inscription_read']],
    denormalizationContext: ['groups' => ['event_inscription_update']]
)]
#[ORM\Entity(repositoryClass: EventInscriptionRepository::class)]
#[ORM\Table('national_event_inscription')]
class EventInscription
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\ManyToOne(targetEntity: NationalEvent::class)]
    public NationalEvent $event;

    #[Assert\Choice(InscriptionStatusEnum::STATUSES)]
    #[Groups(['national_event_inscription:webhook', 'event_inscription_update'])]
    #[ORM\Column(options: ['default' => 'pending'])]
    public string $status = InscriptionStatusEnum::PENDING;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(length: 6)]
    public ?string $gender = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column]
    public ?string $firstName = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column]
    public ?string $lastName = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column]
    public ?string $addressEmail = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $postalCode = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $birthPlace = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $accessibility = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $transportNeeds = false;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $volunteer = false;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?array $qualities = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $birthdate = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $joinNewsletter = false;

    public bool $needSendNewsletterConfirmation = false;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $children = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isResponsibilityWaived = false;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isJAM = false;

    #[ORM\Column(nullable: true)]
    public ?string $visitDay = null;

    #[ORM\Column(nullable: true)]
    public ?string $transport = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $withDiscount = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    public ?int $transportCosts = null;

    #[ORM\Column(nullable: true)]
    public ?string $clientIp = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $sessionId = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'json', nullable: true)]
    public $emailCheck;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $confirmedAt = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $ticketSentAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $ticketScannedAt = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $pushSentAt = null;

    #[Groups(['event_inscription_update'])]
    #[ORM\Column(nullable: true)]
    public ?string $ticketCustomDetail = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $ticketQRCodeFile = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(length: 7, nullable: true)]
    public ?string $referrerCode = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referrer = null;

    #[ORM\OneToMany(mappedBy: 'inscription', targetEntity: PaymentStatus::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $paymentStatuses;

    public function __construct(NationalEvent $event, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->event = $event;
        $this->paymentStatuses = new ArrayCollection();
    }

    public function updateFromDuplicate(EventInscription $eventInscription): void
    {
        $this->addressEmail = $eventInscription->addressEmail ?? $this->addressEmail;
        $this->needSendNewsletterConfirmation = !$this->joinNewsletter && $eventInscription->joinNewsletter;
        $this->joinNewsletter = $this->joinNewsletter ?: $eventInscription->joinNewsletter;
        $this->firstName = $eventInscription->firstName ?? $this->firstName;
        $this->lastName = $eventInscription->lastName ?? $this->lastName;
        $this->gender = $eventInscription->gender ?? $this->gender;
        $this->phone = $eventInscription->phone ?? $this->phone;
        $this->postalCode = $eventInscription->postalCode ?? $this->postalCode;
        $this->qualities = $eventInscription->qualities ?? $this->qualities;
        $this->birthPlace = $eventInscription->birthPlace ?? $this->birthPlace;
        $this->accessibility = $eventInscription->accessibility ?? $this->accessibility;
        $this->transportNeeds = $eventInscription->transportNeeds ?? $this->transportNeeds;
        $this->volunteer = $eventInscription->volunteer ?? $this->volunteer;
        $this->birthdate = $eventInscription->birthdate ?? $this->birthdate;
        $this->children = $eventInscription->children ?? $this->children;
        $this->isResponsibilityWaived = $this->isResponsibilityWaived || $eventInscription->isResponsibilityWaived;
        $this->isJAM = $this->isJAM || $eventInscription->isJAM;
    }

    public function updateFromRequest(EventInscriptionRequest $inscriptionRequest): void
    {
        $this->addressEmail = $inscriptionRequest->email;
        $this->needSendNewsletterConfirmation = !$this->joinNewsletter && $inscriptionRequest->allowNotifications;
        $this->joinNewsletter = $this->joinNewsletter ?: $inscriptionRequest->allowNotifications;
        $this->firstName = $inscriptionRequest->firstName;
        $this->lastName = $inscriptionRequest->lastName;
        $this->gender = $inscriptionRequest->civility;
        $this->phone = $inscriptionRequest->phone;
        $this->postalCode = $inscriptionRequest->postalCode;
        $this->qualities = $inscriptionRequest->qualities;
        $this->birthPlace = $inscriptionRequest->birthPlace;
        $this->accessibility = $inscriptionRequest->accessibility;
        $this->transportNeeds = $inscriptionRequest->transportNeeds;
        $this->volunteer = $inscriptionRequest->volunteer;
        $this->birthdate = $inscriptionRequest->birthdate;
        $this->children = $inscriptionRequest->withChildren ? $inscriptionRequest->children : null;
        $this->isResponsibilityWaived = $inscriptionRequest->isResponsibilityWaived;
        $this->isJAM = $inscriptionRequest->isJAM;
        $this->visitDay = $inscriptionRequest->visitDay;
        $this->transport = $inscriptionRequest->transport;
        $this->withDiscount = $inscriptionRequest->withDiscount;
        $this->transportCosts = $this->getTransportAmount();

        if ($this->transportCosts > 0 && InscriptionStatusEnum::PENDING === $this->status) {
            $this->status = InscriptionStatusEnum::WAITING_PAYMENT;
        }

        // Update only for creation
        if (!$this->id) {
            $this->sessionId = $inscriptionRequest->sessionId;
            $this->clientIp = $inscriptionRequest->clientIp;
            $this->utmSource = $inscriptionRequest->utmSource;
            $this->utmCampaign = $inscriptionRequest->utmCampaign;
            $this->referrerCode = $inscriptionRequest->referrerCode;
        }
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function isPaymentRequired(): bool
    {
        return $this->event->isPaymentEnabled() && $this->transportCosts > 0;
    }

    public function getTransportAmount(): ?int
    {
        if (empty($this->transport) || str_starts_with($this->transport, 'gratuit')) {
            return null;
        }

        return $this->event->calculateTransportAmount($this->transport, $this->withDiscount);
    }

    public function addPaymentStatus(PaymentStatus $paymentStatus): void
    {
        $this->paymentStatuses->add($paymentStatus);
    }
}
