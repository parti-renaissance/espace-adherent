<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "denormalization_context": {"groups": {"contact_create"}},
 *             "normalization_context": {"groups": {"contact_read_after_write"}},
 *             "path": "/contacts",
 *             "validation_groups": {"Default", "contact_create"},
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "normalization_context": {"groups": {"contact_read"}},
 *             "path": "/contacts/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "normalization_context": {"groups": {"contact_read_after_write"}},
 *             "denormalization_context": {"groups": {"contact_update"}},
 *             "path": "/contacts/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "validation_groups": {"Default", "contact_update"},
 *         }
 *     }
 * )
 *
 * @UniqueEntity(fields={"emailAddress"})
 * @AssertRecaptcha(api="friendly_captcha", groups={"contact_create"})
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    #[Groups(['contact_create', 'contact_read'])]
    #[ORM\Column(length: 50)]
    private ?string $firstName;

    /**
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length"
     * )
     */
    #[Groups(['contact_update'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName;

    /**
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    #[Groups(['contact_create', 'contact_read'])]
    #[ORM\Column(unique: true)]
    private ?string $emailAddress;

    /**
     * @AssertPhoneNumber
     */
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private ?PhoneNumber $phone = null;

    /**
     * @Assert\Range(
     *     min="-120 years",
     *     max="now"
     * )
     */
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    /**
     * @Assert\Choice(
     *     choices=App\Membership\Contact\InterestEnum::ALL,
     *     multiple=true
     *  )
     */
    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $interests = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $interestsUpdatedAt = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *     choices=App\Membership\Contact\SourceEnum::ALL,
     *     message="contact.source.choice"
     * )
     */
    #[Groups(['contact_create'])]
    #[ORM\Column]
    private ?string $source;

    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $mailContact = false;

    #[Groups(['contact_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $phoneContact = false;

    /**
     * @Assert\IsTrue(message="contact.cgu_accepted.is_true")
     */
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
        ?string $source = null
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
