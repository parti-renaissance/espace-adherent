<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[Groups(['event_registration_write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, options: ['allowEmptyString' => true])]
    private $firstName;

    #[Groups(['event_registration_write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50, options: ['allowEmptyString' => true])]
    private $lastName;

    #[Groups(['event_registration_write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    private $emailAddress;

    #[Groups(['event_registration_write'])]
    #[Assert\Length(min: 5, max: 5)]
    private $postalCode;

    #[Groups(['event_registration_write'])]
    private $joinNewsletter = false;

    private $registrationUuid;

    public function __construct(BaseEvent $event, ?Adherent $adherent = null)
    {
        $this->event = $event;
        $this->registrationUuid = Uuid::uuid4();

        if ($adherent) {
            $this->updateFromAdherent($adherent);
        }
    }

    private function updateFromAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
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

    public function isJoinNewsletter(): bool
    {
        return $this->joinNewsletter;
    }

    public function setJoinNewsletter(bool $joinNewsletter): void
    {
        $this->joinNewsletter = $joinNewsletter;
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
        return $this->adherent?->getUuid();
    }

    public function getAuthAppCode(): ?string
    {
        return $this->adherent?->getAuthAppCode();
    }

    #[Assert\IsTrue(message: 'event.full')]
    public function isNotFull(): bool
    {
        return !$this->event->isFull();
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }
}
