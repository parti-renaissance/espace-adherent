<?php

namespace App\Entity;

use App\Repository\ReferralRepository;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ReferralRepository::class)]
class Referral
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNullablePostAddressTrait;

    #[ORM\Column]
    public ?string $emailAddress = null;

    #[ORM\Column(length: 50)]
    public ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $lastName = null;

    #[ORM\Column(length: 2, nullable: true)]
    public ?string $nationality = null;

    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone = null;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $birthdate = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referrer = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $referred = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return $this->emailAddress;
    }
}
