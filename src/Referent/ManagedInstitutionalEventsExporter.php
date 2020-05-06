<?php

namespace App\Referent;

use App\Entity\InstitutionalEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagedInstitutionalEventsExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

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
                'invitationsCount' => $institutionalEvent->getInvitationsCount(),
                'edit' => [
                    'label' => "<span class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate(
                        'app_referent_institutional_events_edit',
                        ['uuid' => $institutionalEvent->getUuid()]
                    ),
                ],
                'delete' => [
                    'label' => "<span class='btn btn--default'><i class='fa fa-remove'></i></span>",
                    'url' => $this->urlGenerator->generate(
                        'app_referent_institutional_events_delete',
                        ['uuid' => $institutionalEvent->getUuid()]
                    ),
                ],
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
