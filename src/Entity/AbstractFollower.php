<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractFollower implements FollowerInterface
{
    use EntityTimestampableTrait;
    use EntityIdentityTrait;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $adherent;

    abstract public function getFollowed(): FollowedInterface;

    abstract public function getEmailAddress(): ?string;

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function isAdherent(): bool
    {
        return null !== $this->adherent;
    }
}
