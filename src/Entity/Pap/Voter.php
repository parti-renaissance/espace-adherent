<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Repository\Pap\VoterRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'pap_voter')]
#[ORM\Entity(repositoryClass: VoterRepository::class)]
#[ApiResource(attributes: ['normalization_context' => ['groups' => ['pap_address_voter_list'], 'iri' => true], 'pagination_enabled' => false], collectionOperations: [], itemOperations: [])]
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
        ?string $source = null
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
