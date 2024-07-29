<?php

namespace App\Exporter;

use App\Adherent\Tag\TagTranslator;
use App\Entity\Event\BaseEvent;
use App\Repository\EventRegistrationRepository;
use App\Utils\PhoneNumberUtils;
use Cocur\Slugify\Slugify;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventParticipantsExporter
{
    public function __construct(
        private readonly TagTranslator $tagTranslator,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly SonataExporter $exporter
    ) {
    }

    public function export(BaseEvent $event, string $format): StreamedResponse
    {
        return $this->exporter->getResponse(
            $format,
            \sprintf(
                '%s_%s.%s',
                (new Slugify())->slugify($event->getName()),
                (new \DateTime())->format('Y-m-d'),
                $format
            ),
            new IteratorCallbackSourceIterator(
                $this->eventRegistrationRepository->iterateByEvent($event),
                function (array $data) {
                    $registration = array_shift($data);
                    $row = [];

                    $row['Prénom'] = $registration['first_name'];
                    $row['Nom'] = $registration['last_name'];
                    $row['Email'] = $registration['email_address'];
                    $row['Labels'] = implode(', ', array_map(fn (string $tag) => $this->tagTranslator->trans($tag, false), $registration['tags'] ?? []));
                    $row['Code postal'] = $registration['postal_code'];
                    $row['Téléphone'] = PhoneNumberUtils::format($registration['phone']);
                    $row['Date d\'inscription'] = $registration['subscription_date']->format('Y-m-d H:i:s');

                    return $row;
                })
        );
    }
}
