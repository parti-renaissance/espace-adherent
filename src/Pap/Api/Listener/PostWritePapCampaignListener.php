<?php

declare(strict_types=1);

namespace App\Pap\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Pap\Campaign;
use App\Pap\Command\UpdateCampaignAddressInfoCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostWritePapCampaignListener implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['postWrite', EventPriorities::POST_WRITE]];
    }

    public function postWrite(ViewEvent $event): void
    {
        $campaign = $event->getControllerResult();

        if (
            !$campaign instanceof Campaign
            || !\in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->bus->dispatch(new UpdateCampaignAddressInfoCommand($campaign->getUuid()));
    }
}
