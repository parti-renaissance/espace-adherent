<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentListener implements EventSubscriberInterface
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
        $adherent = $viewEvent->getControllerResult();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $request = $viewEvent->getRequest();

        if (
            !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
            || '_api_/v3/adherents/{uuid}/elect_put' !== $request->attributes->get('_api_operation_name')
        ) {
            return;
        }

        $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
    }
}
