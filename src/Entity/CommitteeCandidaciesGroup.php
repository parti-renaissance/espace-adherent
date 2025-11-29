<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Repository\CommitteeCandidaciesGroupRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Delete(
        uriTemplate: '/committee_candidacies_groups/{uuid}',
        requirements: ['uuid' => '%pattern_uuid%'],
        security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee()) and not object.isVotePeriodStarted() and object.isEmptyCandidacies()"
    ),
    new Post(uriTemplate: '/committee_candidacies_groups'),
],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['committee_candidacies_group:read']],
    denormalizationContext: ['groups' => ['committee_candidacies_group:write']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee')"
)]
#[Assert\Expression(expression: '!this.isVotePeriodStarted()', message: 'Vous ne pouvez pas créer de liste sur une élection en cours')]
#[ORM\Entity(repositoryClass: CommitteeCandidaciesGroupRepository::class)]
class CommitteeCandidaciesGroup extends BaseCandidaciesGroup
{
    use EntityTimestampableTrait;

    #[ApiProperty(identifier: false)]
    private $id;

    #[ApiProperty(identifier: true, openapiContext: ['type' => 'string', 'format' => 'uuid', 'example' => 'b4219d47-3138-5efd-9762-2ef9f9495084'])]
    #[Groups(['committee_election:read', 'committee_candidacies_group:read', 'committee_candidacy:read'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected UuidInterface $uuid;

    #[Assert\NotBlank]
    #[Groups(['committee_candidacies_group:write', 'committee_candidacies_group:read'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeElection::class, inversedBy: 'candidaciesGroups')]
    protected ?CommitteeElection $election = null;

    /**
     * @var CandidacyInterface[]|Collection
     */
    #[Groups(['committee_candidacies_group:read', 'committee_election:read'])]
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: CommitteeCandidacy::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    protected $candidacies;

    public function __construct(?UuidInterface $uuid = null)
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
