<?php

namespace App\Entity\Event;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPersonNameTrait;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: EventRegistrationRepository::class)]
#[ORM\Index(columns: ['email_address'])]
#[ORM\Index(columns: ['adherent_uuid'])]
#[ORM\Table(name: 'events_registrations')]
#[ORM\UniqueConstraint(columns: ['adherent_uuid', 'event_id'])]
class EventRegistration
{
    use EntityIdentityTrait;
    use EntityPersonNameTrait;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: BaseEvent::class)]
    private $event;

    #[ORM\Column(nullable: true)]
    private $emailAddress;

    #[ORM\Column(length: 15, nullable: true)]
    private $postalCode;

    #[ORM\Column(type: 'boolean')]
    private $newsletterSubscriber;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private $adherentUuid;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(nullable: true)]
    private ?string $source;

    public function __construct(
        UuidInterface $uuid,
        BaseEvent $event,
        string $firstName,
        string $lastName,
        string $emailAddress,
        ?string $postalCode,
        bool $newsletterSubscriber = false,
        ?UuidInterface $adherentUuid = null,
        ?string $source = null,
        string $createdAt = 'now',
    ) {
        $this->uuid = $uuid;
        $this->event = $event;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->newsletterSubscriber = $newsletterSubscriber;
        $this->adherentUuid = $adherentUuid;
        $this->source = $source;
        $this->createdAt = new \DateTime($createdAt);
        $this->postalCode = $postalCode;
    }

    public function getEvent(): BaseEvent
    {
        return $this->event;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function isNewsletterSubscriber(): bool
    {
        return $this->newsletterSubscriber;
    }

    public function isEventFinished(): bool
    {
        return $this->event->isFinished();
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function matches(BaseEvent $event, ?Adherent $adherent = null): bool
    {
        if (!$this->event->equals($event)) {
            return false;
        }

        // Registration is not linked to an adherent.
        if (!$this->adherentUuid) {
            return null === $adherent;
        }

        return $adherent && $this->adherentUuid->equals($adherent->getUuid());
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }
}
