<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Adherent\Referral\ModeEnum;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Enum\CivilityEnum;
use App\Repository\ReferralRepository;
use App\Validator\ReferralEmail;
use App\Validator\ReferralInformations;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/referrals',
        ),
        new Post(
            uriTemplate: '/v3/referrals',
        ),
    ],
    normalizationContext: ['groups' => ['referral_read']],
    denormalizationContext: ['groups' => ['referral_write']],
    validationContext: ['groups' => ['Default', 'referral_write']],
    order: ['createdAt' => 'DESC'],
    security: "is_granted('RENAISSANCE_ADHERENT')",
)]
#[ORM\Entity(repositoryClass: ReferralRepository::class)]
#[ReferralInformations]
class Referral
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNullablePostAddressTrait;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(nullable: true)]
    #[ReferralEmail]
    public ?string $emailAddress = null;

    #[ORM\Column(nullable: true)]
    public ?string $emailHash = null;

    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(length: 50)]
    public ?string $firstName = null;

    #[Assert\Length(max: 50)]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(length: 50, nullable: true)]
    public ?string $lastName = null;

    #[Assert\Type(type: CivilityEnum::class)]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(nullable: true, enumType: CivilityEnum::class)]
    public ?CivilityEnum $civility = null;

    #[Assert\Country(message: 'common.nationality.invalid')]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(length: 2, nullable: true)]
    public ?string $nationality = null;

    #[AssertPhoneNumber(message: 'common.phone_number.invalid')]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $birthdate = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referrer = null;

    #[Groups(['referral_read'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referred = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(length: 6, unique: true, nullable: true)]
    public ?string $identifier = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(enumType: TypeEnum::class)]
    public ?TypeEnum $type = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(nullable: true, enumType: ModeEnum::class)]
    public ?ModeEnum $mode = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(enumType: StatusEnum::class)]
    public ?StatusEnum $status = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $reportedAt = null;

    public function __construct(?string $emailAddress, ?UuidInterface $uuid = null)
    {
        if ($emailAddress) {
            $this->emailAddress = mb_strtolower($emailAddress);
            $this->emailHash = self::createHash($this->emailAddress);
        }
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForReferred(Adherent $adherent): self
    {
        $referral = new self($adherent->getEmailAddress());

        $referral->updateFromAdherent($adherent);

        return $referral;
    }

    public static function createHash(string $email): UuidInterface
    {
        return Adherent::createUuid($email);
    }

    public function __toString(): string
    {
        return (string) ($this->emailAddress ?? $this->firstName);
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->referred = $adherent;
        $this->emailAddress = $adherent->getEmailAddress();
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->civility = $adherent->getCivility();
        $this->nationality = $adherent->getNationality();
        $this->phone = $adherent->getPhone();
        $this->birthdate = $adherent->getBirthdate();
    }

    public function getCivilityAlias(): string
    {
        return match ($this->civility) {
            CivilityEnum::Monsieur => 'M',
            CivilityEnum::Madame => 'Mme',
            default => '',
        };
    }

    public function hasFullInformations(): bool
    {
        return null !== $this->lastName
            && !$this->postAddress?->isEmpty()
            && null !== $this->civility
            && null !== $this->nationality;
    }

    public function isAdhesion(): bool
    {
        return \in_array($this->type, [TypeEnum::INVITATION, TypeEnum::PREREGISTRATION], true);
    }

    public function isInProgress(): bool
    {
        return StatusEnum::INVITATION_SENT === $this->status;
    }

    public function isReported(): bool
    {
        return StatusEnum::REPORTED === $this->status;
    }

    public function report(): void
    {
        $this->emailAddress =
        $this->lastName =
        $this->civility =
        $this->nationality =
        $this->phone =
        $this->postAddress =
        $this->birthdate = null;

        $this->status = StatusEnum::REPORTED;
        $this->reportedAt = new \DateTime();
    }

    public function isInvitation(): bool
    {
        return TypeEnum::INVITATION === $this->type;
    }

    public function isReportable(): bool
    {
        return TypeEnum::INVITATION === $this->type || TypeEnum::PREREGISTRATION === $this->type;
    }

    public function isAdhesionFinished(): bool
    {
        return \in_array($this->status, [
            StatusEnum::ADHESION_FINISHED,
            StatusEnum::ADHESION_VIA_OTHER_LINK,
        ], true);
    }
}
