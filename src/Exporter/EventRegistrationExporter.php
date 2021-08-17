<?php

namespace App\Exporter;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Repository\EventRegistrationRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;

class EventRegistrationExporter
{
    private $exporter;
    private $repository;

    public function __construct(SonataExporter $exporter, EventRegistrationRepository $repository)
    {
        $this->exporter = $exporter;
        $this->repository = $repository;
    }

    public function getResponse(string $format, BaseEvent $event): Response
    {
        $array = new \ArrayObject($this->repository->findBy(['event' => $event]));

        return $this->exporter->getResponse(
            $format,
            sprintf('%s_Evenement_Inscrits.%s', $event->getBeginAt()->format('d-m-Y'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                function (EventRegistration $registration) {
                    return [
                        'N° d\'enregistrement' => $registration->getUuid()->toString(),
                        'Prénom' => $registration->getFirstName(),
                        'Nom' => $registration->getLastName(),
                        'Code postal' => $registration->getPostalCode(),
                        'Date d\'inscription' => $registration->getCreatedAt()->format('d/m/Y à H:i'),
                    ];
                },
            )
        );
    }
}
