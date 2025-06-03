<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\RegistrationStatusEnum;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class EventRegistrationCommand
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var Adherent
     */
    private $adherent;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50),
    ], groups: ['registration_public'])]
    #[Groups(['event_registration_write'])]
    private $firstName;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 1, max: 50),
    ], groups: ['registration_public'])]
    #[Groups(['event_registration_write'])]
    private $lastName;

    #[Assert\Email(groups: ['registration_public'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['registration_public'])]
    #[Assert\NotBlank(groups: ['registration_public'])]
    #[Groups(['event_registration_write'])]
    private $emailAddress;

    #[Assert\Length(min: 4, groups: ['registration_public'])]
    #[Groups(['event_registration_write'])]
    private $postalCode;

    #[Groups(['event_registration_write'])]
    private $joinNewsletter = false;

    private $registrationUuid;

    public function __construct(Event $event, ?Adherent $adherent = null, public readonly RegistrationStatusEnum $status = RegistrationStatusEnum::CONFIRMED)
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
        $this->postalCode = $adherent->getPostalCode();
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getEvent(): Event
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
