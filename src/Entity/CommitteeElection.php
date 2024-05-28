<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     itemOperations={
 *         "get": {
 *             "path": "/committee_elections/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee())",
 *             "normalization_context": {
 *                 "groups": {"committee_election:read"},
 *             },
 *         },
 *     },
 *     collectionOperations={}
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeElectionRepository")
 */
class CommitteeElection extends AbstractElectionEntity
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", inversedBy="committeeElections")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Groups(['committee_election:read'])]
    private $committee;

    /**
     * @var CommitteeCandidacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacy", mappedBy="committeeElection")
     */
    private $candidacies;

    /**
     * @var CommitteeCandidaciesGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidaciesGroup", mappedBy="election", cascade={"persist"})
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    #[Groups(['committee_election:read'])]
    private $candidaciesGroups;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
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
