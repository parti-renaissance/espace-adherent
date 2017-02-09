<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeEventRegistrationRepository")
 * @ORM\Table(name="committee_events_registrations")
 */
class CommitteeEventRegistration
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommitteeEvent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $event;

    /**
     * @ORM\Column(length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(length=100)
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=15)
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
        CommitteeEvent $event,
        string $firstName,
        string $emailAddress,
        string $postalCode,
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
        $this->createdAt = new \DateTimeImmutable($createdAt);
    }

    public function getEvent(): CommitteeEvent
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

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function isNewsletterSubscriber(): bool
    {
        return $this->newsletterSubscriber;
    }

    public function matches(CommitteeEvent $event, Adherent $adherent = null): bool
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
