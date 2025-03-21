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
    denormalizationContext: ['groups' => ['event_inscription_update_status']]
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

    #[Assert\Choice(callback: [InscriptionStatusEnum::class, 'toArray'])]
    #[Groups(['national_event_inscription:webhook', 'event_inscription_update_status'])]
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

    #[ORM\Column(nullable: true)]
    public ?string $birthPlace = null;

    #[ORM\Column(nullable: true)]
    public ?string $accessibility = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $transportNeeds = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $volunteer = false;

    #[ORM\Column(nullable: true)]
    public ?array $qualities = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $birthdate = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $joinNewsletter = false;

    public bool $needSendNewsletterConfirmation = false;

    #[ORM\Column(nullable: true)]
    public ?string $children = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isResponsibilityWaived = false;

    #[ORM\Column(nullable: true)]
    public ?string $clientIp = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $sessionId = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'json', nullable: true)]
    public $emailCheck;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $ticketSentAt = null;

    #[ORM\Column(nullable: true)]
    public ?string $ticketCustomDetail = null;

    #[ORM\Column(nullable: true)]
    public ?string $ticketQRCodeFile = null;

    #[ORM\Column(length: 7, nullable: true)]
    public ?string $referrerCode = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referrer = null;

    public function __construct(NationalEvent $event, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->event = $event;
    }

    public function update(EventInscription $eventInscription): void
    {
        $this->addressEmail = $eventInscription->addressEmail;
        $this->needSendNewsletterConfirmation = $eventInscription->needSendNewsletterConfirmation;
        $this->joinNewsletter = $eventInscription->joinNewsletter;
        $this->firstName = $eventInscription->firstName;
        $this->lastName = $eventInscription->lastName;
        $this->gender = $eventInscription->gender;
        $this->phone = $eventInscription->phone;
        $this->postalCode = $eventInscription->postalCode;
        $this->qualities = $eventInscription->qualities;
        $this->birthPlace = $eventInscription->birthPlace;
        $this->accessibility = $eventInscription->accessibility;
        $this->transportNeeds = $eventInscription->transportNeeds;
        $this->volunteer = $eventInscription->volunteer;
        $this->birthdate = $eventInscription->birthdate;
        $this->children = $eventInscription->children;
        $this->isResponsibilityWaived = $eventInscription->isResponsibilityWaived;
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
}
