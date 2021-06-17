<?php

namespace App\Poll;

use App\Entity\Geo\Zone;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;
use App\JeMarche\Command\PollCreatedNotificationCommand;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Poll\LocalPollRepository;
use App\Repository\Poll\NationalPollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PollManager
{
    private $bus;
    private $entityManager;
    private $zoneRepository;
    private $nationalPollRepository;
    private $localPollRepository;

    public function __construct(
        MessageBusInterface $bus,
        EntityManagerInterface $entityManager,
        ZoneRepository $zoneRepository,
        NationalPollRepository $nationalPollRepository,
        LocalPollRepository $localPollRepository
    ) {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
        $this->zoneRepository = $zoneRepository;
        $this->nationalPollRepository = $nationalPollRepository;
        $this->localPollRepository = $localPollRepository;
    }

    public function findActivePoll(string $postalCode = null): ?Poll
    {
        $poll = $this->nationalPollRepository->findLastActivePoll();

        if ($poll) {
            return $poll;
        }

        if ($postalCode) {
            $zone = $this->zoneRepository->findOneByPostalCode($postalCode);

            if ($zone) {
                $poll = $this->findActivePollByZone($zone);

                if ($poll) {
                    return $poll;
                }
            }
        }

        return null;
    }

    public function findActivePollByZone(Zone $zone, string $postalCode = null): ?LocalPoll
    {
        $region = current($zone->getParentsOfType(Zone::REGION));
        $department = current($zone->getParentsOfType(Zone::DEPARTMENT));

        if ($region && $department) {
            $poll = $this->localPollRepository->findOnePublishedByZone($region, $department, $postalCode);

            if ($poll) {
                return $poll;
            }
        }

        return null;
    }

    public function scheduleNotification(Poll $poll): void
    {
        $this->bus->dispatch(new PollCreatedNotificationCommand($poll->getUuid()));
    }

    public function save(Poll $poll): void
    {
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
    }

    public function publish(Poll $poll): void
    {
        $poll->setPublished(true);
        $this->entityManager->getRepository(\get_class($poll))->unpublishExceptOf($poll);

        $this->entityManager->flush();

        $this->scheduleNotification($poll);
    }

    public function unpublish(Poll $poll): void
    {
        $poll->setPublished(false);

        $this->entityManager->flush();
    }
}
