<?php

namespace App\Entity;

use App\Enum\CivilityEnum;
use App\Renaissance\Petition\SignatureRequest;
use App\Repository\PetitionSignatureRepository;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: PetitionSignatureRepository::class)]
class PetitionSignature
{
    use EntityIdentityTrait;
    use EntityUTMTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(enumType: CivilityEnum::class)]
    public ?CivilityEnum $civility = null;

    #[ORM\Column]
    public ?string $firstName = null;

    #[ORM\Column]
    public ?string $lastName = null;

    #[ORM\Column]
    public ?string $emailAddress = null;

    #[ORM\Column]
    public ?string $postalCode = null;

    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public ?bool $newsletter = false;

    #[ORM\Column]
    public ?string $petitionName = null;

    #[ORM\Column]
    public ?string $petitionSlug = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $validatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $remindedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public static function createFromRequest(SignatureRequest $request): self
    {
        $signature = new self();
        $signature->civility = CivilityEnum::from($request->civility);
        $signature->firstName = $request->firstName;
        $signature->lastName = $request->lastName;
        $signature->emailAddress = $request->email;
        $signature->postalCode = $request->postalCode;
        $signature->phone = $request->phone;
        $signature->newsletter = $request->newsletter;
        $signature->petitionName = $request->petitionName;
        $signature->petitionSlug = $request->petitionSlug;
        $signature->utmSource = $request->utmSource;
        $signature->utmCampaign = $request->utmCampaign;

        return $signature;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function validate(): void
    {
        $this->validatedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
