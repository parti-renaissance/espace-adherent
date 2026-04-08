<?php

declare(strict_types=1);

namespace Tests\App\Unit\Event\EventListener;

use App\Entity\Event\Event;
use App\Event\EventEvent;
use App\Event\EventListener\SendEventPushNotificationListener;
use App\JeMengage\Push\Command\EventCreationNotificationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SendEventPushNotificationListenerTest extends TestCase
{
    public function testNotifyEventCreationWhenEventIsHiddenDoesNotDispatch(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::never())
            ->method('dispatch')
        ;

        $event = new Event();
        $event->hidden = true;

        $listener = new SendEventPushNotificationListener($bus);
        $listener->notifyEventCreation(new EventEvent(null, $event));
    }

    public function testNotifyEventCreationWhenEventIsNotHiddenDispatchesCommand(): void
    {
        $event = new Event();
        $event->hidden = false;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(EventCreationNotificationCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $listener = new SendEventPushNotificationListener($bus);
        $listener->notifyEventCreation(new EventEvent(null, $event));
    }
}
