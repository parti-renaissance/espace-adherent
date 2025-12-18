<?php

declare(strict_types=1);

namespace App\Committee\CommandHandler;

use App\Committee\Command\RefreshCommitteeMembershipsInZoneCommand;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class RefreshCommitteeMembershipsInZoneCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ZoneRepository $zoneRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RefreshCommitteeMembershipsInZoneCommand $command): void
    {
        if (!$zone = $this->zoneRepository->findOneByCode($command->getZoneCode())) {
            return;
        }

        $committeesOfZone = $this->findCommitteesForZone($zone);

        [$committeesZones, $zoneCommitteeMapping] = $this->getCommitteesZones($committeesOfZone);

        $committeeAdherentIds = $alreadyAssignedAdherentIds = $adherentsToSyncMC = $committees = [];

        foreach ($committeesZones as $zones) {
            foreach ($zones as $zone) {
                /** @var Zone $zone */
                $adherents = $this->adherentRepository->findAllForCommitteeZone($zone);
                $committee = $committeesOfZone[$zoneCommitteeMapping[$zone->getTypeCode()]];
                $committees[$committee->getId()] = $committee;

                if (!isset($committeeAdherentIds[$committee->getId()])) {
                    $committeeAdherentIds[$committee->getId()] = [];
                }

                $adherentsMembership = [];

                foreach ($adherents as $adherent) {
                    if (\in_array($adherent->getId(), $alreadyAssignedAdherentIds)) {
                        continue;
                    }

                    $adherentsToSyncMC[] = $adherentsMembership[] = $adherent;
                    $committeeAdherentIds[$committee->getId()][] = $alreadyAssignedAdherentIds[] = $adherent->getId();
                }

                foreach ($adherentsMembership as $adherent) {
                    $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::COMMITTEE_EDITION);
                }
            }
        }

        foreach ($committees as $committee) {
            if (!$committee->allowMembershipsMoving()) {
                continue;
            }

            foreach ($this->committeeMembershipManager->getCommitteeMemberships($committee) as $membership) {
                if ($membership->isManual()) {
                    $adherentsToSyncMC[] = $membership->getAdherent();
                    continue;
                }

                if (!\in_array($membership->getAdherent()->getId(), $committeeAdherentIds[$committee->getId()])) {
                    $this->committeeMembershipManager->unfollowCommittee($membership);
                }
            }
        }

        $this->entityManager->clear();

        $this->committeeRepository->updateMembershipsCounters();

        foreach ($adherentsToSyncMC as $adherent) {
            $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress(), batch: true));
        }
    }

    /**
     * @return Committee[]
     */
    private function findCommitteesForZone(Zone $zone): array
    {
        $committees = [];
        foreach ($this->committeeRepository->findInZones([$zone]) as $committee) {
            $committees[$committee->getId()] = $committee;
        }

        return $committees;
    }

    /**
     * @param Committee[] $committees
     */
    private function getCommitteesZones(array $committees): array
    {
        $committeesZones = [];
        $zoneCommitteeMapping = [];

        foreach ($committees as $committee) {
            foreach ($committee->getZones() as $zone) {
                if (!isset($committeesZones[$zone->getType()])) {
                    $committeesZones[$zone->getType()] = [];
                }

                $committeesZones[$zone->getType()][] = $zone;
                $zoneCommitteeMapping[$zone->getTypeCode()] = $committee->getId();
            }
        }

        uksort($committeesZones, fn (string $a, string $b) => array_search($a, Zone::COMMITTEE_TYPES) <=> array_search($b, Zone::COMMITTEE_TYPES));

        return [
            $committeesZones,
            $zoneCommitteeMapping,
        ];
    }
}
