<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\VoterRepository")
 * @ORM\Table(name="pap_voter")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"pap_address_voter_list"},
 *             "iri": true,
 *         },
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={},
 *     itemOperations={},
 * )
 */
class Voter
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_voter_list"})
     */
    private ?string $firstName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_voter_list"})
     */
    private ?string $lastName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_voter_list"})
     */
    private ?string $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"pap_address_voter_list"})
     */
    private ?\DateTimeInterface $birthdate;

    /**
     * @ORM\Column(length=10, nullable=true)
     *
     * @Groups({"pap_address_voter_list"})
     */
    private ?string $votePlace;

    /**
     * @ORM\Column(length=5, nullable=true)
     */
    private ?string $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Address", inversedBy="voters")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Address $address;

    public function __construct(
        UuidInterface $uuid = null,
        string $firstName = null,
        string $lastName = null,
        string $gender = null,
        \DateTimeInterface $birthdate = null,
        string $votePlace = null,
        string $source = null
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
