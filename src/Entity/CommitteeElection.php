<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/committee_elections/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['committee_election:read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee())"
        ),
    ],
    routePrefix: '/v3'
)]
#[ORM\Entity(repositoryClass: CommitteeElectionRepository::class)]
class CommitteeElection extends AbstractElectionEntity
{
    /**
     * @var Committee
     */
    #[Groups(['committee_election:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class, inversedBy: 'committeeElections')]
    private $committee;

    /**
     * @var CommitteeCandidacy[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'committeeElection', targetEntity: CommitteeCandidacy::class)]
    private $candidacies;

    /**
     * @var CommitteeCandidaciesGroup[]|Collection
     */
    #[Groups(['committee_election:read'])]
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: CommitteeCandidaciesGroup::class, cascade: ['persist'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private $candidaciesGroups;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $adherentNotified = false;

    public function __construct(?Designation $designation = null, ?UuidInterface $uuid = null)
    {
        parent::__construct($designation, $uuid);

        $this->candidacies = new ArrayCollection();
        $this->candidaciesGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    /**
     * @return CommitteeCandidacy[]
     */
    public function getCandidacies(): array
    {
        return $this->candidacies->toArray();
    }

    public function countConfirmedCandidacies(): int
    {
        return $this->candidacies
            ->filter(function (CommitteeCandidacy $candidacy) {
                return $candidacy->isConfirmed();
            })
            ->count()
        ;
    }

    public function setAdherentNotified(bool $adherentNotified): void
    {
        $this->adherentNotified = $adherentNotified;
    }

    /**
     * @return CommitteeCandidaciesGroup[]
     */
    public function getCandidaciesGroups(): array
    {
        return $this->candidaciesGroups->toArray();
    }

    public function addCandidaciesGroups(CommitteeCandidaciesGroup $group): void
    {
        if (!$this->candidaciesGroups->contains($group)) {
            $group->setElection($this);
            $this->candidaciesGroups->add($group);
        }
    }
}
