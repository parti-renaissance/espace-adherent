<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"committee_candidacies_group:read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"committee_candidacies_group:write"},
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee')",
 *     },
 *     itemOperations={
 *         "delete": {
 *             "path": "/committee_candidacies_groups/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee()) and not object.isVotePeriodStarted() and object.isEmptyCandidacies()",
 *         }
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/committee_candidacies_groups",
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeCandidaciesGroupRepository")
 */
#[Assert\Expression(expression: '!this.isVotePeriodStarted()', message: 'Vous ne pouvez pas créer de liste sur une élection en cours')]
class CommitteeCandidaciesGroup extends BaseCandidaciesGroup
{
    use EntityTimestampableTrait;

    /**
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     *
     * @ApiProperty(
     *     identifier=true,
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "format": "uuid",
     *             "example": "b4219d47-3138-5efd-9762-2ef9f9495084"
     *         }
     *     }
     * )
     */
    #[Groups(['committee_election:read', 'committee_candidacies_group:read', 'committee_candidacy:read'])]
    protected UuidInterface $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeElection", inversedBy="candidaciesGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    #[Assert\NotBlank]
    #[Groups(['committee_candidacies_group:write', 'committee_candidacies_group:read'])]
    protected ?CommitteeElection $election = null;

    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacy", mappedBy="candidaciesGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    #[Groups(['committee_candidacies_group:read', 'committee_election:read'])]
    protected $candidacies;

    public function __construct(UuidInterface $uuid = null)
    {
        parent::__construct();

        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getElection(): ?CommitteeElection
    {
        return $this->election;
    }

    public function setElection(?CommitteeElection $election): void
    {
        $this->election = $election;
    }

    public function isVotePeriodStarted(): bool
    {
        return $this->election->getDesignation()->isVotePeriodStarted();
    }

    public function isEmptyCandidacies(): bool
    {
        return $this->candidacies->isEmpty();
    }

    public function getCommittee(): Committee
    {
        return $this->election->getCommittee();
    }
}
