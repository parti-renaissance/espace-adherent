<?php

namespace App\Team;

use App\Api\DTO\AdherentUuid;
use App\Entity\Adherent;
use App\Entity\Reporting\TeamMemberHistory;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Security;

class TeamMemberManagementHandler
{
    private EntityManagerInterface $entityManager;
    private AdherentRepository $adherentRepository;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdherentRepository $adherentRepository,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->security = $security;
    }

    public function handleChanges(Team $team, Collection $oldMembers, array $newMembers): void
    {
        $oldTeamMembersAdherentUuids = array_map(function (Member $member) {
            return $member->getAdherent()->getUuid();
        }, $oldMembers->toArray());

        $newTeamMembersAdherentUuids = array_map(function (AdherentUuid $adherentUuid) {
            return $adherentUuid->getAdherentUuid();
        }, $newMembers);

        $this->handleMembersToAdd($team, $oldTeamMembersAdherentUuids, $newTeamMembersAdherentUuids);
        $this->handleMembersToRemove($team, $oldTeamMembersAdherentUuids, $newTeamMembersAdherentUuids);

        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();
        $team->setUpdatedByAdherent($adherent);

        $this->entityManager->flush();
        $this->entityManager->refresh($team);
    }

    private function handleMembersToAdd(
        Team $team,
        array $oldTeamMembersAdherentUuids,
        array $newTeamMembersAdherentUuids
    ): void {
        $adherentToAdd = array_diff($newTeamMembersAdherentUuids, $oldTeamMembersAdherentUuids);
        /** @var UuidInterface $adherentUuid */
        foreach ($adherentToAdd as $adherentUuid) {
            $adherent = $this->adherentRepository->findOneByUuid($adherentUuid->toString());
            if ($adherent) {
                $newMember = new Member(null, $adherent);
                $team->addMember($newMember);

                //Add history
                $history = TeamMemberHistory::createAdd($team, $adherent, $this->security->getUser());

                $this->entityManager->persist($history);
            }
        }
    }

    private function handleMembersToRemove(
        Team $team,
        array $oldTeamMembersAdherentUuids,
        array $newTeamMembersAdherentUuids
    ): void {
        $adherentToRemove = array_diff($oldTeamMembersAdherentUuids, $newTeamMembersAdherentUuids);
        /** @var UuidInterface $adherentUuid */
        foreach ($adherentToRemove as $adherentUuid) {
            $adherent = $this->adherentRepository->findOneByUuid($adherentUuid->toString());
            if ($adherent && $team->hasAdherent($adherent)) {
                $team->removeMember($team->getMember($adherent));

                //Add history
                $history = TeamMemberHistory::createRemove($team, $adherent, $this->security->getUser());

                $this->entityManager->persist($history);
            }
        }
    }
}
