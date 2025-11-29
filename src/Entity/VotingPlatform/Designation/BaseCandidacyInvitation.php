<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\CommitteeMembership;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\MappedSuperclass]
abstract class BaseCandidacyInvitation implements CandidacyInvitationInterface, Timestampable
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var string
     */
    #[ORM\Column]
    private $status = self::STATUS_PENDING;

    /**
     * @var CandidacyInterface
     */
    protected $candidacy;

    /**
     * @var CommitteeMembership
     */
    protected $membership;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $acceptedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $declinedAt;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getCandidacy(): ?CandidacyInterface
    {
        return $this->candidacy;
    }

    public function setCandidacy(CandidacyInterface $candidacy): void
    {
        $this->candidacy = $candidacy;
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
