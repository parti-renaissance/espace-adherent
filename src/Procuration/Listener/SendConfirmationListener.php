<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\Request;
use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
use App\Procuration\ProcurationNotifier;
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
