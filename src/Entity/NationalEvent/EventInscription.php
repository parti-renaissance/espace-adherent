<?php

namespace App\Entity\NationalEvent;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Event\Request\EventInscriptionRequest;
use App\NationalEvent\InscriptionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NationalEvent\EventInscriptionRepository")
 * @ORM\Table("national_event_inscription")
 *
 * @ApiResource(
 *     attributes={
 *         "denormalization_context": {"groups": {"event_inscription_update_status"}},
 *         "normalization_context": {"groups": {"event_inscription_read"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "_api_respond": false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
class EventInscription
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    /**
     * @ORM\ManyToOne(targetEntity=NationalEvent::class)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public NationalEvent $event;

    /**
     * @ORM\Column(options={"default": "pending"})
     * @Assert\Choice(callback={"App\NationalEvent\InscriptionStatusEnum", "toArray"})
     */
    #[Groups(['national_event_inscription:webhook', 'event_inscription_update_status'])]
    public string $status = InscriptionStatusEnum::PENDING;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?Adherent $adherent = null;

    /**
     * @ORM\Column(length=6)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $gender = null;

    /**
     * @ORM\Column
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $firstName = null;

    /**
     * @ORM\Column
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $lastName = null;

    /**
     * @ORM\Column
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $addressEmail = null;

    /**
     * @ORM\Column(nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $postalCode = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?array $qualities = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?\DateTime $birthdate = null;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?PhoneNumber $phone = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $joinNewsletter = false;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $clientIp = null;

    /**
     * @ORM\Column(nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?string $sessionId = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    #[Groups(['national_event_inscription:webhook'])]
    public $emailCheck;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $ticketSentAt = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $ticketCustomDetail = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $ticketQRCodeFile = null;

    public function __construct(NationalEvent $event, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->event = $event;
    }

    public function updateFromRequest(EventInscriptionRequest $inscriptionRequest): void
    {
        $this->sessionId = $inscriptionRequest->sessionId;
        $this->clientIp = $inscriptionRequest->clientIp;
        $this->addressEmail = $inscriptionRequest->email;
        $this->joinNewsletter = $inscriptionRequest->allowNotifications;
        $this->firstName = $inscriptionRequest->firstName;
        $this->lastName = $inscriptionRequest->lastName;
        $this->gender = $inscriptionRequest->civility;
        $this->phone = $inscriptionRequest->phone;
        $this->postalCode = $inscriptionRequest->postalCode;
        $this->qualities = $inscriptionRequest->qualities;
        $this->birthdate = $inscriptionRequest->birthdate;
        $this->utmSource = $inscriptionRequest->utmSource;
        $this->utmCampaign = $inscriptionRequest->utmCampaign;
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
