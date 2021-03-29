<?php

namespace App\Entity;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeCandidacyRepository")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 */
class CommitteeCandidacy extends BaseCandidacy
{
    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeElection", inversedBy="candidacies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    /**
     * @var CommitteeMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeMembership", inversedBy="committeeCandidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $committeeMembership;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $type;

    /**
     * @var CommitteeCandidacyInvitation[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacyInvitation", mappedBy="candidacy", cascade={"all"})
     *
     * @Assert\Count(value=1, groups={"invitation_edit"}, exactMessage="This value should not be blank.")
     */
    protected $invitations;

    /**
     * @var CommitteeCandidaciesGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeCandidaciesGroup", inversedBy="candidacies", cascade={"persist"})
     */
    protected $candidaciesGroup;

    public function __construct(CommitteeElection $election, string $gender = null, UuidInterface $uuid = null)
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

    public function getAdherent(): Adherent
    {
        return $this->committeeMembership->getAdherent();
    }

    /**
     * @Assert\IsTrue(groups={"accept_invitation"})
     */
    public function isValidForConfirmation(): bool
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $this->type) {
            return true;
        }

        if (!$this->candidaciesGroup || !$otherCandidacies = $this->getOtherCandidacies()) {
            return false;
        }

        $binome = current($otherCandidacies);

        return $binome->hasInvitation() && $binome->isDraft();
    }

    /**
     * @Assert\IsTrue(groups={"committee_supervisor_candidacy", "accept_invitation"}, message="Photo est obligatoire")
     */
    public function isValid(): bool
    {
        return $this->hasImageName() && !$this->isRemoveImage()
            || $this->getImage()
        ;
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CommitteeCandidaciesGroup();
    }
}
