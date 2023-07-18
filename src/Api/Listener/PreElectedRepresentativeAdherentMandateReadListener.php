<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class PreElectedRepresentativeAdherentMandateReadListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['hasFilter', EventPriorities::PRE_READ]];
    }

    public function hasFilter(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $resource = $request->attributes->get('_api_resource_class');

        if (ElectedRepresentativeAdherentMandate::class !== $resource) {
            return;
        }

        $operation = $request->attributes->get('_api_collection_operation_name');

        if ('api_elected_representative_adherent_mandates_get_collection' !== $operation) {
            return;
        }

        if (!$request->query->has('adherent_uuid')) {
            throw new BadRequestHttpException('Filter "adherent.uuid" is required.');
        }
    }
}
