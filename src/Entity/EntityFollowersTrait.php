<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

trait EntityFollowersTrait
{
    /**
     * @var Collection|FollowerInterface[]
     */
    private $followers;

    /**
     * @return FollowerInterface[]
     */
    public function getFollowers(): array
    {
        return $this->followers->toArray();
    }

    public function addFollower(FollowerInterface $follower): void
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
        }
    }

    public function getFollower(Adherent $adherent): ?FollowerInterface
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('adherent', $adherent))
        ;

        return $this->followers->matching($criteria)->count() > 0
            ? $this->followers->matching($criteria)->first()
            : null;
    }

    /**
     * @SymfonySerializer\Groups({"cause_read", "coalition_read"})
     */
    public function getFollowersCount(): int
    {
        return $this->followers->count();
    }
}
