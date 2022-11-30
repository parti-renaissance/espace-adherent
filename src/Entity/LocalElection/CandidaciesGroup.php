<?php

namespace App\Entity\LocalElection;

use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="local_election_candidacies_group")
 */
class CandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LocalElection\LocalElection", inversedBy="candidaciesGroups")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?LocalElection $election = null;

    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocalElection\Candidacy", mappedBy="candidaciesGroup", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     */
    protected $candidacies;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $faithStatementFileName = null;

    /**
     * @Assert\File(maxSize="5M", binaryFormat=false, mimeTypes={"application/pdf"})
     */
    public ?UploadedFile $file = null;

    public function __toString(): string
    {
        return (string) $this->election;
    }

    public function addCandidacy(CandidacyInterface $candidacy): void
    {
        parent::addCandidacy($candidacy);
    }

    public function hasFaitStatementFile(): bool
    {
        return null !== $this->faithStatementFileName;
    }

    public function getFaitStatementFilePath(): string
    {
        return sprintf('elections/profession-de-foi/%s', $this->faithStatementFileName);
    }
}
