<?php

namespace App\Entity\Coalition;

use App\Entity\AbstractFollower;
use App\Entity\Adherent;
use App\Entity\FollowedInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="cause_follower_unique", columns={"cause_id", "adherent_id"})
 * })
 *
 * @UniqueEntity(fields={"cause", "adherent"}, errorPath="adherent")
 */
class CauseFollower extends AbstractFollower
{
    /**
     * @var Cause
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="followers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $cause;

    public function __construct(Cause $cause, Adherent $adherent)
    {
        $this->uuid = Uuid::uuid4();
        $this->cause = $cause;
        $this->adherent = $adherent;
    }

    public function getFollowed(): FollowedInterface
    {
        return $this->cause;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(Cause $cause): void
    {
        $this->cause = $cause;
    }
}
