<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\PapVotePlaceScopeFilter;
use App\Entity\EntityIdentityTrait;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\VotePlaceRepository")
 * @ORM\Table(name="pap_vote_place", indexes={
 *     @ORM\Index(columns={"latitude", "longitude"}),
 * })
 *
 * @ApiResource(
 *     shortName="PapVotePlace",
 *     attributes={
 *         "pagination_client_enabled": true,
 *         "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap_v2') or is_granted('IS_FEATURE_GRANTED', 'pap') or (is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER'))",
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_vote_place_read"},
 *         },
 *         "filters": {PapVotePlaceScopeFilter::class},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_vote_places",
 *         },
 *     },
 *     itemOperations={"get"},
 * )
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
     *
     * @Groups({"pap_vote_place_read"})
     */
    public ?string $code = null;

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

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_vote_place_read"})
     */
    public int $nbAddresses;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_vote_place_read"})
     */
    public int $nbVoters;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     */
    public ?Zone $zone = null;

    public function __construct(
        ?float $latitude,
        ?float $longitude,
        ?string $code = null,
        int $nbAddresses = 0,
        int $nbVoters = 0,
        UuidInterface $uuid = null,
        ?Zone $zone = null,
        ?float $deltaPredictionAndResult2017 = null,
        ?float $deltaAveragePredictions = null,
        ?float $abstentions2017 = null,
        ?int $misregistrationsPriority = null,
        ?int $firstRoundPriority = null,
        ?int $secondRoundPriority = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->code = $code;
        $this->nbAddresses = $nbAddresses;
        $this->nbVoters = $nbVoters;
        $this->zone = $zone;
        $this->deltaPredictionAndResult2017 = $deltaPredictionAndResult2017;
        $this->deltaAveragePredictions = $deltaAveragePredictions;
        $this->abstentions2017 = $abstentions2017;
        $this->misregistrationsPriority = $misregistrationsPriority;
        $this->firstRoundPriority = $firstRoundPriority;
        $this->secondRoundPriority = $secondRoundPriority;
    }
}
