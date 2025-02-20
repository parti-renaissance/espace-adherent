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
    validationContext: ['groups' => ['Default', 'referral_write']]
)]
#[ORM\Entity(repositoryClass: ReferralRepository::class)]
#[ReferralInformations]
class Referral
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNullablePostAddressTrait;

    #[Assert\NotBlank]
    #[Groups(['referral_read', 'referral_write'])]
    #[ORM\Column]
    #[ReferralEmail]
    public ?string $emailAddress = null;

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
    #[ORM\Column(length: 6)]
    public ?string $identifier = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(enumType: TypeEnum::class)]
    public ?TypeEnum $type = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(enumType: ModeEnum::class)]
    public ?ModeEnum $mode = null;

    #[Groups(['referral_read'])]
    #[ORM\Column(enumType: StatusEnum::class)]
    public ?StatusEnum $status = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return $this->emailAddress;
    }

    public function hasFullInformations(): bool
    {
        return null !== $this->lastName
            && !$this->postAddress?->isEmpty()
            && null !== $this->civility
            && null !== $this->nationality;
    }
}
