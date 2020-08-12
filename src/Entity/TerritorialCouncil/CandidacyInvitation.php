<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Validator\TerritorialCouncil\ValidTerritorialCouncilCandidacyInvitation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_candidacy_invitation")
 *
 * @Algolia\Index(autoIndex=false)
 *
 * @ValidTerritorialCouncilCandidacyInvitation(groups={"Default", "invitation_edit"})
 */
class CandidacyInvitation
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var Candidacy
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\Candidacy", mappedBy="invitation", cascade={"all"})
     */
    private $candidacy;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="TerritorialCouncilMembership")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank(groups={"Default", "invitation_edit"})
     */
    private $membership;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $acceptedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $declinedAt;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getCandidacy(): ?Candidacy
    {
        return $this->candidacy;
    }

    public function setCandidacy(Candidacy $candidacy): void
    {
        $this->candidacy = $candidacy;
    }

    public function getMembership(): ?TerritorialCouncilMembership
    {
        return $this->membership;
    }

    public function setMembership(?TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }

    public function accept(): void
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->acceptedAt = new \DateTime();
    }

    public function decline(): void
    {
        $this->status = self::STATUS_DECLINED;
        $this->declinedAt = new \DateTime();
    }

    public function isPending(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function isAccepted(): bool
    {
        return self::STATUS_ACCEPTED === $this->status;
    }

    public function resetStatus(): void
    {
        $this->status = self::STATUS_PENDING;
        $this->declinedAt = $this->acceptedAt = null;
    }
}
