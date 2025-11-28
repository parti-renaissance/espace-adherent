<?php

declare(strict_types=1);

namespace App\Entity\Ohme;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Ohme\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Index(columns: ['ohme_identifier'])]
#[ORM\Table(name: 'ohme_contact')]
#[UniqueEntity(fields: ['ohmeIdentifier'])]
class Contact implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[ORM\Column(unique: true)]
    public string $ohmeIdentifier;

    #[ORM\Column(nullable: true)]
    public ?string $email = null;

    #[ORM\Column(nullable: true)]
    public ?string $firstname = null;

    #[ORM\Column(nullable: true)]
    public ?string $lastname = null;

    #[ORM\Column(nullable: true)]
    public ?string $civility = null;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressStreet = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressStreet2 = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressCity = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressPostCode = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressCountry = null;

    #[ORM\Column(nullable: true)]
    public ?string $addressCountryCode = null;

    #[ORM\Column(nullable: true)]
    public ?string $phone = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $paymentCount = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $lastPaymentDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $ohmeCreatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $ohmeUpdatedAt = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return implode(' ', array_filter([
            \sprintf('[%s]', $this->ohmeIdentifier),
            $this->firstname,
            $this->lastname,
        ]));
    }

    public function incrementPaymentCount(): void
    {
        ++$this->paymentCount;
    }
}
