<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Membership\Contact\InterestEnum;
use App\Membership\Contact\SourceEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Repository\ContactRepository;
use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/contacts/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['contact_read']]
        ),
        new Put(
            uriTemplate: '/contacts/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['contact_read_after_write']],
            denormalizationContext: ['groups' => ['contact_update']],
            validationContext: ['groups' => ['Default', 'contact_update']]
        ),
        new Post(
            uriTemplate: '/contacts',
            normalizationContext: ['groups' => ['contact_read_after_write']],
            denormalizationContext: ['groups' => ['contact_create']],
            validationContext: ['groups' => ['Default', 'contact_create']]
        ),
    ]
)]
#[AssertRecaptcha(groups: ['contact_create'])]
#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[UniqueEntity(fields: ['emailAddress'])]
class Contact implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    #[Assert\NotBlank]
    #[Groups(['contact_create', 'contact_read'])]
    #[ORM\Column(length: 50)]
    private ?string $firstName;

    #[Assert\Length(min: 2, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length')]
    #[Groups(['contact_update'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    #[Groups(['contact_create', 'contact_read'])]
    #[ORM\Column(unique: true)]
    private ?string $emailAddress;

    #[AssertPhoneNumber]
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private ?PhoneNumber $phone = null;

    #[Assert\Range(min: '-120 years', max: 'now')]
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[Assert\Choice(choices: InterestEnum::ALL, multiple: true)]
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $interests = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $interestsUpdatedAt = null;

    #[Assert\Choice(choices: SourceEnum::ALL, message: 'contact.source.choice')]
    #[Assert\NotBlank]
    #[Groups(['contact_create'])]
    #[ORM\Column]
    private ?string $source;

    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $mailContact = false;

    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $phoneContact = false;

    #[Assert\IsTrue(message: 'contact.cgu_accepted.is_true')]
    #[Groups(['contact_create', 'contact_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $cguAccepted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $processedAt = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private ?Adherent $adherent = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $firstName = null,
        ?string $emailAddress = null,
        ?string $source = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->firstName = $firstName;
        $this->emailAddress = $emailAddress;
        $this->source = $source;

        $this->setPostAddress(PostAddress::createEmptyAddress());
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;

        $this->interestsUpdatedAt = new \DateTime();
    }

    public function getInterestsUpdatedAt(): ?\DateTime
    {
        return $this->interestsUpdatedAt;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function isMailContact(): bool
    {
        return $this->mailContact;
    }

    public function setMailContact(bool $mailContact): void
    {
        $this->mailContact = $mailContact;
    }

    public function isPhoneContact(): bool
    {
        return $this->phoneContact;
    }

    public function setPhoneContact(bool $phoneContact): void
    {
        $this->phoneContact = $phoneContact;
    }

    public function isCguAccepted(): bool
    {
        return $this->cguAccepted;
    }

    public function setCguAccepted(bool $cguAccepted): void
    {
        $this->cguAccepted = $cguAccepted;
    }

    public function process(): void
    {
        $this->processedAt = new \DateTime();
    }

    public function getProcessedAt(): ?\DateTimeInterface
    {
        return $this->processedAt;
    }

    public function isProcessed(): bool
    {
        return null !== $this->processedAt;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }
}
