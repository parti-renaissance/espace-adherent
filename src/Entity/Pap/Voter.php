<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Entity\EntityIdentityTrait;
use App\Repository\Pap\VoterRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    uriTemplate: '/v3/pap/address/{uuid}/voters',
    operations: [new GetCollection()],
    uriVariables: [
        'uuid' => new Link(toProperty: 'address', fromClass: Address::class),
    ],
    normalizationContext: ['groups' => ['pap_address_voter_list'], 'iri' => true],
    paginationEnabled: false
)]
#[ORM\Entity(repositoryClass: VoterRepository::class)]
#[ORM\Table(name: 'pap_voter')]
class Voter
{
    use EntityIdentityTrait;

    #[ORM\Column(nullable: true)]
    private ?string $firstName;

    #[Groups(['pap_address_voter_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $lastName;

    #[Groups(['pap_address_voter_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $gender;

    #[Groups(['pap_address_voter_list'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthdate;

    #[Groups(['pap_address_voter_list'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $votePlace;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $source;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Address::class, inversedBy: 'voters')]
    private ?Address $address;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $gender = null,
        ?\DateTimeInterface $birthdate = null,
        ?string $votePlace = null,
        ?string $source = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->birthdate = $birthdate;
        $this->votePlace = $votePlace;
        $this->source = $source;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getFirstNameInitial(): ?string
    {
        if (!$this->firstName) {
            return null;
        }

        return strtoupper($this->firstName[0]).'.';
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePlace(): ?string
    {
        return $this->votePlace;
    }

    public function setVotePlace(?string $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }
}
