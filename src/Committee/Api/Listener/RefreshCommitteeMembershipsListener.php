<?php

declare(strict_types=1);

namespace App\Committee\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Committee\Command\RefreshCommitteeMembershipsInZoneCommand;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class RefreshCommitteeMembershipsListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly LoggerInterface $logger)
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
            $committee = $request->attributes->get('data');
        } else {
            $committee = $viewEvent->getControllerResult();
        }

        if (!$committee instanceof Committee) {
            return;
        }

        /** @var Zone[] $zones */
        $zones = $committee->getZonesOfType(Zone::DEPARTMENT, true);

        if (!$zones) {
            $zones = $committee->getZonesOfType(Zone::CUSTOM, true);
        }

        if (!$zones) {
            $this->logger->error(\sprintf('Dpt or custom zone was not found for committee %d', $committee->getId()));

            return;
        }

        $this->bus->dispatch(new RefreshCommitteeMembershipsInZoneCommand(current($zones)->getCode()));
    }
}
