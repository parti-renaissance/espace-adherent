<?php

namespace App\Entity\Election;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_vote_place")
 *
 * @UniqueEntity(fields={"code"})
 */
class VotePlace
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;

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

    /**
     * @ORM\Column(name="delta_prediction_and_result_2017", type="float", nullable=true)
     */
    public ?float $deltaPredictionAndResult2017 = null;

    /**
     * @ORM\Column(name="delta_average_predictions", type="float", nullable=true)
     */
    public ?float $deltaAveragePredictions = null;

    /**
     * @ORM\Column(name="abstentions_2017", type="float", nullable=true)
     */
    public ?float $abstentions2017 = null;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    public ?int $misregistrationsPriority = null;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    public ?int $firstRoundPriority = null;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    public ?int $secondRoundPriority = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}
