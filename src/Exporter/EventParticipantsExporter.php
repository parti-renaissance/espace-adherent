<?php

namespace App\Exporter;

use App\Adherent\Tag\TagTranslator;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
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
        private readonly SonataExporter $exporter,
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
                    /** @var EventRegistration $registration */
                    $registration = array_shift($data);
                    $row = [];

                    $row['Prénom'] = $registration->getFirstName();
                    $row['Nom'] = $registration->getLastName();
                    $row['Email'] = $registration->getEmailAddress();
                    $row['Labels'] = implode(', ', array_map(fn (string $tag) => $this->tagTranslator->trans($tag, false), $registration->getTags() ?? []));
                    $row['Code postal'] = $registration->getPostalCode();
                    $row['Téléphone'] = PhoneNumberUtils::format($registration->getPhone());
                    $row['Date d\'inscription'] = $registration->getCreatedAt()->format('Y-m-d H:i:s');

                    return $row;
                }
            )
        );
    }
}
