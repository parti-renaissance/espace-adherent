<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\Repository\CommitteeCandidacyRepository;
use App\Validator\CommitteeMembershipZoneInScopeZones as AssertCommitteeMembershipZoneInScopeZones;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/committee_candidacies/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee())"
        ),
        new Delete(
            uriTemplate: '/committee_candidacies/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object.getCommittee()) and not object.isVotePeriodStarted()"
        ),
        new Post(uriTemplate: '/committee_candidacies'),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['committee_candidacy:read']],
    denormalizationContext: ['groups' => ['committee_candidacy:write']],
    validationContext: ['groups' => ['api_committee_candidacy_validation']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee')"
)]
#[Assert\Expression(expression: '!this.isVotePeriodStarted()', message: 'Vous ne pouvez pas créer de candidature sur une élection en cours', groups: ['api_committee_candidacy_validation'])]
#[ORM\Entity(repositoryClass: CommitteeCandidacyRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
class CommitteeCandidacy extends BaseCandidacy
{
    /**
     * @var CommitteeElection
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeElection::class, inversedBy: 'candidacies')]
    private $committeeElection;

    /**
     * @var CommitteeMembership
     */
    #[AssertCommitteeMembershipZoneInScopeZones(groups: ['api_committee_candidacy_validation'])]
    #[Assert\NotBlank(message: "Cet adhérent n'est pas un membre du comité.", groups: ['api_committee_candidacy_validation'])]
    #[Groups(['committee_candidacy:read', 'committee_election:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeMembership::class, inversedBy: 'committeeCandidacies')]
    private $committeeMembership;

    /**
     * @var string
     */
    #[ORM\Column]
    private $type;

    /**
     * @var CommitteeCandidacyInvitation[]|Collection
     */
    #[Assert\Count(exactly: 1, exactMessage: 'This value should not be blank.', groups: ['invitation_edit'])]
    #[ORM\OneToMany(mappedBy: 'candidacy', targetEntity: CommitteeCandidacyInvitation::class, cascade: ['all'])]
    protected $invitations;

    /**
     * @var CommitteeCandidaciesGroup|null
     */
    #[Assert\NotBlank(groups: ['api_committee_candidacy_validation'])]
    #[Groups(['committee_candidacy:write', 'committee_candidacy:read'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeCandidaciesGroup::class, cascade: ['persist'], inversedBy: 'candidacies')]
    protected $candidaciesGroup;

    public function __construct(CommitteeElection $election, ?string $gender = null, ?UuidInterface $uuid = null)
    {
        parent::__construct($gender, $uuid);

        $this->type = $election->getDesignationType();

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $this->type) {
            $this->status = CandidacyInterface::STATUS_CONFIRMED;
        }

        $this->committeeElection = $election;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->committeeElection;
    }

    public function getCommitteeMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function getMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function setCommitteeMembership(CommitteeMembership $committeeMembership): void
    {
        $this->committeeMembership = $committeeMembership;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->committeeMembership->getAdherent();
    }

    #[Assert\IsTrue(groups: ['committee_supervisor_candidacy', 'accept_invitation'], message: 'Photo est obligatoire')]
    public function isValid(): bool
    {
        return ($this->hasImageName() && !$this->isRemoveImage())
            || $this->getImage();
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CommitteeCandidaciesGroup();
    }

    public function isVotePeriodStarted(): bool
    {
        return $this->committeeElection->getDesignation()->isVotePeriodStarted();
    }

    public function getCommittee(): Committee
    {
        return $this->committeeElection->getCommittee();
    }

    public function candidateWith(CandidacyInterface $candidacy): void
    {
        parent::candidateWith($candidacy);

        $this->candidaciesGroup->setElection($candidacy->getElection());
    }
}
