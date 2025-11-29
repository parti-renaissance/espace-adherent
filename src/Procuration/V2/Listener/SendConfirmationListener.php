<?php

declare(strict_types=1);

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\ProcurationNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendConfirmationListener implements EventSubscriberInterface
{
    public function __construct(private readonly ProcurationNotifier $notifier)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_CREATED => ['sendConfirmation', -255],
            ProcurationEvents::REQUEST_CREATED => ['sendConfirmation', -255],
        ];
    }

    public function sendConfirmation(ProcurationEvent $event): void
    {
        $procuration = $event->procuration;

        if ($procuration instanceof Proxy) {
            $this->notifier->sendProxyConfirmation($procuration);

            return;
        }

        if ($procuration instanceof Request) {
            $this->notifier->sendRequestConfirmation($procuration);
        }
    }
}
