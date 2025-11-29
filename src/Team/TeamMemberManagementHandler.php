<?php

declare(strict_types=1);

namespace App\Team;

use App\Api\DTO\AdherentUuid;
use App\Entity\Adherent;
use App\Entity\Reporting\TeamMemberHistory;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TeamMemberManagementHandler
{
    private EntityManagerInterface $entityManager;
    private AdherentRepository $adherentRepository;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdherentRepository $adherentRepository,
        Security $security,
    ) {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->security = $security;
    }

    public function handleMembersToAdd(Team $team, array $newTeamMembersAdherentUuids): void
    {
        /** @var Adherent $teamManager */
        $teamManager = $this->security->getUser();

        /** @var AdherentUuid $adherentUuid */
        foreach ($newTeamMembersAdherentUuids as $adherentUuid) {
            $adherent = $this->adherentRepository->findOneByUuid($adherentUuid->adherentUuid->toString());
            if ($adherent && !$team->hasAdherent($adherent)) {
                $newMember = new Member(null, $adherent);
                $team->addMember($newMember);

                // Add history
                $history = TeamMemberHistory::createAdd($team, $adherent, $teamManager);

                $this->entityManager->persist($history);
            }
        }

        $team->setUpdatedByAdherent($teamManager);
        $this->entityManager->flush();
    }

    public function handleMemberToRemove(Team $team, Adherent $adherent): void
    {
        /** @var Adherent $teamManager */
        $teamManager = $this->security->getUser();

        if ($team->hasAdherent($adherent)) {
            $team->removeMember($team->getMember($adherent));

            // Add history
            $history = TeamMemberHistory::createRemove($team, $adherent, $teamManager);
            $this->entityManager->persist($history);

            $team->setUpdatedByAdherent($teamManager);
            $this->entityManager->flush();
        }
    }
}
