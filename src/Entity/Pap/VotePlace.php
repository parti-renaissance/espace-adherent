<?php

namespace App\Entity\Pap;

use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\VotePlaceRepository")
 * @ORM\Table(name="pap_vote_place", indexes={
 *     @ORM\Index(columns={"latitude", "longitude"}),
 * })
 */
class VotePlace
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    public ?float $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    public ?float $longitude;

    /**
     * @ORM\Column(nullable=true, unique=true)
     */
    public ?string $code = null;

    /**
     * @ORM\Column(name="delta_prediction_and_result_2017", type="float", nullable=true)
     */
    public ?float $deltaPredictionAndResult2017;

    /**
     * @ORM\Column(name="delta_average_predictions_2017", type="float", nullable=true)
     */
    public ?float $deltaAveragePredictions2017;

    /**
     * @ORM\Column(name="abstentions_2017", type="float", nullable=true)
     */
    public ?float $abstentions2017;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $nbMisregistrations;

    public function __construct(
        ?float $latitude,
        ?float $longitude,
        UuidInterface $uuid = null,
        ?float $deltaPredictionAndResult2017 = null,
        ?float $deltaAveragePredictions017 = null,
        ?float $abstentions2017 = null,
        ?int $nbMisregistrations = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->deltaPredictionAndResult2017 = $deltaPredictionAndResult2017;
        $this->deltaAveragePredictions2017 = $deltaAveragePredictions017;
        $this->abstentions2017 = $abstentions2017;
        $this->nbMisregistrations = $nbMisregistrations;
    }
}
