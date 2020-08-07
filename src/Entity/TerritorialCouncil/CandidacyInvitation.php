<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
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
 */
class CandidacyInvitation
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_ACCEPTED = 'accepted';
    private const STATUS_DECLINED = 'declined';

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
     * @Assert\NotBlank
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

    public function setMembership(TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }
}
