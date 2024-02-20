<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Event\Request\EventInscriptionRequest;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table("national_event_inscription")
 */
class EventInscription
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    /**
     * @ORM\ManyToOne(targetEntity=NationalEvent::class)
     */
    public NationalEvent $event;

    /**
     * @ORM\Column(length=6)
     */
    public ?string $gender = null;

    /**
     * @ORM\Column
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column
     */
    public ?string $lastName = null;

    /**
     * @ORM\Column
     */
    public ?string $addressEmail = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $postalCode = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTime $birthdate = null;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
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
    public ?string $sessionId = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    public $emailCheck;

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
        $this->birthdate = $inscriptionRequest->birthdate;
        $this->utmSource = $inscriptionRequest->utmSource;
        $this->utmCampaign = $inscriptionRequest->utmCampaign;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
