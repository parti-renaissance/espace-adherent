<?php

declare(strict_types=1);

namespace App\Poll;

use App\Entity\Geo\Zone;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Poll\LocalPollRepository;
use App\Repository\Poll\NationalPollRepository;
use Doctrine\ORM\EntityManagerInterface;

class PollManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ZoneRepository $zoneRepository,
        private readonly NationalPollRepository $nationalPollRepository,
        private readonly LocalPollRepository $localPollRepository,
    ) {
    }

    public function findActivePoll(?string $postalCode = null): ?Poll
    {
        $poll = $this->nationalPollRepository->findLastActivePoll();

        if ($poll) {
            return $poll;
        }

        if ($postalCode) {
            $zone = $this->zoneRepository->findOneByPostalCode($postalCode);

            if ($zone) {
                $poll = $this->findActivePollByZone($zone, $postalCode);

                if ($poll) {
                    return $poll;
                }
            }
        }

        return null;
    }

    public function findActivePollByZone(Zone $zone, ?string $postalCode = null): ?LocalPoll
    {
        if ($zone->isRegion()) {
            $region = $zone;
        } else {
            $regions = $zone->getParentsOfType(Zone::REGION);
            $region = !empty($regions) ? current($regions) : null;
        }

        if ($region) {
            if ($zone->isDepartment()) {
                $department = $zone;
            } else {
                $departments = $zone->getParentsOfType(Zone::DEPARTMENT);
                $department = !empty($departments) ? current($departments) : null;
            }

            $poll = $this->localPollRepository->findOnePublishedByZone($region, $department, $postalCode);

            if ($poll) {
                return $poll;
            }
        }

        return null;
    }

    public function save(Poll $poll): void
    {
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }

    public function publish(Poll $poll): void
    {
        $poll->setPublished(true);
        $this->entityManager->getRepository($poll::class)->unpublishExceptOf($poll);

        $this->entityManager->flush();
    }

    public function unpublish(Poll $poll): void
    {
        $poll->setPublished(false);

        $this->entityManager->flush();
    }
}
