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
    private const STATUS_DRAFT = 'draft';
    private const STATUS_CONFIRMED = 'confirmed';

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
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\Election")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $election;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="TerritorialCouncilMembership", inversedBy="candidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $membership;

    /**
     * @var CandidacyInvitation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\CandidacyInvitation", inversedBy="candidacy", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $invitation;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $quality;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_DRAFT;

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
        return null !== $this->invitation;
    }

    public function getInvitation(): ?CandidacyInvitation
    {
        return $this->invitation;
    }

    public function setInvitation(?CandidacyInvitation $invitation): void
    {
        $this->invitation = $invitation;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }

    public function isDraft(): bool
    {
        return self::STATUS_DRAFT === $this->status;
    }
}
