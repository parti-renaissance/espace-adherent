<?php

declare(strict_types=1);

namespace Tests\App\Unit\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\EventListener\NotifyForActionSubscriber;
use App\Entity\Action\Action;
use App\Entity\Geo\Zone;
use App\Events;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifyForActionSubscriberTest extends TestCase
{
    public function testActionCreationWithoutCityZoneDoesNotDispatch(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::never())
            ->method('dispatch')
        ;

        $subscriber = new NotifyForActionSubscriber($bus);
        $subscriber->onActionEvent(new ActionEvent(null, new Action()), Events::ACTION_CREATED);
    }

    public function testActionCreationWithCityZoneDispatchesCommand(): void
    {
        $action = new Action();
        $action->addZone(new Zone(Zone::CITY, '75056', 'Paris'));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyForActionCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $subscriber = new NotifyForActionSubscriber($bus);
        $subscriber->onActionEvent(new ActionEvent(null, $action), Events::ACTION_CREATED);
    }

    public function testActionCreationWithBoroughZoneDispatchesCommand(): void
    {
        // Paris/Lyon/Marseille actions are attached to an arrondissement (borough), not a city zone.
        $action = new Action();
        $action->addZone(new Zone(Zone::BOROUGH, '75108', 'Paris 8e'));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyForActionCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $subscriber = new NotifyForActionSubscriber($bus);
        $subscriber->onActionEvent(new ActionEvent(null, $action), Events::ACTION_CREATED);
    }

    public function testActionUpdateWithoutCityZoneStillDispatches(): void
    {
        // The city-zone guard only applies to the creation push; update/cancel target participants.
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyForActionCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $subscriber = new NotifyForActionSubscriber($bus);
        $subscriber->onActionEvent(new ActionEvent(null, new Action()), Events::ACTION_UPDATED);
    }
}
