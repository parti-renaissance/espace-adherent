<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class ElectedRepresentativeAdherentMandateListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE]];
    }

    public function onPostWrite(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_DELETE])) {
            return;
        }

        if (Request::METHOD_DELETE === $request->getMethod()) {
            $mandate = $request->attributes->get('data');
        } else {
            $mandate = $viewEvent->getControllerResult();
        }

        if (!$mandate instanceof ElectedRepresentativeAdherentMandate) {
            return;
        }

        $adherent = $mandate->getAdherent();

        $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));
        $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
    }
}
