<?php

namespace App\Exporter;

use App\Entity\Event\BaseEvent;
use App\Repository\EventRegistrationRepository;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventParticipantsExporter
{
    private EventRegistrationRepository $eventRegistrationRepository;
    private SonataExporter $exporter;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository, SonataExporter $exporter)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
        $this->exporter = $exporter;
    }

    public function export(BaseEvent $event, string $format): StreamedResponse
    {
        return $this->exporter->getResponse(
            $format,
            sprintf(
                'inscrits_a_l_evenement_%s_%s.%s',
                (new Slugify())->slugify($event->getSlug()),
                (new \DateTime())->format('YmdHis'),
                $format
            ),
            new IteratorCallbackSourceIterator(
                $this->eventRegistrationRepository->iterateByEvent($event),
                function (array $data) {
                    $registration = array_shift($data);
                    $row = [];

                    $row['Date d\'inscription'] = ($registration['subscription_date'])->format('Y-m-d H:i:s');
                    $row['Prénom'] = $registration['first_name'];
                    $row['Nom'] = $registration['last_name'];
                    $row['Code postal'] = $registration['postal_code'];

                    return $row;
                })
        );
    }
}
