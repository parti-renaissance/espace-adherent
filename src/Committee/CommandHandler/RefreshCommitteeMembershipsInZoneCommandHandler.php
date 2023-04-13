<?php

namespace App\Committee\CommandHandler;

use App\Committee\Command\RefreshCommitteeMembershipsInZoneCommand;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RefreshCommitteeMembershipsInZoneCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ZoneRepository $zoneRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(RefreshCommitteeMembershipsInZoneCommand $command): void
    {
        if (!$zone = $this->zoneRepository->findOneByCode($command->getZoneCode())) {
            return;
        }

        $committeesOfZone = $this->findCommitteesForZone($zone);

        [$committeesZones, $zoneCommitteeMapping] = $this->getCommitteesZones($committeesOfZone);

        $committeeAdherentIds = [];
        $alreadyAssignedAdherentIds = [];
        $committees = [];

        foreach ($committeesZones as $zones) {
            /** @var Zone $zone */
            foreach ($zones as $zone) {
                $adherents = $this->adherentRepository->findAllForCommitteeZone($zone);
                $committee = $committeesOfZone[$zoneCommitteeMapping[$zone->getTypeCode()]];
                $committees[$committee->getId()] = $committee;

                $reports = [
                    'committee' => [
                        'id' => $committee->getId(),
                        'name' => $committee->getName(),
                    ],
                    'zone' => $zone->getTypeCode(),
                    'assigned_adherents_ids' => [],
                    'found_adherents_ids' => array_map(fn (Adherent $adherent) => $adherent->getId(), $adherents),
                ];

                if (!isset($committeeAdherentIds[$committee->getId()])) {
                    $committeeAdherentIds[$committee->getId()] = [];
                }

                foreach ($adherents as $adherent) {
                    if (\in_array($adherent->getId(), $alreadyAssignedAdherentIds)) {
                        continue;
                    }

                    $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::COMMITTEE_EDITION);
                    $reports['assigned_adherents_ids'][] = $committeeAdherentIds[$committee->getId()][] = $alreadyAssignedAdherentIds[] = $adherent->getId();
                }

                if ($zone->isCanton()) {
                    $this->logger->error('CL - assignation report : '.json_encode($reports));
                }
            }
        }

        foreach ($committees as $committee) {
            if (!$committee->allowMembershipsMoving()) {
                continue;
            }

            foreach ($this->committeeMembershipManager->getCommitteeMemberships($committee) as $membership) {
                if (!\in_array($membership->getAdherent()->getId(), $committeeAdherentIds[$committee->getId()])) {
                    $this->committeeMembershipManager->unfollowCommittee($membership, $committee);
                }
            }
        }

        $this->entityManager->clear();

        $this->committeeRepository->updateMembershipsCounters();
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
