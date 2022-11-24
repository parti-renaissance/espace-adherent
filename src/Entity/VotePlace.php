<?php

namespace App\Entity;

use App\Entity\Election\VotePlaceResult;
use App\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotePlaceRepository")
 *
 * @UniqueEntity(fields={"code"})
 *
 * @deprecated Use {@see \App\Entity\Election\VotePlace}
 */
class VotePlace
{
    use EntityTimestampableTrait;

    public const MAX_ASSESSOR_REQUESTS = 2;
    public const RESULTS_EMPTY = 'empty';
    public const RESULTS_PARTIAL = 'partial';
    public const RESULTS_COMPLETED = 'completed';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $alias;

    /**
     * @var string
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank(message="Il semble que le code postal ou la ville est incorrecte")
     * @Assert\Length(max=10)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country = 'FR';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $holderOfficeAvailable = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $substituteOfficeAvailable = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled = true;

    private $voteResults;

    public function __construct()
    {
        $this->voteResults = new ArrayCollection();
    }

    public static function create(
        string $name,
        string $code,
        ?string $postalCode,
        ?string $city,
        string $address,
        string $country = 'FR'
    ): self {
        $votePlace = new self();

        $votePlace->setName($name);
        $votePlace->setCode($code);
        $votePlace->setPostalCode($postalCode);
        $votePlace->setCity($city);
        $votePlace->setAddress($address);
        $votePlace->setCountry($country);

        return $votePlace;
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    public function getLabel(): string
    {
        return implode(', ', array_filter([
            $this->alias ?? $this->name,
            $this->address,
            $this->getLocalCode(),
        ]));
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getPostalCodesAsArray(): array
    {
        return explode(',', $this->postalCode);
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLocalCode(): int
    {
        return (int) explode('_', $this->code, 2)[1];
    }

    public function getInseeCode(): string
    {
        return current(explode('_', $this->code, 2));
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function isHolderOfficeAvailable(): bool
    {
        return $this->holderOfficeAvailable;
    }

    public function setHolderOfficeAvailable(bool $holderOfficeAvailable): void
    {
        $this->holderOfficeAvailable = $holderOfficeAvailable;
    }

    public function isSubstituteOfficeAvailable(): bool
    {
        return $this->substituteOfficeAvailable;
    }

    public function setSubstituteOfficeAvailable(bool $substituteOfficeAvailable): void
    {
        $this->substituteOfficeAvailable = $substituteOfficeAvailable;
    }

    public function equals(self $place): bool
    {
        return $this->id === $place->getId();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getResultForElectionRound(ElectionRound $electionRound): ?VotePlaceResult
    {
        foreach ($this->voteResults as $voteResult) {
            if ($voteResult->getElectionRound() === $electionRound) {
                return $voteResult;
            }
        }

        return null;
    }

    public function getResultStatus(ElectionRound $electionRound): string
    {
        $voteResults = $this->voteResults->filter(function (VotePlaceResult $voteResult) use ($electionRound) {
            return $electionRound === $voteResult->getElectionRound();
        });

        if ($voteResults->isEmpty()) {
            return self::RESULTS_EMPTY;
        }

        /** @var VotePlaceResult $voteResult */
        $voteResult = $voteResults->first();

        if ($voteResult->isComplete()) {
            return self::RESULTS_COMPLETED;
        }

        if ($voteResult->isPartial()) {
            return self::RESULTS_PARTIAL;
        }

        return self::RESULTS_EMPTY;
    }
}
