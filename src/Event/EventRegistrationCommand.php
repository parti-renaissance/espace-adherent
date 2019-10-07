<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Subscription\SubscriptionTypeEnum;
use AppBundle\Validator\EventRegistration;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @EventRegistration
 */
class EventRegistrationCommand
{
    /**
     * @var BaseEvent
     */
    private $event;

    /**
     * @var Adherent
     */
    private $adherent;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $firstName;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $lastName;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;
    private $newsletterSubscriber;
    private $registrationUuid;

    public function __construct(BaseEvent $event, Adherent $adherent = null)
    {
        $this->event = $event;
        $this->registrationUuid = Uuid::uuid4();
        $this->newsletterSubscriber = false;

        if ($adherent) {
            $this->setAdherent($adherent);
        }
    }

    private function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->newsletterSubscriber = $adherent->hasSubscriptionType(SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL);
    }

    public function getEvent(): BaseEvent
    {
        return $this->event;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function isNewsletterSubscriber(): bool
    {
        return $this->newsletterSubscriber;
    }

    public function setNewsletterSubscriber(bool $newsletterSubscriber): void
    {
        $this->newsletterSubscriber = $newsletterSubscriber;
    }

    public function getRegistrationUuid(): UuidInterface
    {
        return $this->registrationUuid;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getAdherentUuid(): ?UuidInterface
    {
        return $this->adherent ? $this->adherent->getUuid() : null;
    }

    /**
     * @Assert\IsTrue(message="event.full")
     */
    public function isNotFull(): bool
    {
        return !$this->event->isFull();
    }
}
