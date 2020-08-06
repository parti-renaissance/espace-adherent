<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_candidacy")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Candidacy extends BaseCandidacy
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=2000)
     */
    private $faithStatement;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isPublicFaithStatement = false;

    /**
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\Election", inversedBy="candidacies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $election;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership", inversedBy="candidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $membership;

    public function __construct(
        TerritorialCouncilMembership $membership,
        Election $election,
        string $gender,
        UuidInterface $uuid = null
    ) {
        parent::__construct($gender, $uuid);

        $this->membership = $membership;
        $this->election = $election;
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    public function getMembership(): ?TerritorialCouncilMembership
    {
        return $this->membership;
    }

    public function setMembership(TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }

    public function isOngoing(): bool
    {
        return $this->election->isOngoing();
    }

    public function getFaithStatement(): ?string
    {
        return $this->faithStatement;
    }

    public function setFaithStatement(?string $faithStatement): void
    {
        $this->faithStatement = $faithStatement;
    }

    public function isPublicFaithStatement(): bool
    {
        return $this->isPublicFaithStatement;
    }

    public function setIsPublicFaithStatement(bool $isPublicFaithStatement): void
    {
        $this->isPublicFaithStatement = $isPublicFaithStatement;
    }

    public function hasInvitation(): bool
    {
        return false;
    }
}
