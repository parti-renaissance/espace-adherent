<?php

declare(strict_types=1);

namespace App\Phoning\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Phoning\Campaign;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PreWritePhoningCampaignListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['dispatchPreWrite', EventPriorities::PRE_WRITE]];
    }

    public function dispatchPreWrite(ViewEvent $event): void
    {
        /** @var Campaign $campaign */
        $campaign = $event->getControllerResult();

        if (
            !\in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)
            || !$campaign instanceof Campaign
        ) {
            return;
        }

        // Force SMS check for Phoning Campaigns
        if ($audience = $campaign->getAudience()) {
            $audience->setHasSmsSubscription(true);
        }
    }
}
