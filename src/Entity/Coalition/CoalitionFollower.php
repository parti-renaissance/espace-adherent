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
 *
 * @UniqueEntity(fields={"coalition", "adherent"}, errorPath="adherent")
 */
class CoalitionFollower extends AbstractFollower
{
    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="followers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $coalition;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $adherent;

    public function __construct(Coalition $coalition, Adherent $adherent)
    {
        $this->uuid = Uuid::uuid4();
        $this->coalition = $coalition;
        $this->adherent = $adherent;
    }

    public function getFollowed(): FollowedInterface
    {
        return $this->coalition;
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }

    public function getEmailAddress(): ?string
    {
        return $this->getAdherent() ? $this->getAdherent()->getEmailAddress() : null;
    }
}
