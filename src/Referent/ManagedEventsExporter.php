<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Event;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagedEventsExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Event[] $managedEvents
     *
     * @return string
     */
    public function exportAsJson(array $managedEvents): string
    {
        $data = [];

        foreach ($managedEvents as $event) {
            $data[] = [
                'id' => $event->getId(),
                'name' => [
                    'label' => $event->getName(),
                    'url' => $this->urlGenerator->generate('app_event_show', [
                        'slug' => $event->getSlug(),
                    ]),
                ],
                'beginAt' => $event->getBeginAt()->format('d/m/Y H:i'),
                'category' => $event->getCategoryName(),
                'postalCode' => $event->getPostalCode(),
                'organizer' => $event->getOrganizer()->getPartialName(),
                'participants' => $event->getParticipantsCount(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
