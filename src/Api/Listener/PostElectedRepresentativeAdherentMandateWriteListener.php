<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Repository\Projection\ManagedUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostElectedRepresentativeAdherentMandateWriteListener implements EventSubscriberInterface
{
    public function __construct(private readonly ManagedUserRepository $managedUserRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function onPostWrite(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (ElectedRepresentativeAdherentMandate::class !== $request->attributes->get('_api_resource_class')) {
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
}
