<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Filter\PapVotePlaceScopeFilter;
use App\Entity\EntityIdentityTrait;
use App\Entity\Geo\Zone;
use App\Repository\Pap\VotePlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'PapVotePlace',
    operations: [
        new GetCollection(uriTemplate: '/v3/pap_vote_places'),
    ],
    normalizationContext: ['iri' => true, 'groups' => ['pap_vote_place_read']],
    filters: [PapVotePlaceScopeFilter::class],
    paginationClientEnabled: true,
    security: "is_granted('REQUEST_SCOPE_GRANTED', ['pap_v2', 'pap']) or (is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER'))"
)]
#[ORM\Entity(repositoryClass: VotePlaceRepository::class)]
#[ORM\Index(columns: ['latitude', 'longitude'])]
#[ORM\Table(name: 'pap_vote_place')]
class VotePlace implements \Stringable
{
    use EntityIdentityTrait;

    #[Groups(['pap_address_list'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    public ?float $latitude;

    #[Groups(['pap_address_list'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    public ?float $longitude;

    #[Groups(['pap_vote_place_read'])]
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $code = null;

    #[Groups(['pap_vote_place_read'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $nbAddresses;

    #[Groups(['pap_vote_place_read'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $nbVoters;

    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $zone = null;

    #[ORM\ManyToMany(targetEntity: Campaign::class, mappedBy: 'votePlaces', fetch: 'EXTRA_LAZY')]
    public Collection $campaigns;

    public function __construct(
        ?float $latitude,
        ?float $longitude,
        ?string $code = null,
        int $nbAddresses = 0,
        int $nbVoters = 0,
        ?UuidInterface $uuid = null,
        ?Zone $zone = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->code = $code;
        $this->nbAddresses = $nbAddresses;
        $this->nbVoters = $nbVoters;
        $this->zone = $zone;
        $this->campaigns = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}
