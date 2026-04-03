<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
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

        if (!\in_array($request->getMethod(), [Request::METHOD_DELETE, Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $mandate = $request->attributes->get('data');

        if (!$mandate instanceof ElectedRepresentativeAdherentMandate) {
            return;
        }

        if ($adherent = $mandate->getAdherent()) {
            $this->messageBus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
        }
    }
}
