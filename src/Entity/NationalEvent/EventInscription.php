<?php

namespace App\Entity\NationalEvent;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Filter\OrTextSearchFilter;
use App\Controller\Api\NationalEvent\GetNextInscriptionForValidationController;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\ImageAwareInterface;
use App\Entity\ImageExposeInterface;
use App\Entity\PublicIdTrait;
use App\Entity\ZoneableEntityInterface;
use App\Enum\CivilityEnum;
use App\NationalEvent\Api\State\UpdateStatusPutProcessor;
use App\NationalEvent\DTO\InscriptionRequest;
use App\NationalEvent\DTO\RemainingStatsOutput;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\Normalizer\ImageExposeNormalizer;
use App\Normalizer\TranslateAdherentTagNormalizer;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: OrTextSearchFilter::class, properties: ['firstName' => 'lastName', 'lastName' => 'firstName', 'addressEmail' => 'addressEmail'])]
#[ApiFilter(filterClass: InZoneOfScopeFilter::class)]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['event.type' => 'exact'])]
#[ApiFilter(filterClass: ExistsFilter::class, properties: ['adherent'])]
#[ApiResource(
    shortName: 'NationalEventInscription',
    operations: [
        new GetCollection(order: ['createdAt' => 'DESC']),
        new Put(requirements: ['uuid' => '%pattern_uuid%'], normalizationContext: ['groups' => []], output: RemainingStatsOutput::class, processor: UpdateStatusPutProcessor::class),
        new Post(
            uriTemplate: '/national_event_inscriptions/next-to-validate',
            controller: GetNextInscriptionForValidationController::class,
            deserialize: false,
        ),
    ],
    routePrefix: '/v3',
    normalizationContext: [
        TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR => true,
        'groups' => ['event_inscription_read', ImageExposeNormalizer::NORMALIZATION_GROUP],
    ],
    denormalizationContext: ['groups' => ['event_inscription_update']],
    paginationItemsPerPage: 50,
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'rentree')"
)]
#[ORM\Entity(repositoryClass: EventInscriptionRepository::class)]
#[ORM\Table('national_event_inscription')]
class EventInscription implements ZoneableEntityInterface, ImageAwareInterface, ImageExposeInterface, TranslatedTagInterface
{
    use EntityIdentityTrait;
    use PublicIdTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;
    use EntityZoneTrait;

    public const int CANCELLATION_DELAY_IN_HOUR = 336;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\ManyToOne(targetEntity: NationalEvent::class)]
    public NationalEvent $event;

    #[Assert\Choice(InscriptionStatusEnum::STATUSES)]
    #[Groups(['national_event_inscription:webhook', 'event_inscription_update', 'event_inscription_read'])]
    #[ORM\Column(options: ['default' => InscriptionStatusEnum::PENDING])]
    public string $status = InscriptionStatusEnum::PENDING;

    #[ORM\ManyToOne(targetEntity: self::class)]
    public ?self $duplicateInscriptionForStatus = null;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(nullable: true, enumType: PaymentStatusEnum::class)]
    public ?PaymentStatusEnum $paymentStatus = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(length: 6)]
    public ?string $gender = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column]
    public ?string $firstName = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column]
    public ?string $lastName = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column]
    public ?string $addressEmail = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $postalCode = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column(nullable: true)]
    public ?string $birthPlace = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column(nullable: true)]
    public ?string $accessibility = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $transportNeeds = false;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $volunteer = false;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?array $qualities = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $birthdate = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $joinNewsletter = false;

    public bool $needSendNewsletterConfirmation = false;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $children = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isResponsibilityWaived = false;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isJAM = false;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $visitDay = null;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $transport = null;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $accommodation = null;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $roommateIdentifier = null;

    #[Groups(['event_inscription_read'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $withDiscount = null;

    #[Groups(['event_inscription_read', 'event_inscription_read_for_validation'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $amount = null;

    #[ORM\Column(nullable: true)]
    public ?string $clientIp = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(nullable: true)]
    public ?string $sessionId = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'json', nullable: true)]
    public $emailCheck;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $confirmedAt = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $canceledAt = null;

    #[Groups(['national_event_inscription:webhook', 'event_inscription_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $ticketSentAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $confirmationSentAt = null;

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

    public ?self $originalInscription = null;

    #[ORM\OneToMany(mappedBy: 'inscription', targetEntity: Payment::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $payments;

    #[Groups(['event_inscription_update'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $validationComment = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $validationStartedAt = null;

    #[Groups(['event_inscription_update'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $validationFinishedAt = null;

    public function __construct(NationalEvent $event, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->event = $event;
        $this->payments = new ArrayCollection();
        $this->zones = new ArrayCollection();
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

    public function updateFromRequest(InscriptionRequest $inscriptionRequest): void
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

        // Update only for creation
        if (!$this->id) {
            $this->sessionId = $inscriptionRequest->sessionId;
            $this->clientIp = $inscriptionRequest->clientIp;
            $this->utmSource = $inscriptionRequest->utmSource;
            $this->utmCampaign = $inscriptionRequest->utmCampaign;
            $this->referrerCode = $inscriptionRequest->referrerCode;

            $this->updateTransportFromRequest($inscriptionRequest);

            if ($this->amount) {
                $this->status = InscriptionStatusEnum::WAITING_PAYMENT;
                $this->paymentStatus = PaymentStatusEnum::PENDING;
            }
        }
    }

    public function updateTransportFromRequest(InscriptionRequest $inscriptionRequest): void
    {
        $this->transport = $inscriptionRequest->transport;
        $this->accommodation = $inscriptionRequest->accommodation;
        $this->visitDay = $inscriptionRequest->visitDay;
        $this->withDiscount = $inscriptionRequest->withDiscount;
        $this->roommateIdentifier = $inscriptionRequest->roommateIdentifier;
        $this->amount = $this->event->calculateInscriptionAmount($this->transport, $this->accommodation, $this->withDiscount);
    }

    #[Groups(['event_inscription_read_for_validation'])]
    public function getCivility(): ?CivilityEnum
    {
        return CivilityEnum::fromGender($this->gender);
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
        return $this->event->isPaymentEnabled() && $this->amount > 0 && !$this->isRejectedState();
    }

    public function addPayment(Payment $payment): void
    {
        $this->payments->add($payment);

        if (null === $this->paymentStatus) {
            if ($this->isPending()) {
                $this->status = InscriptionStatusEnum::WAITING_PAYMENT;
            }
            $this->paymentStatus = $payment->status;
        }
    }

    public function countPayments(): int
    {
        return $this->payments->count();
    }

    public function isPaymentSuccess(): bool
    {
        return PaymentStatusEnum::CONFIRMED === $this->paymentStatus;
    }

    public function isPaymentFailed(): bool
    {
        return PaymentStatusEnum::ERROR === $this->paymentStatus;
    }

    public function getVisitDayConfig(): ?array
    {
        if (!$this->visitDay) {
            return null;
        }

        foreach ($this->event->transportConfiguration['jours'] ?? [] as $day) {
            if ($day['id'] === $this->visitDay) {
                return $day;
            }
        }

        return null;
    }

    public function getTransportConfig(): ?array
    {
        if (!$this->transport) {
            return null;
        }

        foreach ($this->event->transportConfiguration['transports'] ?? [] as $transport) {
            if ($transport['id'] === $this->transport) {
                return $transport;
            }
        }

        return null;
    }

    public function getAccommodationConfig(): ?array
    {
        if (!$this->accommodation) {
            return null;
        }

        foreach ($this->event->transportConfiguration['hebergements'] ?? [] as $accommodation) {
            if ($accommodation['id'] === $this->accommodation) {
                return $accommodation;
            }
        }

        return null;
    }

    public function isApproved(): bool
    {
        return \in_array($this->status, InscriptionStatusEnum::APPROVED_STATUSES);
    }

    public function isDuplicate(): bool
    {
        return InscriptionStatusEnum::DUPLICATE === $this->status;
    }

    /**
     * @return Payment[]
     */
    public function getPayments(): array
    {
        return $this->payments->toArray();
    }

    /** @return Payment[] */
    public function getSuccessPayments(): array
    {
        return array_filter($this->getPayments(), static fn (Payment $payment) => $payment->isConfirmed());
    }

    public function getAmountInEuro(): ?float
    {
        if (null === $this->amount) {
            return null;
        }

        return $this->amount / 100.0;
    }

    public function isPending(): bool
    {
        return InscriptionStatusEnum::PENDING === $this->status;
    }

    public function allowEditInscription(): bool
    {
        return $this->event->allowEditInscription() && !$this->isRejectedState();
    }

    public function isRejectedState(): bool
    {
        return \in_array($this->status, InscriptionStatusEnum::REJECTED_STATUSES, true);
    }

    public function getPublicId(): ?string
    {
        return $this->adherent?->getPublicId() ?? $this->publicId;
    }

    public function isCurrentPayment(Payment $payment): bool
    {
        if ($this->payments->isEmpty()) {
            return true;
        }

        return $this->visitDay === $payment->visitDay
            && $this->transport === $payment->transport
            && $this->accommodation === $payment->accommodation
            && $this->amount === $payment->amount;
    }

    public function getImageName(): ?string
    {
        return $this->adherent?->getImageName();
    }

    public function hasImageName(): bool
    {
        return $this->adherent?->hasImageName();
    }

    public function getImagePath(): ?string
    {
        return $this->adherent?->getImagePath();
    }

    #[Groups(['event_inscription_read'])]
    #[SerializedName('tags')]
    public function getAdherentTags(): ?array
    {
        return $this->adherent?->tags;
    }

    #[Groups(['event_inscription_read_for_validation'])]
    public function getMemberTags(): ?array
    {
        $tags = array_filter(array_map(
            static fn (string $tag): ?string => \in_array($tag, TagEnum::getAdherentTags(), true) ? $tag : null,
            $this->adherent?->tags ?? []
        ));

        return empty($tags) ? ['Externe'] : array_values($tags);
    }

    #[Groups(['event_inscription_read_for_validation'])]
    public function getElectTags(): ?array
    {
        $tags = array_filter(array_map(
            static fn (string $tag): ?string => \in_array($tag, TagEnum::getElectTags(), true) ? $tag : null,
            $this->adherent?->tags ?? []
        ));

        return empty($tags) ? null : array_values($tags);
    }

    #[Groups(['event_inscription_read_for_validation'])]
    public function getOtherTags(): ?array
    {
        $filteredTags = array_merge(TagEnum::getAdherentTags(), TagEnum::getElectTags());

        $tags = array_filter(array_map(
            static fn (string $tag): ?string => !\in_array($tag, $filteredTags, true) ? $tag : null,
            $this->adherent?->tags ?? []
        ));

        return empty($tags) ? null : array_values($tags);
    }

    public function markAsInValidation(): void
    {
        if (!$this->isPending()) {
            throw new \LogicException('Only pending inscription can be marked as in validation.');
        }

        $this->status = InscriptionStatusEnum::IN_VALIDATION;
        $this->validationStartedAt = new \DateTime();
    }
}
