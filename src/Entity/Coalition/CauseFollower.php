<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractFollower;
use App\Entity\Adherent;
use App\Entity\FollowedInterface;
use App\Entity\Geo\Zone;
use App\Validator\Coalition\CauseFollowerEmail as AssertCauseFollowerEmailValid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={"get"},
 *     collectionOperations={}
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\Coalition\CauseFollowerRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="cause_follower_unique", columns={"cause_id", "adherent_id"})
 * })
 *
 * @UniqueEntity(fields={"cause", "adherent"}, errorPath="adherent")
 * @UniqueEntity(
 *     fields={"cause", "emailAddress"},
 *     errorPath="emailAddress",
 *     message="cause_follower.exists",
 *     groups={"anonymous_follower"}
 * )
 *
 * @AssertCauseFollowerEmailValid(groups={"anonymous_follower"})
 */
class CauseFollower extends AbstractFollower
{
    /**
     * @var Cause
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="followers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     */
    private $cause;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\NotBlank(groups={"anonymous_follower"})
     * @Assert\Length(max=50, groups={"anonymous_follower"})
     */
    private $firstName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank(groups={"anonymous_follower"})
     * @Assert\Email(message="common.email.invalid", groups={"anonymous_follower"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"anonymous_follower"})
     */
    private $emailAddress;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\NotBlank(groups={"anonymous_follower"})
     */
    private $zone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Assert\NotBlank(groups={"anonymous_follower"})
     */
    private $cguAccepted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $causeSubscription;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $coalitionSubscription;

    public function __construct(?Cause $cause = null, ?Adherent $adherent = null)
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isCguAccepted(): bool
    {
        return $this->cguAccepted;
    }

    public function setCguAccepted(?bool $cguAccepted): void
    {
        $this->cguAccepted = $cguAccepted;
    }

    public function hasCauseSubscription(): bool
    {
        return $this->causeSubscription;
    }

    public function setCauseSubscription(bool $causeSubscription): void
    {
        $this->causeSubscription = $causeSubscription;
    }

    public function hasCoalitionSubscription(): bool
    {
        return $this->coalitionSubscription;
    }

    public function setCoalitionSubscription(bool $coalitionSubscription): void
    {
        $this->coalitionSubscription = $coalitionSubscription;
    }
}
