<?php

namespace App\Api;

use App\Exception\EventException;
use App\Repository\EventRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventProvider
{
    private $repository;
    private $urlGenerator;

    public function __construct(EventRepository $repository, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @throws EventException
     */
    public function getUpcomingEvents(int $type = null): array
    {
        $data = [];

        foreach ($this->repository->findUpcomingEvents($type) as $event) {
            if (!$event->isGeocoded()) {
                continue;
            }

            $el = [
                'uuid' => $event->getUuid()->toString(),
                'slug' => $event->getSlug(),
                'name' => $event->getName(),
                'url' => $this->urlGenerator->generate('app_event_show', [
                    'slug' => $event->getSlug(),
                ]),
                'position' => [
                    'lat' => (float) $event->getLatitude(),
                    'lng' => (float) $event->getLongitude(),
                ],
            ];

            if ($committee = $event->getCommittee()) {
                $el += [
                    'committee_name' => $committee->getName(),
                    'committee_url' => $this->urlGenerator->generate('app_committee_show', [
                        'slug' => $committee->getSlug(),
                    ]),
                ];
            } else {
                $el['organizer'] = $event->getOrganizer()->getFullName();
            }

            $data[] = $el;
        }

        return $data;
    }
}
