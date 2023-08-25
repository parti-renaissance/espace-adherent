<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Repository\Projection\ManagedUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ElectedRepresentativeAdherentMandateListener implements EventSubscriberInterface
{
    public function __construct(private readonly ManagedUserRepository $managedUserRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onPreRead', EventPriorities::PRE_READ],
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::PRE_RESPOND],
        ];
    }

    public function onPostWrite(RequestEvent $event): void
    {
        if (!$this->valideRequest($request = $event->getRequest())) {
            return;
        }

        if (!\in_array($request->getMethod(), [Request::METHOD_DELETE, Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        /** @var ElectedRepresentativeAdherentMandate $mandate */
        $mandate = $request->attributes->get('data');

        if (!$adherent = $mandate?->getAdherent()) {
            return;
        }

        $this->managedUserRepository->refreshAdherentMandates($adherent);
    }

    public function onPreRead(RequestEvent $event): void
    {
        if (!$this->valideRequest($request = $event->getRequest())) {
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

    private function valideRequest(Request $request): bool
    {
        return ElectedRepresentativeAdherentMandate::class === $request->attributes->get('_api_resource_class');
    }
}
