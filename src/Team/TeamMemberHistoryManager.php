<?php

declare(strict_types=1);

namespace App\Team;

use App\Entity\Administrator;
use App\Entity\Reporting\TeamMemberHistory;
use App\Entity\Team\Team;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TeamMemberHistoryManager
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function handleChanges(Team $newTeam, ?Team $oldTeam = null): void
    {
        $newMembers = $newTeam->getMembers();
        $oldMembers = $oldTeam ? $oldTeam->getMembers() : new ArrayCollection();

        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        foreach ($this->getAddedMembers($newMembers, $oldMembers) as $member) {
            $history = TeamMemberHistory::createAdd($newTeam, $member->getAdherent(), $administrator);

            $this->entityManager->persist($history);
        }

        $this->entityManager->flush();

        if (!$oldTeam) {
            return;
        }

        foreach ($this->getRemovedMembers($newMembers, $oldMembers) as $member) {
            $history = TeamMemberHistory::createRemove($newTeam, $member->getAdherent(), $administrator);

            $this->entityManager->persist($history);
        }

        $this->entityManager->flush();
    }

    public function getAddedMembers(Collection $newMembers, Collection $oldMembers): array
    {
        $addedMembers = [];
        foreach ($newMembers as $member) {
            if (!$oldMembers->contains($member)) {
                $addedMembers[] = $member;
            }
        }

        return $addedMembers;
    }

    public function getRemovedMembers(Collection $newMembers, Collection $oldMembers): array
    {
        $removedMembers = [];
        foreach ($oldMembers as $member) {
            if (!$newMembers->contains($member)) {
                $removedMembers[] = $member;
            }
        }

        return $removedMembers;
    }
}
