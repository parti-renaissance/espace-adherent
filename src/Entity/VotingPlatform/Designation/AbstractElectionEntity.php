<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\EntityDesignationTrait;
use App\Entity\EntityIdentityTrait;
use App\VotingPlatform\Designation\DesignationStatusEnum;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractElectionEntity implements ElectionEntityInterface
{
    use EntityDesignationTrait {
        getStatus as getDesignationStatus;
    }
    use EntityIdentityTrait;

    public function __construct(Designation $designation = null, UuidInterface $uuid = null)
    {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    /**
     * @Groups({"committee_election:read"})
     */
    public function getStatus(): string
    {
        return match (true) {
            $this->isVotePeriodScheduled() => DesignationStatusEnum::SCHEDULED,
            $this->isVotePeriodActive() => DesignationStatusEnum::IN_PROGRESS,
            $this->isResultPeriodStarted() => DesignationStatusEnum::CLOSED,
            default => DesignationStatusEnum::NOT_STARTED,
        };
    }

    public function isVotePeriodScheduled(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getElectionCreationDate()
            && $this->getElectionCreationDate() <= $now
            && $now < $this->getVoteStartDate()
        ;
    }
}
