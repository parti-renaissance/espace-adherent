<?php

namespace AppBundle\Referent;

use AppBundle\Entity\InstitutionalEvent;

class ManagedInstitutionalEventsExporter
{
    public function exportAsJson(array $managedInstitutionalEvents): string
    {
        $data = [];

        /** @var InstitutionalEvent $institutionalEvent */
        foreach ($managedInstitutionalEvents as $institutionalEvent) {
            $data[] = [
                'id' => $institutionalEvent->getId(),
                'name' => $institutionalEvent->getName(),
                'beginAt' => $institutionalEvent->getBeginAt()->format('d/m/Y H:i'),
                'category' => $institutionalEvent->getCategoryName(),
                'postalCode' => $institutionalEvent->getPostalCode(),
                'organizer' => $institutionalEvent->getOrganizer()->getPartialName(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
