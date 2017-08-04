<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRegistrationRepository")
 * @ORM\Table(name="events_registrations")
 *
 * @Algolia\Index(autoIndex=false)
 */
class EventRegistration
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventBase")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $event;

    /**
     * @ORM\Column(length=50)
     */
    private $firstName;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $newsletterSubscriber;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private $adherentUuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(
        UuidInterface $uuid,
        EventBase $event,
        string $firstName,
        string $emailAddress,
        ?string $postalCode = null,
        bool $newsletterSubscriber = false,
        UuidInterface $adherentUuid = null,
        string $createdAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->event = $event;
        $this->firstName = $firstName;
        $this->emailAddress = $emailAddress;
        $this->postalCode = $postalCode;
        $this->newsletterSubscriber = $newsletterSubscriber;
        $this->adherentUuid = $adherentUuid;
        $this->createdAt = new \DateTime($createdAt);
    }

    public function getEvent(): EventBase
    {
        return $this->event;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCreatedAt()
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

    public function getAttendedAt(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->event->getBeginAt());
    }

    public function matches(EventBase $event, Adherent $adherent = null): bool
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
}
