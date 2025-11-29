<?php

declare(strict_types=1);

namespace App\Donation\Listener;

use App\Donation\Command\ReceivePayboxIpnResponseCommand;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TransactionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [PayboxEvents::PAYBOX_IPN_RESPONSE => 'onPayboxIpnResponse'];
    }

    public function onPayboxIpnResponse(PayboxResponseEvent $event): void
    {
        if (!$event->isVerified()) {
            return;
        }

        $this->bus->dispatch(new ReceivePayboxIpnResponseCommand($event->getData()));
    }
}
