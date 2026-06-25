<?php

declare(strict_types=1);

namespace Tests\App\Unit\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\Command\SendActionCreationNotificationCommand;
use App\Action\Command\SendActionParticipantsNotificationCommand;
use App\Action\EventListener\ActionMessageNotifierListener;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class ActionMessageNotifierListenerTest extends TestCase
{
    public function testOnActionCreatedDispatchesTheCreationMailCommand(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendActionCreationNotificationCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $listener = new ActionMessageNotifierListener($bus);
        $listener->onActionCreated(new ActionEvent(null, new Action()));
    }

    public function testOnActionUpdatedDispatchesAParticipantsNotificationForAnUpdate(): void
    {
        $action = $this->createActionWithUuid($uuid = Uuid::v4());

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (SendActionParticipantsNotificationCommand $command): bool => $command->getUuid()->equals($uuid) && !$command->isCancelled()))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $listener = new ActionMessageNotifierListener($bus);
        $listener->onActionUpdated(new ActionEvent($action->getAuthor(), $action));
    }

    public function testOnActionCancelledDispatchesAParticipantsNotificationFlaggedCancelled(): void
    {
        $action = $this->createActionWithUuid($uuid = Uuid::v4());

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (SendActionParticipantsNotificationCommand $command): bool => $command->getUuid()->equals($uuid) && $command->isCancelled()))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $listener = new ActionMessageNotifierListener($bus);
        $listener->onActionCancelled(new ActionEvent($action->getAuthor(), $action));
    }

    private function createActionWithUuid(Uuid $uuid): Action
    {
        $action = new Action();
        $action->setAuthor(new Adherent());

        $reflection = new \ReflectionProperty(Action::class, 'uuid');
        $reflection->setValue($action, $uuid);

        return $action;
    }
}
