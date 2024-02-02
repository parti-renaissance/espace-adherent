<?php

namespace App\Entity\Election;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNullablePostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Election\VotePlaceRepository")
 * @ORM\Table(name="election_vote_place")
 *
 * @UniqueEntity(fields={"code"})
 */
class VotePlace
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNullablePostAddressTrait;
    public const MAX_ASSESSOR_REQUESTS = 2;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    public ?string $name = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $alias = null;

    /**
     * @ORM\Column(nullable=true, unique=true)
     *
     * @Groups({"pap_vote_place_read"})
     */
    public ?string $code = null;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    public ?float $latitude = null;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    public ?float $longitude = null;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_vote_place_read"})
     */
    public int $nbAddresses = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_vote_place_read"})
     */
    public int $nbVoters = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     */
    public ?Zone $zone = null;

    public function __construct(?UuidInterface $uuid = null, ?string $code = null, ?string $name = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->code = $code;
        $this->name = $name;
    }

    public function getLabel(): string
    {
        return implode(', ', array_filter([
            $this->alias ?? $this->name,
            $this->getAddress(),
            $this->getLocalCode(),
        ]));
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getLocalCode(): string
    {
        return explode('_', $this->code, 2)[1];
    }
}
