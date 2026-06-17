<?php

declare(strict_types=1);

namespace Tests\App\Unit\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\Command\SendActionCreationNotificationCommand;
use App\Action\EventListener\ActionMessageNotifierListener;
use App\Entity\Action\Action;
use App\Mailer\MailerService;
use App\Repository\Action\ActionParticipantRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        $listener = new ActionMessageNotifierListener(
            $bus,
            $this->createStub(ActionParticipantRepository::class),
            $this->createStub(MailerService::class),
            $this->createStub(UrlGeneratorInterface::class),
        );
        $listener->onActionCreated(new ActionEvent(null, new Action()));
    }
}
