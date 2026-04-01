<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\ManagedUsers\Command\RefreshManagedUserProjectionCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostElectedRepresentativeAdherentMandateWriteListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function onPostWrite(ViewEvent $event): void
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

        $this->messageBus->dispatch(new RefreshManagedUserProjectionCommand($adherent->getUuid()));
    }
}
