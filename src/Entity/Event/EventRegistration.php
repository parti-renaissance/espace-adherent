<?php

namespace App\Entity\Event;

use App\Adherent\Tag\TranslatedTagInterface;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPersonNameTrait;
use App\Entity\ImageAwareInterface;
use App\Entity\ImageExposeInterface;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventRegistrationRepository::class)]
#[ORM\Index(columns: ['email_address'])]
#[ORM\Table(name: 'events_registrations')]
#[ORM\UniqueConstraint(columns: ['adherent_id', 'event_id'])]
class EventRegistration implements TranslatedTagInterface, ImageAwareInterface, ImageExposeInterface
{
    use EntityIdentityTrait;
    use EntityPersonNameTrait;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Event::class)]
    private $event;

    #[Groups(['event_registration_list'])]
    #[ORM\Column(nullable: true)]
    private $emailAddress;

    #[Groups(['event_registration_list'])]
    #[ORM\Column(length: 15, nullable: true)]
    private $postalCode;

    #[ORM\Column(type: 'boolean')]
    private $newsletterSubscriber;

    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private ?Adherent $adherent;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(nullable: true)]
    private ?string $source;

    #[Groups(['event_registration_list'])]
    #[ORM\Column(enumType: RegistrationStatusEnum::class, options: ['default' => RegistrationStatusEnum::CONFIRMED])]
    public RegistrationStatusEnum $status = RegistrationStatusEnum::CONFIRMED;

    #[Groups(['event_registration_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $confirmedAt = null;

    public function __construct(
        UuidInterface $uuid,
        Event $event,
        string $firstName,
        string $lastName,
        string $emailAddress,
        ?string $postalCode,
        bool $newsletterSubscriber = false,
        ?Adherent $adherent = null,
        ?string $source = null,
        string $createdAt = 'now',
        RegistrationStatusEnum $status = RegistrationStatusEnum::CONFIRMED,
    ) {
        $this->uuid = $uuid;
        $this->event = $event;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->newsletterSubscriber = $newsletterSubscriber;
        $this->adherent = $adherent;
        $this->source = $source;
        $this->createdAt = new \DateTime($createdAt);
        $this->postalCode = $postalCode;
        $this->status = $status;

        if (RegistrationStatusEnum::CONFIRMED === $status) {
            $this->confirmedAt = $this->createdAt;
        }
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    #[Groups(['event_registration_list'])]
    public function getFirstName(): string
    {
        return $this->adherent?->getFirstName() ?? $this->firstName;
    }

    #[Groups(['event_registration_list'])]
    public function getLastName(): string
    {
        return $this->adherent?->getLastName() ?? $this->lastName;
    }

    #[Groups(['event_registration_list'])]
    public function getEmailAddress(): ?string
    {
        return $this->adherent?->getEmailAddress() ?? $this->emailAddress;
    }

    #[Groups(['event_registration_list'])]
    public function getPostalCode(): ?string
    {
        return $this->adherent?->getPostalCode() ?? $this->postalCode;
    }

    #[Groups(['event_registration_list'])]
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

    public function matches(Event $event, ?Adherent $adherent = null): bool
    {
        if (!$this->event->equals($event)) {
            return false;
        }

        // Registration is not linked to an adherent.
        if (!$this->adherent) {
            return null === $adherent;
        }

        return $this->adherent === $adherent;
    }

    #[Groups(['event_registration_list'])]
    public function getPhone(): ?PhoneNumber
    {
        return $this->adherent?->getPhone();
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    #[Groups(['event_registration_list'])]
    public function getTags(): ?array
    {
        return $this->adherent?->tags;
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

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function confirm(): void
    {
        $this->status = RegistrationStatusEnum::CONFIRMED;
        $this->confirmedAt = new \DateTime();
    }

    public function cancel(): void
    {
        $this->status = RegistrationStatusEnum::INVITED;
    }

    public function isConfirmed(): bool
    {
        return RegistrationStatusEnum::CONFIRMED === $this->status;
    }
}
